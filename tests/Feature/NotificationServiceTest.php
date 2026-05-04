<?php

use App\Models\Notification;
use App\Models\Profile;
use App\Models\SentEmail;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class)->in('Feature');

test('daily collection submission notification is sent to admin and manager roles', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);

    $admin = User::create([
        'username' => 'admin-user',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);
    $manager = User::create([
        'username' => 'manager-user',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $admin->assignRole($adminRole);
    $manager->assignRole($managerRole);

    app(NotificationService::class)->notifyDailyCollectionSubmitted('AO Test', 12345.67);

    expect(Notification::where('title', 'Daily Collection Entry Submitted')->count())->toBe(2);
    expect(Notification::where('user_id', $admin->user_id)->exists())->toBeTrue();
    expect(Notification::where('user_id', $manager->user_id)->exists())->toBeTrue();
});

test('due date reminder sends notification, email, and sms when profile contact exists', function () {
    Mail::fake();

    $profile = Profile::create([
        'first_name' => 'Due',
        'last_name' => 'Reminder',
        'email' => 'due.reminder@example.com',
        'mobile_number' => '09171234567',
    ]);

    $user = User::create([
        'username' => 'due-user',
        'password' => bcrypt('password'),
        'profile_id' => $profile->profile_id,
        'is_active' => true,
    ]);

    $smsMock = Mockery::mock(SmsService::class);
    $smsMock->shouldReceive('sendBulkSms')
        ->once()
        ->with(['+639171234567'], Mockery::type('string'))
        ->andReturn(['status' => 'ok']);

    app()->instance(SmsService::class, $smsMock);

    app(NotificationService::class)->notifyDueDateReminder(
        $profile->profile_id,
        1234.56,
        'Jun 15, 2026',
        3,
    );

    expect(Notification::where('user_id', $user->user_id)->where('title', 'Payment Due in 3 Days')->exists())->toBeTrue();
    expect(SentEmail::where('email', $profile->email)->where('type', 'notification')->exists())->toBeTrue();
});

