<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoopSetting;

class OrientationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        CoopSetting::updateOrCreate(
            ['key' => 'orientation.zoom_link'],
            [
                'value' => 'https://us04web.zoom.us/j/75134744605?pwd=VRhW86X9b4dv0zIMLfuZrrmQ3PTBRe.1',
                'type' => 'string',
                'group' => 'orientation',
                'label' => 'Zoom Link',
                'description' => 'Pre-membership Zoom orientation link',
            ]
        );

        CoopSetting::updateOrCreate(
            ['key' => 'orientation.video_link'],
            [
                'value' => 'https://www.youtube.com/embed/MjfB-WmX91Y?autoplay=0&rel=0',
                'type' => 'string',
                'group' => 'orientation',
                'label' => 'Video Embed Link',
                'description' => 'Embedded orientation video link',
            ]
        );

        CoopSetting::updateOrCreate(
            ['key' => 'orientation.passing_score'],
            [
                'value' => '75',
                'type' => 'integer',
                'group' => 'orientation',
                'label' => 'Passing Score',
                'description' => 'Minimum passing score for post-orientation assessment',
            ]
        );

        CoopSetting::updateOrCreate(
            ['key' => 'orientation.require_for_loan'],
            [
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'orientation',
                'label' => 'Require Orientation for Loan',
                'description' => 'Whether orientation must be completed before loan application',
            ]
        );

        CoopSetting::updateOrCreate(
            ['key' => 'orientation.questions'],
            [
                'value' => json_encode([
                    [
                        'question' => 'What is one of the main purposes of joining the cooperative?',
                        'choices' => [
                            ['value' => 'To become eligible for cooperative services and benefits'],
                            ['value' => 'To avoid submitting requirements'],
                            ['value' => 'To skip membership approval'],
                            ['value' => 'To immediately receive a loan without evaluation'],
                        ],
                        'correct_answer' => 'To become eligible for cooperative services and benefits',
                    ],
                    [
                        'question' => 'Before applying for a loan, a member must first:',
                        'choices' => [
                            ['value' => 'Complete orientation and pass the assessment'],
                            ['value' => 'Only upload a valid ID'],
                            ['value' => 'Only attend Zoom orientation'],
                            ['value' => 'Ask a staff member to approve them manually'],
                        ],
                        'correct_answer' => 'Complete orientation and pass the assessment',
                    ],
                    [
                        'question' => 'Why is the post-orientation assessment important?',
                        'choices' => [
                            ['value' => 'It checks whether the applicant understood the orientation'],
                            ['value' => 'It replaces membership approval'],
                            ['value' => 'It automatically approves all loan applications'],
                            ['value' => 'It removes the need for documents'],
                        ],
                        'correct_answer' => 'It checks whether the applicant understood the orientation',
                    ],
                    [
                        'question' => 'What should a member do after finishing the orientation video?',
                        'choices' => [
                            ['value' => 'Answer the assessment and meet the passing score'],
                            ['value' => 'Close the application immediately'],
                            ['value' => 'Skip the remaining steps'],
                            ['value' => 'Submit without reviewing anything'],
                        ],
                        'correct_answer' => 'Answer the assessment and meet the passing score',
                    ],
                    [
                        'question' => 'If the passing score is 75%, what happens when the applicant scores below it?',
                        'choices' => [
                            ['value' => 'They are not yet eligible to proceed'],
                            ['value' => 'They are automatically approved'],
                            ['value' => 'They can ignore the result'],
                            ['value' => 'They become eligible for loan immediately'],
                        ],
                        'correct_answer' => 'They are not yet eligible to proceed',
                    ],
                ]),
                'type' => 'json',
                'group' => 'orientation',
                'label' => 'Orientation Questions',
                'description' => 'Default post-orientation assessment questions',
            ]
        );
    }
}