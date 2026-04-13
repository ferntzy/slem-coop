<div class="space-y-4">
    <div class="flex justify-between items-center p-3 bg-gray-50 rounded border">
        <span class="font-semibold">Loan Status:</span>
        <span class="px-3 py-1 rounded 
            @if($record->status === 'Pending') bg-yellow-100 text-yellow-800
            @elseif($record->status === 'Approved') bg-green-100 text-green-800
            @elseif($record->status === 'Rejected') bg-red-100 text-red-800
            @elseif($record->status === 'Released') bg-blue-100 text-blue-800
            @elseif($record->status === 'Completed') bg-indigo-100 text-indigo-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ $record->status }}
        </span>
    </div>

    <div class="flex justify-between items-center p-3 bg-gray-50 rounded border">
        <span class="font-semibold">Collateral Status:</span>
        <span class="px-3 py-1 rounded 
            @if($record->collateral_status === 'Pending') bg-yellow-100 text-yellow-800
            @elseif($record->collateral_status === 'Approved') bg-green-100 text-green-800
            @elseif($record->collateral_status === 'Needs Correction') bg-red-100 text-red-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ $record->collateral_status }}
        </span>
    </div>
</div>