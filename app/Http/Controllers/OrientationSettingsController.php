<?php

namespace App\Http\Controllers;

use App\Models\CoopSetting;

class OrientationSettingsController extends Controller
{
    public function show()
    {
        $questions = CoopSetting::get('orientation.questions', []);

        if (is_string($questions)) {
            $decoded = json_decode($questions, true);
            $questions = is_array($decoded) ? $decoded : [];
        }

        // Convert the questions to proper format, handling Livewire's UUID-keyed structure
        $formattedQuestions = [];
        if (is_array($questions)) {
            foreach ($questions as $question) {
                if (is_array($question) && isset($question['question'])) {
                    // Convert choices from UUID-keyed object to indexed array
                    $choices = $question['choices'] ?? [];
                    $choicesArray = [];
                    
                    if (is_array($choices)) {
                        foreach ($choices as $choice) {
                            if (is_array($choice) && isset($choice['value'])) {
                                $choicesArray[] = $choice;
                            }
                        }
                    }

                    $formattedQuestions[] = [
                        'question' => $question['question'] ?? '',
                        'choices' => $choicesArray,
                        'correct_answer' => $question['correct_answer'] ?? '',
                    ];
                }
            }
        }

        return response()->json([
            'zoom_link' => CoopSetting::get('orientation.zoom_link', ''),
            'video_link' => CoopSetting::get('orientation.video_link', ''),
            'passing_score' => (int) CoopSetting::get('orientation.passing_score', 75),
            'require_for_loan' => (bool) CoopSetting::get('orientation.require_for_loan', true),
            'questions' => $formattedQuestions,
        ]);
    }
}