<?php

use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    public function generate(OrientationAssessment $assessment): string
    {
        $application = $assessment->application;
        $orientation = $assessment->orientation;

        $pdf = Pdf::loadView('certificates.orientation', [
            'name' => trim("{$application->first_name} {$application->last_name}"),
            'orientation' => $orientation->title,
            'score' => $assessment->score,
            'date' => $assessment->completed_at->format('F d, Y'),
            'coop_name' => config('app.coop_name', 'Community Cooperative'),
        ])->setPaper('a4', 'landscape');

        $path = "certificates/orientation_{$assessment->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $assessment->update([
            'certificate_path' => $path,
            'certificate_generated_at' => now(),
        ]);

        return $path;
    }
}
