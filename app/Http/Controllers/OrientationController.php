<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use App\Models\Orientation;
use App\Models\OrientationAnswer;
use App\Models\OrientationAssessment;
use App\Services\CertificateService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrientationController extends Controller
{
    // ── 1. Get Active Orientation ────────────────────────────────────────────

    public function show(): JsonResponse
    {
        $orientation = Orientation::with([
            'questions' => fn ($q) => $q->orderBy('order'),
            'questions.choices',
        ])
            ->where('is_active', true)
            ->latest()
            ->first();

        if (! $orientation) {
            return response()->json([
                'message' => 'No active orientation found.',
            ], 404);
        }

        return response()->json([
            'id' => $orientation->id,
            'title' => $orientation->title,
            'description' => $orientation->description,
            'video_url' => asset('storage/'.$orientation->video_path),
            'pass_threshold' => $orientation->pass_threshold,
            'allow_retakes' => $orientation->allow_retakes,
            'max_attempts' => $orientation->max_attempts,
            'questions' => $orientation->questions->map(fn ($q) => [
                'id' => $q->id,
                'question' => $q->question,
                'points' => $q->points,

                'choices' => $q->choices->shuffle()->map(fn ($c) => [
                    'id' => $c->id,
                    'text' => $c->choice_text,
                ]),
            ]),
        ]);
    }

    // ── 2. Get Application Orientation Status ────────────────────────────────

    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:membership_applications,id',
        ]);

        $application = MembershipApplication::findOrFail($request->application_id);
        $orientation = Orientation::getActive();

        if (! $orientation) {
            return response()->json(['message' => 'No active orientation.'], 404);
        }

        $latestAssessment = $application->latestAssessment;
        $attempts = $application->orientationAssessments()
            ->where('orientation_id', $orientation->id)
            ->count();

        return response()->json([
            'has_passed' => $application->hasPassedOrientation(),
            'can_retake' => $application->canRetakeOrientation(),
            'remaining_attempts' => $application->remainingAttempts(),
            'total_attempts' => $attempts,
            'latest_score' => $latestAssessment?->score,
            'latest_passed' => $latestAssessment?->passed,
            'video_watched' => ! is_null($latestAssessment?->video_watched_at),
            'certificate_url' => $latestAssessment?->certificate_path
                                    ? asset('storage/'.$latestAssessment->certificate_path)
                                    : null,
        ]);
    }

    // ── 3. Mark Video as Watched ─────────────────────────────────────────────

    public function markVideoWatched(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:membership_applications,id',
        ]);

        $application = MembershipApplication::findOrFail($request->application_id);
        $orientation = Orientation::getActive();

        if (! $orientation) {
            return response()->json(['message' => 'No active orientation found.'], 404);
        }

        // Already passed — no need to create a new assessment
        if ($application->hasPassedOrientation()) {
            return response()->json(['message' => 'Already passed orientation.']);
        }

        // Get or create an in-progress assessment for this attempt
        $assessment = OrientationAssessment::firstOrCreate(
            [
                'membership_application_id' => $application->id,
                'orientation_id' => $orientation->id,
                'completed_at' => null, // only match incomplete ones
            ],
            [
                'attempt_number' => $this->nextAttemptNumber($application->id, $orientation->id),
            ]
        );

        // Only set video_watched_at once
        if (is_null($assessment->video_watched_at)) {
            $assessment->update(['video_watched_at' => now()]);

            if ($application->profile_id) {
                app(NotificationService::class)->notifyOrientationStarted(
                    $application->profile_id,
                    $orientation->title,
                );
            }
        }

        return response()->json([
            'message' => 'Video marked as watched. You may now take the assessment.',
            'assessment_id' => $assessment->id,
        ]);
    }

    // ── 4. Submit Assessment ─────────────────────────────────────────────────

    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:membership_applications,id',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:orientation_questions,id',
            'answers.*.choice_id' => 'required|exists:orientation_choices,id',
        ]);

        $application = MembershipApplication::findOrFail($request->application_id);
        $orientation = Orientation::with('questions.correctChoice')
            ->where('is_active', true)
            ->latest()
            ->firstOrFail();

        // ── Guards ───────────────────────────────────────────────────────────

        if ($application->hasPassedOrientation()) {
            return response()->json([
                'message' => 'You have already passed the orientation.',
            ], 422);
        }

        if (
            $application->orientationAssessments()->exists() &&
            ! $application->canRetakeOrientation()
        ) {
            return response()->json([
                'message' => 'You have no remaining attempts. Please contact the cooperative.',
            ], 422);
        }

        // ── Get the current in-progress assessment ───────────────────────────

        $assessment = OrientationAssessment::where('membership_application_id', $application->id)
            ->where('orientation_id', $orientation->id)
            ->whereNotNull('video_watched_at')  // must have watched video
            ->whereNull('completed_at')          // must not be completed yet
            ->latest()
            ->first();

        if (! $assessment) {
            return response()->json([
                'message' => 'Please watch the full orientation video before submitting.',
            ], 422);
        }

        // ── Guard: prevent double submission ─────────────────────────────────

        if ($assessment->answers()->exists()) {
            return response()->json([
                'message' => 'Assessment already submitted.',
            ], 422);
        }

        // ── Score Calculation ─────────────────────────────────────────────────

        return DB::transaction(function () use ($request, $assessment, $orientation, $application) {
            $totalPoints = $orientation->questions->sum('points');
            $earnedPoints = 0;

            foreach ($request->answers as $ans) {
                $question = $orientation->questions->find($ans['question_id']);

                if ($question && $question->correctChoice?->id == $ans['choice_id']) {
                    $earnedPoints += $question->points;
                }

                OrientationAnswer::create([
                    'assessment_id' => $assessment->id,
                    'question_id' => $ans['question_id'],
                    'choice_id' => $ans['choice_id'],
                ]);
            }

            $score = $totalPoints > 0 ? (int) round(($earnedPoints / $totalPoints) * 100) : 0;
            $passed = $score >= $orientation->pass_threshold;

            $assessment->update([
                'score' => $score,
                'passed' => $passed,
                'completed_at' => now(),
            ]);

            // ── Auto-generate certificate if passed ───────────────────────────
            $certificateUrl = null;

            if ($passed) {
                $path = app(CertificateService::class)->generate($assessment);
                $certificateUrl = asset('storage/'.$path);
            }

            if ($application->profile_id) {
                app(NotificationService::class)->notifyOrientationCompleted(
                    $application->profile_id,
                    $passed,
                    $score,
                    $certificateUrl,
                );
            }

            return response()->json([
                'score' => $score,
                'passed' => $passed,
                'threshold' => $orientation->pass_threshold,
                'can_retake' => ! $passed && $application->fresh()->canRetakeOrientation(),
                'remaining_attempts' => $application->fresh()->remainingAttempts(),
                'certificate_url' => $certificateUrl,
            ]);
        });
    }

    // ── 5. Download Certificate ───────────────────────────────────────────────

    public function downloadCertificate(Request $request): mixed
    {
        $request->validate([
            'application_id' => 'required|exists:membership_applications,id',
        ]);

        $assessment = OrientationAssessment::where('membership_application_id', $request->application_id)
            ->where('passed', true)
            ->whereNotNull('certificate_path')
            ->latest()
            ->first();

        if (! $assessment) {
            return response()->json(['message' => 'No certificate found.'], 404);
        }

        $fullPath = storage_path('app/public/'.$assessment->certificate_path);

        if (! file_exists($fullPath)) {
            return response()->json(['message' => 'Certificate file not found.'], 404);
        }

        return response()->download(
            $fullPath,
            'orientation-certificate.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function nextAttemptNumber(int $applicationId, int $orientationId): int
    {
        return OrientationAssessment::where('membership_application_id', $applicationId)
            ->where('orientation_id', $orientationId)
            ->count() + 1;
    }
}
