<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('authenticated member can change their password', function () {
    $user = User::query()->create([
        'username' => 'temp-password-user-'.uniqid(),
        'password' => 'Temporary123!',
        'profile_id' => null,
        'coop_id' => null,
        'avatar' => null,
        'image_path' => null,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $this->actingAs($user)->get('/api/auth-status')
        ->assertSuccessful()
        ->assertJson([
            'authenticated' => true,
            'must_change_password' => true,
        ]);

    $response = $this->actingAs($user)->postJson(route('member.password.update'), [
        'current_password' => 'Temporary123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'message' => 'Password updated successfully.',
    ]);

    $user->refresh();

    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
    expect(Hash::check('Temporary123!', $user->password))->toBeFalse();
    $this->actingAs($user)->get('/api/auth-status')
        ->assertSuccessful()
        ->assertJson([
            'authenticated' => true,
            'must_change_password' => false,
        ]);
});

test('member browser form redirects back after changing password', function () {
    $user = User::query()->create([
        'username' => 'browser-password-user-'.uniqid(),
        'password' => 'Temporary123!',
        'profile_id' => null,
        'coop_id' => null,
        'avatar' => null,
        'image_path' => null,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $response = $this->actingAs($user)->post(route('member.password.update'), [
        'current_password' => 'Temporary123!',
        'password' => 'BrowserPassword123!',
        'password_confirmation' => 'BrowserPassword123!',
    ]);

    $response->assertRedirect(route('member.password.form'));
    $response->assertSessionHas('status', 'Password updated successfully.');

    $user->refresh();

    expect(Hash::check('BrowserPassword123!', $user->password))->toBeTrue();
    expect($user->must_change_password)->toBeFalse();
});

test('member can open password change page', function () {
    $user = User::query()->create([
        'username' => 'password-page-user-'.uniqid(),
        'password' => 'Temporary123!',
        'profile_id' => null,
        'coop_id' => null,
        'avatar' => null,
        'image_path' => null,
        'is_active' => true,
        'must_change_password' => true,
    ]);
    $user->assignRole('Member');

    $response = $this->actingAs($user)->get(route('member.password.form'));

    $response->assertSuccessful();
    $response->assertSee('Member Password Update');
    $response->assertSee('Change Password');
});
