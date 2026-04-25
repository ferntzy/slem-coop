<?php

use App\Http\Controllers\Api\AboutPageController;
use App\Http\Controllers\Api\AccountDashboard;
use App\Http\Controllers\Api\AccountOfficerController;
use App\Http\Controllers\Api\ContactPageController;
use App\Http\Controllers\Api\LoanApplicationController as LoanOfficerApplicationController;
use App\Http\Controllers\Api\Loans;
use App\Http\Controllers\Api\Members;
use App\Http\Controllers\Api\RestructureApplicationController;
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
use App\Http\Controllers\SavingsAccount as ControllersSavingsAccount;
use App\Models\SavingsAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberDetailsController;
use App\Http\Controllers\Api\AccountMembersController;
use App\Http\Controllers\Api\AccountLoansController;
use App\Http\Controllers\Api\AccountLoanEditController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/mobile-login', [Auth::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile-set-pin', [Auth::class, 'setPin']);
    Route::post('/mobile-verify-pin', [Auth::class, 'verifyPin']);
});

Route::get('/active-all-loans', [MobileMemberGeneral::class, 'getNumberOfActiveLoans']);

Route::get('/membership-types', [MembershipApplicationController::class, 'membershipTypes']);
Route::get('/branches', [MembershipApplicationController::class, 'branches']);
Route::post('/profiles', [ProfileController::class, 'store']);

Route::post('/membership-application', [MembershipApplicationController::class, 'store']);

// edit profile
Route::post('/edit-profile', [ProfileController::class, 'editProfile']);

// account officer apis
// stat card data
Route::get('/active-members', [Members::class, 'getActiveMembers']);
Route::get('/inactive-members', [Members::class,  'inactiveMembers']);


// loan officer apis
// stat card data
Route::get('/approved-loans', [Loans::class, 'getApprovedLoans']);
Route::get('/pending-loans', [Loans::class, 'getPendingLoans']);

// loan applications
Route::get('/loan-applications', [Loans::class, 'getLoanApplications']);
Route::post('/get-loan-application-detail', [Loans::class, 'getLoanApplication']);

Route::get('/loan-applications-list', [LoanOfficerApplicationController::class, 'index']);
Route::get('/loan-applications/{id}', [LoanOfficerApplicationController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    // loan application actions
    Route::post('/loan-applications/{id}/approve', [LoanOfficerApplicationController::class, 'approve']);
    Route::post('/loan-applications/{id}/reloan', [LoanOfficerApplicationController::class, 'reloan']);
    Route::post('/loan-applications/{id}/set-penalty-rule', [LoanOfficerApplicationController::class, 'setPenaltyRule']);
    Route::post('/loan-applications/{id}/under-review', [LoanOfficerApplicationController::class, 'markUnderReview']);
    Route::get('/loan-applications/{id}/download-form', [LoanOfficerApplicationController::class, 'downloadLoanForm']);
    Route::post('/loan-applications/{id}/reject', [LoanOfficerApplicationController::class, 'reject']);

    // restructure applications
    Route::get('/restructure-applications/eligible-loans', [RestructureApplicationController::class, 'eligibleLoans']);
    Route::get('/restructure-applications', [RestructureApplicationController::class, 'index']);
    Route::post('/restructure-applications', [RestructureApplicationController::class, 'store']);
    Route::get('/restructure-applications/{id}', [RestructureApplicationController::class, 'show']);
    Route::post('/restructure-applications/{id}/under-review', [RestructureApplicationController::class, 'markUnderReview']);
    Route::post('/restructure-applications/{id}/approve', [RestructureApplicationController::class, 'approve']);
    Route::post('/restructure-applications/{id}/reject', [RestructureApplicationController::class, 'reject']);
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
Route::get('/members', [AccountMembersController::class, 'member']);
Route::get('/members/{id}', [AccountMembersController::class, 'show']);
Route::get('/loans', [AccountLoansController::class, 'Loans']);
Route::get('/all-loans', [AccountLoansController::class, 'allLoans']);
Route::get('/loans/{id}', [AccountLoansController::class, 'show']);
Route::get('/loan-edit', [AccountLoanEditController::class, 'index']);
Route::get('/loan-edit/{id}', [AccountLoanEditController::class, 'show']);

Route::prefix('member-details')->group(function () {
    Route::get('/', [MemberDetailsController::class, 'index']);
    Route::get('/{id}', [MemberDetailsController::class, 'show']);
    Route::post('/', [MemberDetailsController::class, 'store']);
    Route::put('/{id}', [MemberDetailsController::class, 'update']);
    Route::delete('/{id}', [MemberDetailsController::class, 'destroy']);
});



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
// member delinquent list
Route::get('/member/delinquent-list', [MobileMemberGeneral::class, 'getDelinquentMembersList']);



// member loan application
Route::post('/send-application-form', [ControllersLoanApplication::class, 'applyLoan']);
Route::post('/member/fetch-loan-applications', [ControllersLoanApplication::class, 'viewMemberLoanApplications']);
Route::post('/cancel-loan-applications', [ControllersLoanApplication::class, 'cancelLoanApplication']);

// member notifications
Route::post('/member/fetch-notifications', [Notifications::class, 'fetchNotifications']);
Route::post('/member/fetch-unread-notifications', [Notifications::class, 'fetchUnreadNotifications']);
Route::post('/member/delete-notification', [Notifications::class, 'deleteNotification']);
Route::post('/member/mark-notification-seen', [Notifications::class, 'markAsRead']);

Route::get('/member/savings-account-data', [ControllersSavingsAccount::class, 'getSavingsAccount']);



Route::get('/newsevent', [NewsEventController::class, 'show']);
Route::get('/newsevent/hero', [HeroNewsEventController::class, 'show']);
Route::get('/newsevent/news', [NewsController::class, 'show']);

Route::get('/orientation-settings', [OrientationSettingsController::class, 'show']);
