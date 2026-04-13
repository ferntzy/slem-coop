<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanApplicationPrintController extends Controller
{
    public function download(LoanApplication $loanApplication)
    {
        $loanApplication->load([
            'member.profile',
            'member.membershipType',
            'member.branch',
            'member.spouse',
            'member.coMakers',
            'type',
            'loanAccount',
        ]);

        $member = $loanApplication->member;
        $profile = $member?->profile;
        $spouse = $member?->spouse;
        $coMakers = $member?->coMakers ?? collect();

        $data = [
            'loanApplication' => $loanApplication,
            'member' => $member,
            'profile' => $profile,
            'spouse' => $spouse,
            'coMakers' => $coMakers,
            'page1Background' => public_path('forms/loan-application-page-1.jpg'),
            'page2Background' => public_path('forms/loan-application-page-2.jpg'),
        ];

        $memberName = \Str::slug($loanApplication->member?->profile?->full_name ?? 'member');

        $pdf = Pdf::loadView('pdf.loan-application-auto-fill', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream($memberName . '.pdf'); 
    }
}