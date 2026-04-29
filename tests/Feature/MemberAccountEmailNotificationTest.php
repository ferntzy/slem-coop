<?php

use App\Models\MembershipApplication;
use App\Models\MembershipType;
use App\Models\Profile;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

uses(Tests\TestCase::class)->in('Feature');

test('membership application approval creates user and sends email with tracking', function () {
    // Get or create a membership type
    $membershipType = MembershipType::first() ?? MembershipType::create([
        'membership_type_id' => 1,
        'name' => 'Associate Member',
        'description' => 'Test membership type',
    ]);

    // Get a valid user ID for created_by
    $adminUser = User::first() ?? User::first();

    // Create a membership application
    $application = MembershipApplication::create([
        'first_name' => 'Test',
        'middle_name' => 'User',
        'last_name' => 'Member',
        'email' => 'test.member.'.uniqid().'@example.com',
        'mobile_number' => '09171234567',
        'birthdate' => '1990-05-15',
        'sex' => 'Male',
        'civil_status' => 'Single',
        'address' => '123 Test St',
        'membership_type_id' => $membershipType->membership_type_id,
        'status' => 'pending',
        'application_date' => now(),
        'created_by' => $adminUser?->user_id ?? 1,
    ]);

    // Assert no user/profile exists yet
    expect(Profile::where('email', $application->email)->first())->toBeNull();
    expect(SentEmail::count())->toBe(0);

    // Act: Approve the application
    $application->update(['status' => 'approved']);

    // Assert profile was created
    $profile = Profile::where('email', $application->email)->first();
    expect($profile)->not->toBeNull();
    expect($profile->first_name)->toBe('Test');

    // Assert user was created with auto-generated password
    $user = User::where('profile_id', $profile->profile_id)->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('Member'))->toBeTrue();

    // Assert SentEmail record was created (inside transaction)
    $sentEmail = SentEmail::where('email', $application->email)->first();
    expect($sentEmail)->not->toBeNull();
    expect($sentEmail->subject)->toBe('Your SLEM Coop Member Account is Ready');
    expect($sentEmail->mailable_class)->toBe(App\Mail\MemberAccountReady::class);
});

test('sent_email record is created inside transaction but mail is queued after commit', function () {
    Mail::fake();

    // Get or create a membership type
    $membershipType = MembershipType::first() ?? MembershipType::create([
        'membership_type_id' => 2,
        'name' => 'Regular Member',
        'description' => 'Test membership type',
    ]);

    // Get a valid user ID for created_by
    $adminUser = User::first();

    // Create application
    $application = MembershipApplication::create([
        'first_name' => 'Commit',
        'middle_name' => 'Test',
        'last_name' => 'User',
        'email' => 'commit.test.'.uniqid().'@example.com',
        'mobile_number' => '09171234567',
        'birthdate' => '1990-05-15',
        'sex' => 'Male',
        'civil_status' => 'Single',
        'address' => '123 Test St',
        'membership_type_id' => $membershipType->membership_type_id,
        'status' => 'pending',
        'application_date' => now(),
        'created_by' => $adminUser?->user_id ?? 1,
    ]);

    // Approve
    $application->update(['status' => 'approved']);

    // Assert SentEmail was created (inside transaction)
    $sentEmail = SentEmail::where('email', $application->email)->first();
    expect($sentEmail)->not->toBeNull();

    // Assert mail was queued (after commit)
    Mail::assertQueued(App\Mail\MemberAccountReady::class);
});

test('profile creation survives when mail fails after commit', function () {
    Mail::fake();

    // Get or create a membership type
    $membershipType = MembershipType::first() ?? MembershipType::create([
        'membership_type_id' => 3,
        'name' => 'Test Member',
        'description' => 'Test membership type',
    ]);

    // Get a valid user ID for created_by
    $adminUser = User::first();

    // Make the mailable throw an exception when queued
    Mail::shouldReceive('to')->andThrow(new \Exception('Mail service unavailable'));

    // Create application
    $application = MembershipApplication::create([
        'first_name' => 'Fail',
        'middle_name' => 'Test',
        'last_name' => 'User',
        'email' => 'fail.test.'.uniqid().'@example.com',
        'mobile_number' => '09171234567',
        'birthdate' => '1990-05-15',
        'sex' => 'Male',
        'civil_status' => 'Single',
        'address' => '123 Test St',
        'membership_type_id' => $membershipType->membership_type_id,
        'status' => 'pending',
        'application_date' => now(),
        'created_by' => $adminUser?->user_id ?? 1,
    ]);

    // Approve - this should NOT throw even if mail fails
    $application->update(['status' => 'approved']);

    // Assert profile was created (survived the mail failure)
    $profile = Profile::where('email', $application->email)->first();
    expect($profile)->not->toBeNull();

    // Assert user was created
    $user = User::where('profile_id', $profile->profile_id)->first();
    expect($user)->not->toBeNull();

    // Assert SentEmail record exists with failure info
    $sentEmail = SentEmail::where('email', $application->email)->first();
    expect($sentEmail)->not->toBeNull();
    expect($sentEmail->failed_at)->not->toBeNull();
    expect($sentEmail->failure_reason)->toContain('Mail service unavailable');
});

test('approving already linked application does not create duplicate', function () {
    // Get or create a membership type
    $membershipType = MembershipType::first() ?? MembershipType::create([
        'membership_type_id' => 4,
        'name' => 'Dup Member',
        'description' => 'Test membership type',
    ]);

    // Get a valid user ID for created_by
    $adminUser = User::first();

    // Create application and profile/user first
    $application = MembershipApplication::create([
        'first_name' => 'Dup',
        'middle_name' => 'Test',
        'last_name' => 'User',
        'email' => 'dup.test.'.uniqid().'@example.com',
        'mobile_number' => '09171234567',
        'birthdate' => '1990-05-15',
        'sex' => 'Male',
        'civil_status' => 'Single',
        'address' => '123 Test St',
        'membership_type_id' => $membershipType->membership_type_id,
        'status' => 'pending',
        'application_date' => now(),
        'created_by' => $adminUser?->user_id ?? 1,
    ]);

    // First approval
    $application->update(['status' => 'approved']);
    $userCountAfterFirstApproval = User::count();

    // Second approval (should not create duplicate)
    $application->update(['status' => 'approved']);
    $userCountAfterSecondApproval = User::count();

    // Assert no duplicate user was created
    expect($userCountAfterFirstApproval)->toBe($userCountAfterSecondApproval);
});