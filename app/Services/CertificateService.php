<?php

namespace App\Services;

use App\Models\OrientationAssessment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    /**
     * Generate a PDF certificate for a passed orientation assessment.
     * Returns the storage path of the generated file.
     */
    public function generate(OrientationAssessment $assessment): string
    {
        // Eager load relationships if not already loaded
        $assessment->loadMissing(['application', 'orientation']);

        $application = $assessment->application;
        $orientation = $assessment->orientation;

        $data = [
            'member_name'      => $this->formatName(
                                    $application->first_name,
                                    $application->middle_name,
                                    $application->last_name
                                  ),
            'orientation_title'=> $orientation->title,
            'score'            => $assessment->score,
            'pass_threshold'   => $orientation->pass_threshold,
            'completed_date'   => $assessment->completed_at->format('F d, Y'),
            'issued_date'      => now()->format('F d, Y'),
            'attempt_number'   => $assessment->attempt_number,
            'coop_name'        => config('coop.name', 'Community Cooperative'),
            'coop_address'     => config('coop.address', ''),
            'certificate_no'   => $this->generateCertificateNumber($assessment),
        ];

        $pdf = Pdf::loadView('certificates.orientation', $data)
                  ->setPaper('a4', 'landscape')
                  ->setOptions([
                      'defaultFont'     => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'dpi'             => 150,
                  ]);

        $path = "certificates/orientation_{$assessment->id}.pdf";

        // Store in public disk
        Storage::disk('public')->put($path, $pdf->output());

        // Update the assessment record
        $assessment->update([
            'certificate_path'            => $path,
            'certificate_generated_at'    => now(),
        ]);

        return $path;
    }

    /**
     * Regenerate a certificate (e.g. triggered manually by admin).
     */
    public function regenerate(OrientationAssessment $assessment): string
    {
        // Delete old file if it exists
        if ($assessment->certificate_path) {
            Storage::disk('public')->delete($assessment->certificate_path);
        }

        return $this->generate($assessment);
    }

    /**
     * Format full name — handles nullable middle name.
     */
    private function formatName(string $first, ?string $middle, string $last): string
    {
        return trim(
            $first . ' ' .
            ($middle ? strtoupper(substr($middle, 0, 1)) . '. ' : '') .
            $last
        );
    }

    /**
     * Generate a unique human-readable certificate number.
     * Format: COOP-ORI-{YEAR}-{ASSESSMENT_ID padded to 5 digits}
     * Example: COOP-ORI-2026-00042
     */
    private function generateCertificateNumber(OrientationAssessment $assessment): string
    {
        return sprintf(
            'COOP-ORI-%s-%05d',
            now()->format('Y'),
            $assessment->id
        );
    }
}