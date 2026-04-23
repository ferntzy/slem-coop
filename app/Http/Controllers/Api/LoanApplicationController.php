<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoopSetting;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatusLog;
use App\Models\Notification as ModelsNotification;
use App\Models\PenaltyRule;
use App\Services\CoopFeeCalculatorService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanApplicationController extends Controller
{
    private function loanOfficerApprovalLimit(): float
    {
        return (float) CoopSetting::get('loan.loan_officer_approval_limit', 20000);
    }

    private function isLoanOfficer(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (! $user->relationLoaded('profile')) {
            $user->load('profile');
        }

        $profile = $user->profile;

        if (! $profile) {
            return false;
        }

        return $user->hasAnyRole([
            'Loan Officer',
            'loan_officer',
            'HQ Loan Officer',
            'hq_loan_officer',
        ]) || $user->canApproveAnyLoanAmount();
    }

    private function canApproveAnyLoanAmount(): bool
    {
        $user = auth()->user();

        return $user?->canApproveAnyLoanAmount() ?? false;
    }

    private function isLoanOfficerApprovalRole(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole([
            'Loan Officer',
            'loan_officer',
            'HQ Loan Officer',
            'hq_loan_officer',
        ]) ?? false;
    }

    private function getUserRoleInfo(): array
    {
        $user = auth()->user();
        if (! $user) {
            return ['error' => 'No authenticated user'];
        }

        if (! $user->relationLoaded('profile')) {
            $user->load('profile');
        }

        return [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'email' => $user->email,
            'role_id' => $user->profile?->roles_id,
            'is_loan_officer' => $user->hasAnyRole([
                'Loan Officer',
                'loan_officer',
                'HQ Loan Officer',
                'hq_loan_officer',
            ]),
            'has_profile' => $user->profile ? 'yes' : 'no',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        // pagination for mobile
        $loanApplications = LoanApplication::query()
            ->with(['member.profile', 'type', 'documents', 'cashflows', 'loanAccount'])
            ->paginate($request->query('per_page', 15));

        return response()->json($loanApplications);
    }

    public function show(string $id): JsonResponse
    {
        $loanApplication = LoanApplication::query()
            ->with(['member.profile', 'type', 'documents', 'cashflows', 'loanAccount', 'payments', 'statusLogs'])
            ->where('loan_application_id', $id)
            ->firstOrFail();

        return response()->json($loanApplication);
    }

    // approval actions for loan officers
    public function approve(Request $request, $id): JsonResponse
    {
        if (! $this->isLoanOfficer()) {
            return response()->json([
                'message' => 'Unauthorized. You need Loan Officer, HQ Loan Officer, Manager, HQ Manager, or Admin access to approve loans.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $record = LoanApplication::with(['member.profile.user'])
            ->where('loan_application_id', $id)
            ->firstOrFail();

        if (! in_array($record->status, ['Pending', 'Under Review'], true)) {
            return response()->json(['message' => 'Only Pending or Under Review applications can be approved.'], 422);
        }

        if ($record->approved_at) {
            return response()->json(['message' => 'This application has already been approved.'], 422);
        }

        $approvalLimit = $this->loanOfficerApprovalLimit();
        if ((float) $record->amount_requested > $approvalLimit && ! $this->canApproveAnyLoanAmount()) {
            if (! $this->isLoanOfficerApprovalRole()) {
                return response()->json([
                    'message' => 'Unauthorized. Only loan officers can escalate high-value loans.',
                ], 403);
            }

            $from = $record->status;

            if ($from !== 'Under Review') {
                $record->update(['status' => 'Under Review']);

                LoanApplicationStatusLog::create([
                    'loan_application_id' => $record->loan_application_id,
                    'from_status' => $from,
                    'to_status' => 'Under Review',
                    'changed_by_user_id' => auth()->id(),
                    'reason' => 'Escalated for Manager and Admin approvals due to loan officer limit.',
                    'changed_at' => now(),
                ]);
            }

            $profileId = $record->member?->profile_id ?? null;

            app(NotificationService::class)->notifyManagers(
                'Loan requires manager approval',
                "Loan application #{$record->loan_application_id} exceeds the loan officer limit of PHP ".number_format($approvalLimit, 2).' and needs manager approval.',
                notifiableType: 'loan_application',
                notifiableId: $record->loan_application_id
            );

            app(NotificationService::class)->notifyAdmins(
                'Loan requires admin approval',
                "Loan application #{$record->loan_application_id} exceeds the loan officer limit of PHP ".number_format($approvalLimit, 2).' and needs manager + admin approvals.',
                notifiableType: 'loan_application',
                notifiableId: $record->loan_application_id
            );

            if ($profileId) {
                app(NotificationService::class)->notifyProfile(
                    $profileId,
                    'Loan application escalated for approval',
                    "Your loan application #{$record->loan_application_id} is under review and requires manager + admin approvals.",
                    notifiableType: 'loan_application',
                    notifiableId: $record->loan_application_id
                );
            }

            return response()->json([
                'message' => 'This loan exceeds your approval limit and was escalated to Manager and Admin.',
                'approval_limit' => $approvalLimit,
            ], 202);
        }

        $profileId = $record->member?->profile_id ?? null;
        $from = $record->status;

        DB::transaction(function () use ($record, $from, $profileId) {

            $record->update([
                'status' => 'Approved',
                'approved_at' => now(),
            ]);

            LoanApplicationStatusLog::create([
                'loan_application_id' => $record->loan_application_id,
                'from_status' => $from,
                'to_status' => 'Approved',
                'changed_by_user_id' => auth()->id(),
                'changed_at' => now(),
            ]);

            ModelsNotification::create([
                'user_id' => $record->member->profile->user->user_id,
                'title' => 'Loan Application',
                'description' => 'Your loan application has been approved! Waiting for release date.',
                'notifiable_type' => 'loan_application',
                'notifiable_id' => $record->loan_application_id,
            ]);

            if ($profileId) {
                app(NotificationService::class)->notifyProfile(
                    $profileId,
                    'Loan application approved',
                    "Your loan application #{$record->loan_application_id} has been approved.",
                    notifiableType: 'loan_application',
                    notifiableId: $record->loan_application_id
                );
            }

            app(NotificationService::class)->notifyAdmins(
                'Loan application approved',
                "Loan application #{$record->loan_application_id} has been approved.",
                notifiableType: 'loan_application',
                notifiableId: $record->loan_application_id
            );
        });

        return response()->json(['message' => 'Loan application approved successfully. Please set the release date to create the loan account.']);
    }

    // reloan - Allow Loan Officers
    public function reloan(Request $request, $id): JsonResponse
    {
        if (! $this->isLoanOfficer()) {
            return response()->json([
                'message' => 'Unauthorized. You need Loan Officer or Admin access to process reloans.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $request->validate([
            'amount_requested' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
        ]);

        $record = LoanApplication::with(['member.profile', 'loanAccount', 'type'])
            ->where('loan_application_id', $id)
            ->firstOrFail();

        $loan = $record->loanAccount;

        if (! $loan) {
            return response()->json(['message' => 'No loan account found for this application.'], 422);
        }

        if ($loan->status !== 'Active') {
            return response()->json(['message' => 'Loan account is not active.'], 422);
        }

        // 50 % rule
        $paid = $loan->principal_amount - $loan->balance;
        $required = $loan->principal_amount * 0.5;

        if ($paid < $required) {
            return response()->json([
                'message' => 'Must pay at least 50% of previous loan before reloaning.',
                'paid' => $paid,
                'required' => $required,
                'remaining_balance' => $loan->balance,
            ], 422);
        }

        DB::transaction(function () use ($record, $request, $loan) {

            $principal = (float) $request->amount_requested;
            $term = (int) $request->term_months;
            $interestRate = (float) ($record->type?->max_interest_rate ?? 0);
            $releaseDate = now()->format('Y-m-d');
            $profileId = $record->member?->profile_id ?? null;

            $monthlyPrincipal = $term > 0 ? ($principal / $term) : $principal;
            $firstMonthInterest = $principal * ($interestRate / 100) / 12;
            $monthlyAmort = $monthlyPrincipal + $firstMonthInterest;

            $fees = app(CoopFeeCalculatorService::class)->calculate('reloan', $principal);

            $remainingBalance = $loan->balance;
            $netPrincipal = max(0, $principal - $remainingBalance);
            $netRelease = max(0, ($fees['net_release_amount'] ?? 0) - $remainingBalance);

            // New loan application (pre-approved)
            $newLoanApp = LoanApplication::create([
                'member_id' => $record->member_id,
                'loan_type_id' => $record->loan_type_id,
                'amount_requested' => $principal,
                'term_months' => $term,
                'status' => 'Approved',
                'reloan_from_loan_account_id' => $loan->loan_account_id,
                'previous_balance' => $loan->balance,
            ]);

            $newLoanApp->update([
                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                'processing_fee' => $fees['processing_fee'] ?? 0,
                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                'net_release_amount' => $netRelease,
            ]);

            LoanAccount::create([
                'loan_application_id' => $newLoanApp->loan_application_id,
                'profile_id' => $profileId,
                'principal_amount' => $netPrincipal,
                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                'processing_fee' => $fees['processing_fee'] ?? 0,
                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                'net_release_amount' => $netRelease,
                'interest_rate' => $interestRate,
                'term_months' => $term,
                'release_date' => $releaseDate,
                'maturity_date' => date('Y-m-d', strtotime("{$releaseDate} +{$term} months")),
                'monthly_amortization' => $monthlyAmort,
                'balance' => $netPrincipal,
                'status' => 'Active',
                'parent_loan_account_id' => $loan->loan_account_id,
            ]);
        });

        return response()->json(['message' => 'Reloan application created and released successfully.']);
    }
    // ─────────────────────────────────────────────────────────────
    // SET PENALTY RULE - Allow Loan Officers
    // POST /api/loan-applications/{id}/set-penalty-rule
    // ─────────────────────────────────────────────────────────────

    public function setPenaltyRule(Request $request, $id): JsonResponse
    {
        if (! $this->isLoanOfficer()) {
            return response()->json([
                'message' => 'Unauthorized. You need Loan Officer or Admin access to set penalty rules.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $request->validate([
            'penalty_rule_id' => 'required|exists:penalty_rules,id',
        ]);

        $record = LoanApplication::where('loan_application_id', $id)->firstOrFail();

        if (! in_array($record->status, ['Pending', 'Under Review', 'Approved'], true)) {
            return response()->json(['message' => 'Penalty rule can only be set for Pending, Under Review, or Approved applications.'], 422);
        }

        $rule = PenaltyRule::where('id', $request->penalty_rule_id)
            ->where('status', 'active')
            ->first();

        if (! $rule) {
            return response()->json(['message' => 'Selected penalty rule is not active.'], 422);
        }

        $record->update(['penalty_rule_id' => $request->penalty_rule_id]);

        return response()->json(['message' => 'Penalty rule updated successfully.']);
    }

    // ─────────────────────────────────────────────────────────────
    // MARK UNDER REVIEW - Allow Loan Officers
    // POST /api/loan-applications/{id}/under-review
    // ─────────────────────────────────────────────────────────────

    public function markUnderReview(Request $request, $id): JsonResponse
    {
        if (! $this->isLoanOfficer()) {
            return response()->json([
                'message' => 'Unauthorized. You need Loan Officer or Admin access to mark under review.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $record = LoanApplication::with('member.profile')
            ->where('loan_application_id', $id)
            ->firstOrFail();

        if ($record->status !== 'Pending') {
            return response()->json(['message' => 'Only Pending applications can be moved to Under Review.'], 422);
        }

        $profileId = $record->member?->profile_id ?? null;
        $from = $record->status;

        DB::transaction(function () use ($record, $from, $profileId) {

            $record->update(['status' => 'Under Review']);

            LoanApplicationStatusLog::create([
                'loan_application_id' => $record->loan_application_id,
                'from_status' => $from,
                'to_status' => 'Under Review',
                'changed_by_user_id' => auth()->id(),
                'changed_at' => now(),
            ]);

            if ($profileId) {
                app(NotificationService::class)->notifyProfile(
                    $profileId,
                    'Loan under review',
                    "Your loan application #{$record->loan_application_id} is now under review."
                );
            }

            app(NotificationService::class)->notifyAdmins(
                'Loan under review',
                "Loan application #{$record->loan_application_id} moved to Under Review."
            );
        });

        return response()->json(['message' => 'Loan application marked as Under Review.']);
    }

    // ─────────────────────────────────────────────────────────────
    // DOWNLOAD LOAN FORM - Allow Loan Officers
    // GET /api/loan-applications/{id}/download-form
    // ─────────────────────────────────────────────────────────────

    public function downloadLoanForm($id): JsonResponse
    {
        if (! $this->isLoanOfficer()) {
            return response()->json([
                'message' => 'Unauthorized. You need Loan Officer or Admin access.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        LoanApplication::where('loan_application_id', $id)->firstOrFail();

        $url = route('loan-applications.pdf', ['loanApplication' => $id]);

        return response()->json([
            'message' => 'PDF URL generated successfully.',
            'pdf_url' => $url,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // REJECT - Allow Loan Officers
    // POST /api/loan-applications/{id}/reject
    // ─────────────────────────────────────────────────────────────

    public function reject(Request $request, $id): JsonResponse
    {
        if (! $this->isLoanOfficer()) {
            return response()->json([
                'message' => 'Unauthorized. You need Loan Officer or Admin access to reject loans.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $record = LoanApplication::with(['member.profile.user'])
            ->where('loan_application_id', $id)
            ->firstOrFail();

        if (! in_array($record->status, ['Pending', 'Under Review'], true)) {
            return response()->json(['message' => 'Only Pending or Under Review applications can be rejected.'], 422);
        }

        $profileId = $record->member?->profile_id ?? null;
        $from = $record->status;

        DB::transaction(function () use ($record, $from, $profileId, $request) {

            $record->update(['status' => 'Rejected']);

            LoanApplicationStatusLog::create([
                'loan_application_id' => $record->loan_application_id,
                'from_status' => $from,
                'to_status' => 'Rejected',
                'changed_by_user_id' => auth()->id(),
                'reason' => $request->reason,
                'changed_at' => now(),
            ]);

            ModelsNotification::create([
                'user_id' => $record->member->profile->user->user_id,
                'title' => 'Loan application was rejected',
                'description' => $request->reason,
                'notifiable_type' => 'loan_application',
                'notifiable_id' => $record->loan_application_id,
            ]);

            if ($profileId) {
                app(NotificationService::class)->notifyProfile(
                    $profileId,
                    'Loan application rejected',
                    "Your loan application #{$record->loan_application_id} has been rejected. Reason: {$request->reason}",
                    notifiableType: 'loan_application',
                    notifiableId: $record->loan_application_id
                );
            }

            app(NotificationService::class)->notifyAdmins(
                'Loan application rejected',
                "Loan application #{$record->loan_application_id} has been rejected. Reason: {$request->reason}",
                notifiableType: 'loan_application',
                notifiableId: $record->loan_application_id
            );
        });

        return response()->json(['message' => 'Loan application rejected.']);
    }
}
