# Notification System Enhancement Guide

## Overview

The notification system has been enhanced to track which resource (loan application, membership application, etc.) triggered a notification and provide direct redirect links.

## Database Changes

- Added `notifiable_type` - The type of resource (e.g., 'loan_application')
- Added `notifiable_id` - The ID of the resource
- Added `is_read` - Boolean flag for read status

## Usage Examples

### 1. Notifying a profile when their loan application is approved

```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

$profileId = $member->profile_id;
$loanApplicationId = $loanApplication->loan_application_id;

$notificationService->notifyProfile(
    $profileId,
    'Loan Application Approved',
    "Your loan application #{$loanApplicationId} has been approved.",
    notifiableType: 'loan_application',
    notifiableId: $loanApplicationId
);
```

### 2. Notifying admins when a membership application needs review

```php
$notificationService->notifyAdmins(
    'New Membership Application',
    "Membership application #{$applicationId} requires verification.",
    notifiableType: 'membership_application',
    notifiableId: $applicationId
);
```

### 3. Notifying users with specific roles

```php
$notificationService->notifyRoles(
    ['Admin', 'Loan Officer'],
    'Collateral Approved',
    "Collateral for loan application #{$loanApplicationId} is approved.",
    notifiableType: 'loan_application',
    notifiableId: $loanApplicationId
);
```

## Supported Resource Types

The system currently supports redirecting to these resources:

| Type                     | Route                                                   |
| ------------------------ | ------------------------------------------------------- |
| `loan_application`       | `filament.admin.resources.loan-applications.edit`       |
| `membership_application` | `filament.admin.resources.membership-applications.edit` |
| `member_detail`          | `filament.admin.resources.member-details.edit`          |
| `member`                 | `filament.admin.resources.member-details.edit`          |
| `profile`                | `filament.admin.resources.profiles.edit`                |

## Frontend Integration

When you fetch notifications via API, each notification will include a `redirect_url` field:

```javascript
// Fetch notifications
const response = await fetch("/api/notifications");
const data = await response.json();

// Click notification to navigate
notifications.forEach((notif) => {
    if (notif.redirect_url) {
        // Redirect to the resource
        window.location.href = notif.redirect_url;
    }
});
```

## API Response Example

```json
{
    "notifications": [
        {
            "id": 1,
            "title": "Collateral Approved",
            "description": "Collateral for loan application #123 has been approved.",
            "status": "unseen",
            "is_read": false,
            "notifiable_type": "loan_application",
            "notifiable_id": 123,
            "redirect_url": "/admin/resources/loan-applications/123/edit",
            "created_at": "2026-04-15T10:30:00Z"
        }
    ]
}
```

## Implementation Checklist

When adding new notifications in your code:

1. ✅ Include `notifiable_type` parameter
2. ✅ Include `notifiable_id` parameter
3. ✅ Use consistent type names from the supported list above
4. ✅ Test that the redirect URL works correctly
5. ✅ Update the `Notification::getRedirectUrl()` method if adding new resource types
