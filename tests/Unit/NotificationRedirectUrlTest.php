<?php

use App\Models\Notification;

uses(Tests\TestCase::class)->in('Unit');

test('notification redirect url uses view route for membership and member detail notifications', function () {
    $membershipNotification = new Notification([
        'notifiable_type' => 'membership_application',
        'notifiable_id' => 123,
    ]);

    $memberDetailNotification = new Notification([
        'notifiable_type' => 'member_detail',
        'notifiable_id' => 456,
    ]);

    expect($membershipNotification->getRedirectUrl())->toBe(route('filament.admin.resources.membership-applications.view', ['record' => 123]));
    expect($memberDetailNotification->getRedirectUrl())->toBe(route('filament.admin.resources.member-details.view', ['record' => 456]));
});
