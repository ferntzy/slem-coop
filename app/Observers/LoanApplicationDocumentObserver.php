<?php

namespace App\Observers;

use App\Models\LoanApplicationDocument;
use App\Services\NotificationService;

class LoanApplicationDocumentObserver
{
    public function created(LoanApplicationDocument $document): void
    {
        $application = $document->application;
        if (! $application) {
            return;
        }

        $profileId = $application->primary_profile_id ?? $application->profile_id;
        if (! $profileId) {
            return;
        }

        // Notify the member that a document was uploaded
        app(NotificationService::class)->notifyDocumentUpload(
            $profileId,
            $document->document_type
        );

        // Notify admins of the upload
        app(NotificationService::class)->notifyAdmins(
            'Loan Application Document Uploaded',
            "Document '{$document->document_type}' has been uploaded for loan application #{$application->loan_application_id}"
        );
    }
}
