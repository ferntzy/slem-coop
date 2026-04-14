<?php

use App\Http\Controllers\Api\AboutPageController;
use App\Http\Controllers\Api\AccountDashboard;
use App\Http\Controllers\Api\AccountOfficerController;
use App\Http\Controllers\Api\ContactPageController;
use App\Http\Controllers\Api\LoanApplicationController as LoanOfficerApplicationController;
use App\Http\Controllers\Api\LoanOfficerProfileController;
use App\Http\Controllers\HeroNewsEventController;
use App\Http\Controllers\LoanApplication as ControllersLoanApplication;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\MobileAuth\Auth;
use App\Http\Controllers\MobileMemberGeneral;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsEventController;
use App\Http\Controllers\Notifications;
use App\Http\Controllers\OrientationController;
use App\Http\Controllers\OrientationSettingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/mobile-login', [Auth::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile-set-pin', [Auth::class, 'setPin']);
    Route::post('/mobile-verify-pin', [Auth::class, 'verifyPin']);
});

Route::get('/membership-types', [MembershipApplicationController::class, 'membershipTypes']);
Route::get('/branches', [MembershipApplicationController::class, 'branches']);
Route::post('/profiles', [ProfileController::class, 'store']);

Route::post('/membership-application', [MembershipApplicationController::class, 'store']);

// edit profile
Route::post('/edit-profile', [ProfileController::class, 'editProfile']);

// loan mobile routes
Route::put('/loan-officer/profile/{profileId}', [LoanOfficerProfileController::class, 'update']);
Route::get('/loan-officer/profile/{profileId}', [LoanOfficerProfileController::class, 'show']);
// loan applications
Route::get('/loan-applications', [LoanOfficerApplicationController::class, 'index']);
Route::get('/loan-applications/{id}', [LoanOfficerApplicationController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    // loan application actions
    Route::post('/loan-applications/{id}/approve', [LoanOfficerApplicationController::class, 'approve']);
    Route::post('/loan-applications/{id}/reloan', [LoanOfficerApplicationController::class, 'reloan']);
    Route::post('/loan-applications/{id}/set-penalty-rule', [LoanOfficerApplicationController::class, 'setPenaltyRule']);
    Route::post('/loan-applications/{id}/under-review', [LoanOfficerApplicationController::class, 'markUnderReview']);
    Route::get('/loan-applications/{id}/download-form', [LoanOfficerApplicationController::class, 'downloadLoanForm']);
    Route::post('/loan-applications/{id}/reject', [LoanOfficerApplicationController::class, 'reject']);
    Route::post('/loan-applications/{id}/cancel', [LoanOfficerApplicationController::class, 'cancel']);
});

// account officer
Route::put('/account-officer/profile/{profileId}', [AccountOfficerController::class, 'update']);
Route::get('/account-officer/members', [AccountDashboard::class, 'activemembers']);
Route::get('/account-officer/loan-disbursements', [AccountDashboard::class, 'loanDisbursements']);
Route::get('/account-officer/collections', [AccountDashboard::class, 'collections']);
Route::get('/account-officer/loans', [AccountDashboard::class, 'activeLoanAccounts']);
Route::get('/account-officer/pending-loans', [AccountDashboard::class, 'pendingLoanApplications']);
Route::get('/account-officer/delinquent', [AccountDashboard::class, 'delinquentMembers']);
Route::get('/account-officer/loan-disbursements', [AccountDashboard::class, 'loanDisbursements']);
Route::get('/account-officer/collections', [AccountDashboard::class, 'collections']);
Route::get('/account-officer/loans', [AccountDashboard::class, 'activeLoanAccounts']);
Route::get('/account-officer/pending-loans', [AccountDashboard::class, 'pendingLoanApplications']);
Route::get('/account-officer/delinquent', [AccountDashboard::class, 'delinquentMembers']);

Route::get('/about', [AboutPageController::class, 'show']);

Route::get('/contact', [ContactPageController::class, 'show']);
Route::post('/contact/submit', [ContactPageController::class, 'submit']);

Route::get('/orientation', [OrientationController::class, 'show']);
Route::post('/orientation/video-watched', [OrientationController::class, 'markVideoWatched']);
Route::post('/orientation/submit', [OrientationController::class, 'submit']);

// member apis
// member dashboard datas
Route::post('/member/dashboard-data', [MobileMemberGeneral::class, 'getDashboardData']);

// member active-loans
Route::post('/member/active-loans', [MobileMemberGeneral::class, 'getActiveLoansData']);

// member loan history
Route::post('/member/loan-history', [MobileMemberGeneral::class, 'getLoanHistoryData']);

// member loan application
Route::post('/send-application-form', [ControllersLoanApplication::class, 'applyLoan']);
Route::post('/member/fetch-loan-applications', [ControllersLoanApplication::class, 'viewMemberLoanApplications']);
Route::post('/cancel-loan-applications', [ControllersLoanApplication::class, 'cancelLoanApplication']);

// member notifications
Route::post('/member/fetch-notifications', [Notifications::class, 'fetchNotifications']);
Route::post('member/fetch-unread-notifications', [Notifications::class, 'fetchUnreadNotifications']);
Route::post('/member/delete-notification', [Notifications::class, 'deleteNotification']);
Route::post('/member/mark-notification-seen', [Notifications::class, 'markAsRead']);

Route::get('/newsevent', [NewsEventController::class, 'show']);
Route::get('/newsevent/hero', [HeroNewsEventController::class, 'show']);
Route::get('/newsevent/news', [NewsController::class, 'show']);

Route::get('/orientation-settings', [OrientationSettingsController::class, 'show']);
