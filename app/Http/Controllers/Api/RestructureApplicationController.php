<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\RestructureApplication;
use App\Models\RestructureApplicationStatusLog;
use App\Models\User;
use App\Services\CoopFeeCalculatorService;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestructureApplicationController extends Controller
{
    public function eligibleLoans(Request $request): JsonResponse
    {
        $query = LoanApplication::query()
            ->with(['member.profile', 'type'])
            ->whereHas('loanAccount', fn (Builder $loanAccountQuery) => $loanAccountQuery->where('status', 'Active'));

        $this->applyLoanApplicationScope($query);

        $limit = max(1, min((int) $request->query('limit', 50), 200));

        $items = $query
            ->orderByDesc('loan_application_id')
            ->limit($limit)
            ->get()
            ->map(function (LoanApplication $loanApplication): array {
                $activeLoanAccount = $this->getActiveLoanAccount($loanApplication->loan_application_id);

                if (! $activeLoanAccount) {
                    return [];
                }

                $eligibility = $this->getEligibilityMetrics($loanApplication, $activeLoanAccount);
                $newPrincipal = (float) $activeLoanAccount->balance;
                $newInterest = (float) ($loanApplication->type?->max_interest_rate ?? 0);
                $fees = app(CoopFeeCalculatorService::class)->calculate('restructure', $newPrincipal);

                return [
                    'loan_application_id' => $loanApplication->loan_application_id,
                    'member_name' => $loanApplication->member?->profile?->full_name,
                    'loan_type' => $loanApplication->type?->name,
                    'loan_type_id' => $loanApplication->loan_type_id,
                    'max_term_months' => $loanApplication->type?->max_term_months,
                    'interest_rate' => $newInterest,
                    'active_loan_account_id' => $activeLoanAccount->loan_account_id,
                    'remaining_balance' => $newPrincipal,
                    'new_principal' => $newPrincipal,
                    'new_interest' => $newInterest,
                    'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                    'insurance_fee' => $fees['insurance_fee'] ?? 0,
                    'processing_fee' => $fees['processing_fee'] ?? 0,
                    'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                    'net_release_amount' => $fees['net_release_amount'] ?? 0,
                    'eligibility' => $eligibility,
                ];
            })
            ->filter(fn (array $item) => ! empty($item))
            ->values();

        return response()->json(['data' => $items]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = RestructureApplication::query()
            ->with(['loanApplication.member.profile', 'loanApplication.type', 'oldLoanAccount']);

        $this->applyRestructureScope($query);

        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        return response()->json(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function show(string $id): JsonResponse
    {
        $record = RestructureApplication::query()
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
                'oldLoanAccount',
                'statusLogs.changedBy',
            ])
            ->where('restructure_application_id', $id)
            ->firstOrFail();

        if (! $this->canAccessRestructureApplication($record)) {
            return response()->json(['message' => 'You are not allowed to view this restructure application.'], 403);
        }

        return response()->json($record);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'loan_application_id' => 'required|integer|exists:loan_applications,loan_application_id',
            'term_months' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $loanApplication = LoanApplication::query()
            ->with(['member.profile', 'type'])
            ->where('loan_application_id', $validated['loan_application_id'])
            ->firstOrFail();

        if (! $this->canAccessLoanApplication($loanApplication)) {
            return response()->json(['message' => 'You are not allowed to create a restructure application for this loan.'], 403);
        }

        $activeLoanAccount = $this->getActiveLoanAccount($loanApplication->loan_application_id);

        if (! $activeLoanAccount) {
            return response()->json(['message' => 'No active loan account found for this loan application.'], 422);
        }

        $eligibility = $this->getEligibilityMetrics($loanApplication, $activeLoanAccount);

        if (! $eligibility['is_eligible']) {
            return response()->json([
                'message' => 'Not eligible for restructuring. At least 50% of the original loan amount must be paid.',
                'eligibility' => $eligibility,
            ], 422);
        }

        $maxTermMonths = (int) ($loanApplication->type?->max_term_months ?? 0);
        $requestedTerm = (int) $validated['term_months'];

        if ($maxTermMonths > 0 && $requestedTerm > $maxTermMonths) {
            return response()->json([
                'message' => "Requested term exceeds the allowed maximum of {$maxTermMonths} months.",
            ], 422);
        }

        $hasOpenApplication = RestructureApplication::query()
            ->where('loan_application_id', $loanApplication->loan_application_id)
            ->whereIn('status', ['Pending', 'Under Review'])
            ->exists();

        if ($hasOpenApplication) {
            return response()->json([
                'message' => 'An existing pending or under-review restructure application already exists for this loan.',
            ], 422);
        }

        $principal = (float) $activeLoanAccount->balance;
        $interestRate = (float) ($loanApplication->type?->max_interest_rate ?? 0);
        $fees = app(CoopFeeCalculatorService::class)->calculate('restructure', $principal);

        $record = DB::transaction(function () use ($loanApplication, $activeLoanAccount, $validated, $principal, $interestRate, $fees) {
            $createdRecord = RestructureApplication::create([
                'loan_application_id' => $loanApplication->loan_application_id,
                'old_loan_account_id' => $activeLoanAccount->loan_account_id,
                'status' => 'Pending',
                'new_principal' => $principal,
                'new_interest' => $interestRate,
                'new_maturity_date' => now()->addMonths((int) $validated['term_months'])->toDateString(),
                'term_months' => (int) $validated['term_months'],
                'remarks' => $validated['remarks'] ?? null,
                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                'processing_fee' => $fees['processing_fee'] ?? 0,
                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                'net_release_amount' => $fees['net_release_amount'] ?? 0,
            ]);

            RestructureApplicationStatusLog::create([
                'restructure_application_id' => $createdRecord->restructure_application_id,
                'from_status' => null,
                'to_status' => 'Pending',
                'changed_by_user_id' => auth()->id(),
                'changed_at' => now(),
            ]);

            return $createdRecord;
        });

        $profileId = $loanApplication->member?->profile_id;

        if ($profileId) {
            app(NotificationService::class)->notifyProfileWithPush(
                $profileId,
                'Restructure application submitted',
                "Your restructure application #{$record->restructure_application_id} has been submitted and is pending review.",
                notifiableType: 'restructure_application',
                notifiableId: $record->restructure_application_id
            );
        }

        app(NotificationService::class)->notifyAdmins(
            'New restructure application submitted',
            "Restructure application #{$record->restructure_application_id} was submitted for loan application #{$loanApplication->loan_application_id}.",
            notifiableType: 'restructure_application',
            notifiableId: $record->restructure_application_id
        );

        return response()->json([
            'message' => 'Restructure application submitted successfully.',
            'data' => $record->load(['loanApplication.member.profile', 'loanApplication.type', 'oldLoanAccount']),
        ], 201);
    }

    public function markUnderReview(string $id): JsonResponse
    {
        if (! $this->isLoanOfficerOrAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Loan Officer or admin access is required.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $record = RestructureApplication::query()
            ->with('loanApplication.member.profile')
            ->where('restructure_application_id', $id)
            ->firstOrFail();

        if ($record->status !== 'Pending') {
            return response()->json(['message' => 'Only Pending applications can be moved to Under Review.'], 422);
        }

        $fromStatus = $record->status;

        $actor = auth()->user();

        DB::transaction(function () use ($record, $fromStatus, $actor) {
            $record->update(['status' => 'Under Review']);

            RestructureApplicationStatusLog::create([
                'restructure_application_id' => $record->restructure_application_id,
                'from_status' => $fromStatus,
                'to_status' => 'Under Review',
                'changed_by_user_id' => auth()->id(),
                'changed_at' => now(),
            ]);

            if ($actor) {
                app(NotificationService::class)->notifyUserWithPush(
                    $actor->user_id,
                    'Restructure under review',
                    "You moved restructure application #{$record->restructure_application_id} to Under Review.",
                    notifiableType: 'restructure_application',
                    notifiableId: $record->restructure_application_id
                );
            }
        });

        $profileId = $record->loanApplication?->member?->profile_id;

        if ($profileId) {
            app(NotificationService::class)->notifyProfileWithPush(
                $profileId,
                'Restructure under review',
                "Your restructure application #{$record->restructure_application_id} is now under review.",
                notifiableType: 'restructure_application',
                notifiableId: $record->restructure_application_id
            );
        }

        return response()->json(['message' => 'Restructure application marked as Under Review.']);
    }

    public function approve(string $id): JsonResponse
    {
        if (! $this->isLoanOfficerOrAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Loan Officer or admin access is required.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $record = RestructureApplication::query()
            ->with(['loanApplication.member.profile', 'oldLoanAccount'])
            ->where('restructure_application_id', $id)
            ->firstOrFail();

        if (! in_array($record->status, ['Pending', 'Under Review'], true)) {
            return response()->json(['message' => 'Only Pending or Under Review applications can be approved.'], 422);
        }

        $oldLoanAccount = $record->oldLoanAccount;

        if (! $oldLoanAccount) {
            return response()->json(['message' => 'Old loan account not found.'], 422);
        }

        if ($oldLoanAccount->status !== 'Active') {
            return response()->json(['message' => 'Only Active loan accounts can be restructured.'], 422);
        }

        $principal = (float) $record->new_principal;
        $interestRate = (float) $record->new_interest;
        $term = (int) $record->term_months;

        if ($principal <= 0 || $term <= 0) {
            return response()->json(['message' => 'Invalid restructure terms. Principal and term must be greater than zero.'], 422);
        }

        $releaseDate = now()->toDateString();

        $monthlyPrincipal = $principal / $term;
        $firstMonthInterest = $principal * ($interestRate / 100) / 12;
        $monthlyAmortization = $monthlyPrincipal + $firstMonthInterest;

        $fees = app(CoopFeeCalculatorService::class)->calculate('restructure', $principal);
        $fromStatus = $record->status;

        $actor = auth()->user();

        $newLoan = DB::transaction(function () use ($record, $oldLoanAccount, $principal, $interestRate, $term, $releaseDate, $monthlyAmortization, $fees, $fromStatus, $actor) {
            $record->update([
                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                'processing_fee' => $fees['processing_fee'] ?? 0,
                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                'net_release_amount' => $fees['net_release_amount'] ?? 0,
                'status' => 'Approved',
            ]);

            RestructureApplicationStatusLog::create([
                'restructure_application_id' => $record->restructure_application_id,
                'from_status' => $fromStatus,
                'to_status' => 'Approved',
                'changed_by_user_id' => auth()->id(),
                'changed_at' => now(),
            ]);

            $oldLoanAccount->update([
                'status' => 'Restructured',
                'restructured_at' => now(),
                'restructure_application_id' => $record->restructure_application_id,
            ]);

            if ($actor) {
                app(NotificationService::class)->notifyUserWithPush(
                    $actor->user_id,
                    'Restructure approved',
                    "You approved restructure application #{$record->restructure_application_id}.",
                    notifiableType: 'restructure_application',
                    notifiableId: $record->restructure_application_id
                );
            }

            return LoanAccount::create([
                'loan_application_id' => $record->loan_application_id,
                'profile_id' => $record->loanApplication?->member?->profile_id,
                'principal_amount' => $principal,
                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                'processing_fee' => $fees['processing_fee'] ?? 0,
                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                'net_release_amount' => $fees['net_release_amount'] ?? 0,
                'interest_rate' => $interestRate,
                'term_months' => $term,
                'release_date' => $releaseDate,
                'maturity_date' => now()->addMonths($term)->toDateString(),
                'monthly_amortization' => $monthlyAmortization,
                'balance' => $principal,
                'status' => 'Active',
                'parent_loan_account_id' => $oldLoanAccount->loan_account_id,
                'restructure_application_id' => $record->restructure_application_id,
                'penalty_rule_id' => $oldLoanAccount->penalty_rule_id,
            ]);
        });

        $profileId = $record->loanApplication?->member?->profile_id;

        if ($profileId) {
            app(NotificationService::class)->notifyProfileWithPush(
                $profileId,
                'Restructure approved',
                "Your restructure application #{$record->restructure_application_id} has been approved.",
                notifiableType: 'restructure_application',
                notifiableId: $record->restructure_application_id
            );
        }

        app(NotificationService::class)->notifyAdmins(
            'Restructure approved',
            "Restructure application #{$record->restructure_application_id} has been approved.",
            notifiableType: 'restructure_application',
            notifiableId: $record->restructure_application_id
        );

        return response()->json([
            'message' => 'Restructure approved. The old loan is now marked Restructured and a new Active loan has been created.',
            'new_loan_account_id' => $newLoan->loan_account_id,
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        if (! $this->isLoanOfficerOrAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Loan Officer or admin access is required.',
                'user_info' => $this->getUserRoleInfo(),
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $record = RestructureApplication::query()
            ->with('loanApplication.member.profile')
            ->where('restructure_application_id', $id)
            ->firstOrFail();

        if (! in_array($record->status, ['Pending', 'Under Review'], true)) {
            return response()->json(['message' => 'Only Pending or Under Review applications can be rejected.'], 422);
        }

        $fromStatus = $record->status;

        $actor = auth()->user();

        DB::transaction(function () use ($record, $fromStatus, $validated, $actor) {
            $record->update(['status' => 'Rejected']);

            RestructureApplicationStatusLog::create([
                'restructure_application_id' => $record->restructure_application_id,
                'from_status' => $fromStatus,
                'to_status' => 'Rejected',
                'changed_by_user_id' => auth()->id(),
                'reason' => $validated['reason'],
                'changed_at' => now(),
            ]);

            if ($actor) {
                app(NotificationService::class)->notifyUserWithPush(
                    $actor->user_id,
                    'Restructure rejected',
                    "You rejected restructure application #{$record->restructure_application_id}.",
                    notifiableType: 'restructure_application',
                    notifiableId: $record->restructure_application_id
                );
            }
        });

        $profileId = $record->loanApplication?->member?->profile_id;

        if ($profileId) {
            app(NotificationService::class)->notifyProfileWithPush(
                $profileId,
                'Restructure rejected',
                "Your restructure application #{$record->restructure_application_id} has been rejected. Reason: {$validated['reason']}",
                notifiableType: 'restructure_application',
                notifiableId: $record->restructure_application_id
            );
        }

        return response()->json(['message' => 'Restructure application rejected.']);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $record = RestructureApplication::query()
            ->with('loanApplication.member.profile')
            ->where('restructure_application_id', $id)
            ->firstOrFail();

        if (! $this->canAccessRestructureApplication($record)) {
            return response()->json(['message' => 'You are not allowed to cancel this restructure application.'], 403);
        }

        if (! in_array($record->status, ['Pending', 'Under Review'], true)) {
            return response()->json(['message' => 'Only Pending or Under Review applications can be cancelled.'], 422);
        }

        $fromStatus = $record->status;

        DB::transaction(function () use ($record, $fromStatus, $validated) {
            $record->update(['status' => 'Cancelled']);

            RestructureApplicationStatusLog::create([
                'restructure_application_id' => $record->restructure_application_id,
                'from_status' => $fromStatus,
                'to_status' => 'Cancelled',
                'changed_by_user_id' => auth()->id(),
                'reason' => $validated['reason'] ?? null,
                'changed_at' => now(),
            ]);
        });

        $profileId = $record->loanApplication?->member?->profile_id;

        if ($profileId) {
            app(NotificationService::class)->notifyProfileWithPush(
                $profileId,
                'Restructure cancelled',
                "Restructure application #{$record->restructure_application_id} has been cancelled.",
                notifiableType: 'restructure_application',
                notifiableId: $record->restructure_application_id
            );
        }

        return response()->json(['message' => 'Restructure application cancelled.']);
    }

    private function applyRestructureScope(Builder $query): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($user->isAdminOrSuperAdmin() || $user->isHeadOffice() || $this->isLoanOfficerOrAdmin()) {
            return;
        }

        if ($user->isMember()) {
            $query->whereHas('loanApplication.member', function (Builder $memberQuery) use ($user): void {
                $memberQuery->where('profile_id', $user->profile_id);
            });

            return;
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereHas('loanApplication.member', function (Builder $memberQuery) use ($branchId): void {
                $memberQuery->where('branch_id', $branchId);
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function applyLoanApplicationScope(Builder $query): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($user->isAdminOrSuperAdmin() || $user->isHeadOffice() || $this->isLoanOfficerOrAdmin()) {
            return;
        }

        if ($user->isMember()) {
            $query->whereHas('member', function (Builder $memberQuery) use ($user): void {
                $memberQuery->where('profile_id', $user->profile_id);
            });

            return;
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereHas('member', function (Builder $memberQuery) use ($branchId): void {
                $memberQuery->where('branch_id', $branchId);
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function canAccessLoanApplication(LoanApplication $loanApplication): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdminOrSuperAdmin() || $user->isHeadOffice() || $this->isLoanOfficerOrAdmin()) {
            return true;
        }

        if ($user->isMember()) {
            return (string) $loanApplication->member?->profile_id === (string) $user->profile_id;
        }

        if ($user->isBranchScoped()) {
            return (int) ($loanApplication->member?->branch_id ?? 0) === (int) ($user->branchId() ?? 0);
        }

        return false;
    }

    private function canAccessRestructureApplication(RestructureApplication $record): bool
    {
        $record->loadMissing('loanApplication.member');

        $loanApplication = $record->loanApplication;

        if (! $loanApplication) {
            return false;
        }

        return $this->canAccessLoanApplication($loanApplication);
    }

    private function getActiveLoanAccount(int $loanApplicationId): ?LoanAccount
    {
        return LoanAccount::query()
            ->where('loan_application_id', $loanApplicationId)
            ->where('status', 'Active')
            ->orderByDesc('loan_account_id')
            ->first();
    }

    /**
     * @return array{is_eligible: bool, original_amount: float, principal_paid: float, required_payment: float, remaining_to_eligible: float, remaining_balance: float, payment_progress_percent: float}
     */
    private function getEligibilityMetrics(LoanApplication $loanApplication, LoanAccount $loanAccount): array
    {
        $originalAmount = (float) $loanApplication->amount_requested;
        $principalPaid = max(0, (float) $loanAccount->principal_amount - (float) $loanAccount->balance);
        $requiredPayment = $originalAmount * 0.5;

        return [
            'is_eligible' => $principalPaid >= $requiredPayment,
            'original_amount' => $originalAmount,
            'principal_paid' => $principalPaid,
            'required_payment' => $requiredPayment,
            'remaining_to_eligible' => max(0, $requiredPayment - $principalPaid),
            'remaining_balance' => (float) $loanAccount->balance,
            'payment_progress_percent' => $originalAmount > 0
                ? round(min(100, ($principalPaid / $originalAmount) * 100), 1)
                : 0.0,
        ];
    }

    private function isLoanOfficerOrAdmin(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if (! $user->relationLoaded('profile')) {
            $user->load('profile');
        }

        return $user->isAdminOrSuperAdmin()
            || $user->isHeadOffice()
            || ((int) ($user->profile?->roles_id ?? 0) === 9);
    }

    private function getUserRoleInfo(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
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
            'is_loan_officer_or_admin' => $this->isLoanOfficerOrAdmin(),
            'has_profile' => $user->profile ? 'yes' : 'no',
        ];
    }
}
