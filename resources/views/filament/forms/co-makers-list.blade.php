@php
    $memberId = $get('member_id');
    $member = $memberId
        ? \App\Models\MemberDetail::with('coMakers')->find($memberId)
        : null;

    $coMakers = $member?->coMakers ?? collect();
@endphp

@if ($coMakers->isEmpty())
    <div style="color: #6b7280; font-size: 14px;">
        No co-makers added.
    </div>
@else
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @foreach ($coMakers as $coMaker)
            <div style="border: 1px solid #d1d5db; border-radius: 10px; padding: 14px;">
                <div><strong>Full Name:</strong> {{ $coMaker->full_name ?? '—' }}</div>
                <div><strong>Relationship:</strong> {{ $coMaker->relationship ?? '—' }}</div>
                <div><strong>Contact Number:</strong> {{ $coMaker->contact_number ?? '—' }}</div>
                <div><strong>Address:</strong> {{ $coMaker->address ?? '—' }}</div>
                <div><strong>Occupation:</strong> {{ $coMaker->occupation ?? '—' }}</div>
                <div><strong>Employer:</strong> {{ $coMaker->employer_name ?? '—' }}</div>
                <div><strong>Monthly Income:</strong>
                    {{ $coMaker->monthly_income !== null ? '₱' . number_format($coMaker->monthly_income, 2) : '—' }}
                </div>
            </div>
        @endforeach
    </div>
@endif