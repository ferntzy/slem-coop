<?php

use App\Models\Profile;
use App\Models\User;
use App\Models\UserPushToken;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Http;

test('notifyUserWithPush sends expo request when token exists', function () {
    Http::fake([
        'https://exp.host/--/api/v2/push/send' => Http::response(['data' => ['status' => 'ok']], 200),
    ]);

    $user = User::create([
        'username' => 'push-user-'.uniqid(),
        'password' => 'password',
        'profile_id' => null,
        'coop_id' => null,
        'avatar' => null,
        'image_path' => null,
        'is_active' => true,
    ]);

    UserPushToken::create([
        'user_id' => $user->user_id,
        'push_token' => 'ExponentPushToken[TEST]',
        'device_type' => 'android',
        'is_active' => true,
    ]);

    $notification = app(NotificationService::class)->notifyUserWithPush(
        $user->user_id,
        'Profile update confirmed',
        'Your profile was updated.'
    );

    expect($notification->id)->not->toBeNull();
    expect($notification->user_id)->toBe($user->user_id);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://exp.host/--/api/v2/push/send'
            && $request['to'] === 'ExponentPushToken[TEST]'
            && $request['title'] === 'Profile update confirmed'
            && $request['body'] === 'Your profile was updated.';
    });
});

test('notifyUserWithPush does not send when no token exists', function () {
    Http::fake();

    $user = User::create([
        'username' => 'no-token-user-'.uniqid(),
        'password' => 'password',
        'profile_id' => null,
        'coop_id' => null,
        'avatar' => null,
        'image_path' => null,
        'is_active' => true,
    ]);

    $notification = app(NotificationService::class)->notifyUserWithPush(
        $user->user_id,
        'Profile update confirmed',
        'Your profile was updated.'
    );

    expect($notification->id)->not->toBeNull();

    Http::assertNothingSent();
});

test('notifyProfileWithPush sends expo request for a profile user', function () {
    Http::fake([
        'https://exp.host/--/api/v2/push/send' => Http::response(['data' => ['status' => 'ok']], 200),
    ]);

    $profile = Profile::create([
        'first_name' => 'Push',
        'last_name' => 'Profile',
        'email' => 'push.profile.'.uniqid().'@example.com',
        'roles_id' => null,
    ]);

    $user = User::create([
        'username' => 'profile-user-'.uniqid(),
        'password' => 'password',
        'profile_id' => $profile->profile_id,
        'coop_id' => null,
        'avatar' => null,
        'image_path' => null,
        'is_active' => true,
    ]);

    UserPushToken::create([
        'user_id' => $user->user_id,
        'push_token' => 'ExponentPushToken[PROFILE]',
        'device_type' => 'android',
        'is_active' => true,
    ]);

    $notification = app(NotificationService::class)->notifyProfileWithPush(
        $profile->profile_id,
        'Loan application approved',
        'Your loan application has been approved!'
    );

    expect($notification)->not->toBeNull();
    expect($notification->user_id)->toBe($user->user_id);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://exp.host/--/api/v2/push/send'
            && $request['to'] === 'ExponentPushToken[PROFILE]'
            && $request['title'] === 'Loan application approved'
            && $request['body'] === 'Your loan application has been approved!';
    });
});
