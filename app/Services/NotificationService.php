<?php

namespace App\Services;

use App\Mail\GenericNotification;
use App\Mail\MemberAccountReady;
use App\Models\Notification;
use App\Models\Profile;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationService
{
    public function notifyUser(
        int $userId,
        string $title,
        string $description,
        bool $isRead = false,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'status' => $isRead ? 'seen' : 'unseen',
            'is_read' => $isRead,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
        ]);
    }

    public function notifyProfile(
        string|int $profileId,
        string $title,
        string $description,
        bool $isRead = false,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $user = User::where('profile_id', $profileId)->first();

        if (! $user) {
            Log::warning("NotificationService: no user found for profile_id={ $profileId }");

            return null;
        }

        return $this->notifyUser($user->user_id, $title, $description, $isRead, $notifiableType, $notifiableId);
    }

    public function notifyRoles(
        array|string $roles,
        string $title,
        string $description,
        bool $isRead = false,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): void {
        $roleNames = is_array($roles) ? $roles : [$roles];
        $notifiedUserIds = [];

        foreach ($roleNames as $roleName) {
            try {
                $roleUsers = User::role($roleName)->get();
            } catch (RoleDoesNotExist $exception) {
                Log::warning("NotificationService: role does not exist for notifyRoles - {$roleName}");

                continue;
            }

            foreach ($roleUsers as $user) {
                if (in_array($user->user_id, $notifiedUserIds, true)) {
                    continue;
                }
                $this->notifyUser($user->user_id, $title, $description, $isRead, $notifiableType, $notifiableId);
                $notifiedUserIds[] = $user->user_id;
            }
        }
    }

    public function notifyAdmins(
        string $title,
        string $description,
        bool $isRead = false,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): void {
        $this->notifyRoles(['Admin', 'super_admin'], $title, $description, $isRead, $notifiableType, $notifiableId);
    }

    public function notifyManagers(
        string $title,
        string $description,
        bool $isRead = false,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): void {
        $this->notifyRoles([
            'Manager',
            'manager',
            'Loan Manager',
            'loan_manager',
            'Branch Manager',
            'branch_manager',
            'HQ Manager',
            'hq_manager',
            'hqmanager',
        ], $title, $description, $isRead, $notifiableType, $notifiableId);
    }

    public function createUserWithAutoPassword(Profile $profile): ?User
    {
        $existing = User::where('profile_id', $profile->profile_id)->first();
        if ($existing) {
            return $existing;
        }

        $password = Str::random(12);

       $user = User::create([
        'profile_id' => $profile->profile_id,
        'password'   => Hash::make($password),
        'username'   => $profile->first_name . ' ' . $profile->last_name,
        'must_change_password' => true,
        'is_active'  => true,
    ]);

        $user->assignRole('Member');

        // Send email and track it - use afterCommit so the record survives email failures
        $this->sendPasswordEmailWithTracking($user, $password);
        $this->notifyUserAccountCreated($user);

        return $user;
    }

    /**
     * Send password email with tracking - uses afterCommit to ensure
     * the sent_emails record is created AFTER the transaction commits.
     */
    protected function sendPasswordEmailWithTracking(User $user, string $password): void
    {
        $profile = $user->profile;

        if (! $profile || empty($profile->email)) {
            return;
        }

        $email = $profile->email;
        $subject = 'Your SLEM Coop Member Account is Ready';

        // Create pending email record inside transaction
        $sentEmail = SentEmail::create([
            'user_id' => $user->user_id,
            'profile_id' => $profile->profile_id,
            'email' => $email,
            'subject' => $subject,
            'mailable_class' => MemberAccountReady::class,
        ]);

        // Use afterCommit to ensure the record survives if mail fails
        DB::afterCommit(function () use ($user, $password, $sentEmail, $email) {
            try {
                Mail::to($email)->send(new MemberAccountReady($user, $password));

                // Mark as sent after successful queue
                $sentEmail->update(['sent_at' => now()]);

                Log::info("Password email queued for user_id={$user->user_id}, sent_email_id={$sentEmail->id}");
            } catch (\Throwable $exception) {
                // Mark as failed
                $sentEmail->update([
                    'failed_at' => now(),
                    'failure_reason' => $exception->getMessage(),
                ]);

                Log::warning("Failed to send password email to {$email}: ".$exception->getMessage());
            }
        });
    }

    /**
     * @deprecated Use sendPasswordEmailWithTracking() instead
     */
    protected function sendPasswordEmail(User $user, string $password): void
    {
        $this->sendPasswordEmailWithTracking($user, $password);
    }

    public function sendPaymentConfirmation(int|string $profileId, float $amount, ?string $loanNumber = null): ?Notification
    {
        $title = 'Payment Confirmation';
        $description = 'Your payment of ₱'.number_format($amount, 2).' has been received and posted.';
        if ($loanNumber) {
            $description .= " Loan: {$loanNumber}.";
        }
        $description .= ' Thank you!';

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyDocumentUpload(
        int|string $profileId,
        string $documentType,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = 'Document Uploaded';
        $description = "Your {$documentType} has been received and is being reviewed.";

        return $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
    }

    public function notifyDocumentMissing(
        int|string $profileId,
        string $documentType,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = 'Missing Document Required';
        $description = "Please upload the required {$documentType} to proceed with your application.";

        return $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
    }

    public function notifyPaymentPosted(
        int|string $profileId,
        float $amount,
        string $status = 'posted',
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = 'Payment '.ucfirst($status);
        $description = 'Your payment of ₱'.number_format($amount, 2)." has been {$status}.";

        return $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
    }

    public function notifyPaymentEdited(
        int|string $profileId,
        float $oldAmount,
        float $newAmount,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = 'Payment Edited';
        $description = 'Your payment was edited from ₱'.number_format($oldAmount, 2).' to ₱'.number_format($newAmount, 2).'.';

        return $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
    }

    public function notifyPaymentVoided(
        int|string $profileId,
        float $amount,
        string $reason = '',
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = 'Payment Voided';
        $description = 'Your payment of ₱'.number_format($amount, 2).' has been voided.';
        if ($reason) {
            $description .= " Reason: {$reason}";
        }

        return $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
    }

    public function notifyDueDateReminder(
        int|string $profileId,
        float $amount,
        string $dueDate,
        ?int $daysUntilDue = null,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = $daysUntilDue === 0 ? 'Payment Due Today' : "Payment Due in {$daysUntilDue} Days";
        $description = 'Your loan payment of ₱'.number_format($amount, 2).' is due on '.$dueDate.'.';

        $notification = $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
        $this->sendEmailNotification($profileId, $title, $description);

        return $notification;
    }

    public function notifyOverdueNotice(
        int|string $profileId,
        float $amount,
        string $dueDate,
        int $daysOverdue,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = "Overdue Payment Notice ({$daysOverdue} days)";
        $description = 'Your loan payment of ₱'.number_format($amount, 2).' was due on '.$dueDate.'. Please make payment to avoid penalties.';

        $notification = $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
        $this->sendEmailNotification($profileId, $title, $description);

        return $notification;
    }

    public function notifyReloanEligibility(
        int|string $profileId,
        string $loanNumber,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): ?Notification {
        $title = 'Now eligible for reloan';
        $description = "Your loan {$loanNumber} is now eligible for reloan. Please submit your request when ready.";

        return $this->notifyProfile($profileId, $title, $description, notifiableType: $notifiableType, notifiableId: $notifiableId);
    }

    public function notifyOrientationStarted(int|string $profileId, string $orientationTitle): ?Notification
    {
        $title = 'Orientation Started';
        $description = "You have started the orientation: {$orientationTitle}. Complete it to stay eligible for loan processing.";

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyOrientationCompleted(int|string $profileId, bool $passed, int $score, ?string $certificateUrl = null): ?Notification
    {
        $title = $passed ? 'Orientation Passed' : 'Orientation Completed';
        $description = $passed ?
            "You passed the orientation with a score of {$score}%." :
            "You completed the orientation with a score of {$score}%.";

        if ($certificateUrl) {
            $description .= " Your certificate is available at: {$certificateUrl}";
        }

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyPenaltyRuleUpdated(int|string $profileId, string $ruleName, int $graceDays, string $details): ?Notification
    {
        $title = 'Penalty Rule Updated';
        $description = "Your loan penalty configuration has been updated to '{$ruleName}' with a {$graceDays}-day grace period. {$details}";

        return $this->notifyProfile($profileId, $title, $description);
    }

    protected function sendEmailNotification(int|string $profileId, string $title, string $message): void
    {
        $profile = Profile::where('profile_id', $profileId)->first();

        if (! $profile || empty($profile->email)) {
            return;
        }

        try {
            Mail::queue(new GenericNotification($profile->email, $title, $message));
        } catch (\Throwable $exception) {
            Log::warning("NotificationService: failed to send email notification to profile_id={$profile->profile_id} - {$exception->getMessage()}");
        }
    }

    protected function hasSentNotification(int $userId, string $title): bool
    {
        return Notification::where('user_id', $userId)
            ->where('title', $title)
            ->exists();
    }

    public function notifyUniqueProfile(string|int $profileId, string $title, string $description): ?Notification
    {
        $user = User::where('profile_id', $profileId)->first();

        if (! $user || $this->hasSentNotification($user->user_id, $title)) {
            return null;
        }

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyAdminOfAccountChange(string $adminTitle, string $adminDescription): void
    {
        $this->notifyAdmins($adminTitle, $adminDescription);
    }

    public function notifyUserAccountCreated(User $user): ?Notification
     {
        $title = 'Account Created';

        $description = "Your account has been successfully created.\n\n"
            ."You can now log in using your registered email address.\n"
            ."A temporary password has been sent to your email.\n\n"
            .'For security purposes, please change your password after your first login.';

        return $this->notifyUser($user->user_id, $title, $description);
    }

    public function notifyUserRoleChanged(User $user, string $oldRole, string $newRole): ?Notification
    {
        $title = 'Account Role Changed';
        $description = "Your account role has been changed from {$oldRole} to {$newRole}.";

        return $this->notifyUser($user->user_id, $title, $description);
    }

    public function notifyUserStatusChanged(User $user, string $oldStatus, string $newStatus): ?Notification
    {
        $title = 'Account Status Changed';
        $description = "Your account status has been changed from {$oldStatus} to {$newStatus}.";

        return $this->notifyUser($user->user_id, $title, $description);
    }

    public function notifyPermissionsChanged(User $user, string $changes): ?Notification
    {
        $title = 'Account Permissions Changed';
        $description = "Your account permissions have been updated: {$changes}";

        return $this->notifyUser($user->user_id, $title, $description);
    }
}
