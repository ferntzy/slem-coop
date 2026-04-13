<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationService
{
    public function notifyUser(int $userId, string $title, string $description, bool $isRead = false): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'status' => $isRead ? 'seen' : 'unseen',
        ]);
    }

    public function notifyProfile(string|int $profileId, string $title, string $description, bool $isRead = false): ?Notification
    {
        $user = User::where('profile_id', $profileId)->first();

        if (! $user) {
            Log::warning("NotificationService: no user found for profile_id={ $profileId }");

            return null;
        }

        return $this->notifyUser($user->user_id, $title, $description, $isRead);
    }

    public function notifyRoles(array|string $roles, string $title, string $description, bool $isRead = false): void
    {
        $roleUsers = User::role($roles)->get();

        foreach ($roleUsers as $user) {
            $this->notifyUser($user->user_id, $title, $description, $isRead);
        }
    }

    public function notifyAdmins(string $title, string $description, bool $isRead = false): void
    {
        $this->notifyRoles(['Admin', 'super_admin'], $title, $description, $isRead);
    }
    public function createUserWithAutoPassword(Profile $profile): ?User
    {
        $existing = User::where('profile_id', $profile->profile_id)->first();
        if ($existing) {
            return $existing;
        }

        $password = Str::random(12);
        $username = Str::slug($profile->full_name, '.').'.'.strtolower(Str::random(4));

        $user = User::create([
            'username' => $username,
            'profile_id' => $profile->profile_id,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $user->assignRole('Member');

        $this->sendPasswordEmail($user, $password);
        $this->notifyUserAccountCreated($user, $username, $password);

        return $user;
    }

    protected function sendPasswordEmail(User $user, string $password): void
    {
        $profile = $user->profile;

        if (! $profile || empty($profile->email)) {
            return;
        }

        $title = 'Your member account is ready';
        $message = sprintf(
            "Hello %s,\n\nYour account has been created.\nUsername: %s\nTemporary password: %s\n\nPlease login and change your password immediately.",
            $profile->full_name,
            $user->username,
            $password,
        );

        $this->sendEmailNotification($profile->profile_id, $title, $message);
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

    public function notifyDocumentUpload(int|string $profileId, string $documentType): ?Notification
    {
        $title = 'Document Uploaded';
        $description = "Your {$documentType} has been received and is being reviewed.";

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyDocumentMissing(int|string $profileId, string $documentType): ?Notification
    {
        $title = 'Missing Document Required';
        $description = "Please upload the required {$documentType} to proceed with your application.";

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyPaymentPosted(int|string $profileId, float $amount, string $status = 'posted'): ?Notification
    {
        $title = 'Payment '.ucfirst($status);
        $description = 'Your payment of ₱'.number_format($amount, 2)." has been {$status}.";

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyPaymentEdited(int|string $profileId, float $oldAmount, float $newAmount): ?Notification
    {
        $title = 'Payment Edited';
        $description = 'Your payment was edited from ₱'.number_format($oldAmount, 2).' to ₱'.number_format($newAmount, 2).'.';

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyPaymentVoided(int|string $profileId, float $amount, string $reason = ''): ?Notification
    {
        $title = 'Payment Voided';
        $description = 'Your payment of ₱'.number_format($amount, 2).' has been voided.';
        if ($reason) {
            $description .= " Reason: {$reason}";
        }

        return $this->notifyProfile($profileId, $title, $description);
    }

    public function notifyDueDateReminder(int|string $profileId, float $amount, string $dueDate, ?int $daysUntilDue = null): ?Notification
    {
        $title = $daysUntilDue === 0 ? 'Payment Due Today' : "Payment Due in {$daysUntilDue} Days";
        $description = 'Your loan payment of ₱'.number_format($amount, 2).' is due on '.$dueDate.'.';

        $notification = $this->notifyProfile($profileId, $title, $description);
        $this->sendEmailNotification($profileId, $title, $description);

        return $notification;
    }

    public function notifyOverdueNotice(int|string $profileId, float $amount, string $dueDate, int $daysOverdue): ?Notification
    {
        $title = "Overdue Payment Notice ({$daysOverdue} days)";
        $description = 'Your loan payment of ₱'.number_format($amount, 2).' was due on '.$dueDate.'. Please make payment to avoid penalties.';

        $notification = $this->notifyProfile($profileId, $title, $description);
        $this->sendEmailNotification($profileId, $title, $description);

        return $notification;
    }

    public function notifyReloanEligibility(int|string $profileId, string $loanNumber): ?Notification
    {
        $title = 'Now eligible for reloan';
        $description = "Your loan {$loanNumber} is now eligible for reloan. Please submit your request when ready.";

        return $this->notifyProfile($profileId, $title, $description);
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
            Mail::raw($message, function ($mail) use ($profile, $title) {
                $mail->to($profile->email)
                    ->subject($title);
            });
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

    public function notifyUserAccountCreated(User $user, string $username, string $tempPassword): ?Notification
    {
        $title = 'Account Created';
        $description = "Your account has been created. Username: {$username}. Check your email for your temporary password.";

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
