<?php

test('it returns orientation settings for the membership application', function () {
    $response = $this->getJson('/api/orientation-settings');

    $response->assertOk()
        ->assertJsonStructure([
            'zoom_link',
            'orientation_zoom_link',
            'video_link',
            'orientation_video_link',
            'passing_score',
            'require_for_loan',
            'questions',
        ])
        ->assertJsonPath('video_link', fn ($value) => is_string($value) && $value !== '')
        ->assertJsonPath('zoom_link', fn ($value) => is_string($value) && $value !== '');
});
