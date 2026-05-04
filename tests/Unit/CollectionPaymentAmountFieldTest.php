<?php

function collection_posting_list_page_source(): string
{
    return file_get_contents(dirname(__DIR__, 2).'/app/Filament/Resources/CollectionAndPostings/Pages/ListCollectionAndPostings.php');
}

it('keeps the selected schedule amount editable before payment submission', function (): void {
    $amountFieldDefinition = str(collection_posting_list_page_source())
        ->after("TextInput::make('amount_paid')")
        ->before("DatePicker::make('payment_date')")
        ->toString();

    expect($amountFieldDefinition)
        ->not->toContain('->readOnly()')
        ->toContain("'id' => 'amount_paid_field'");
});

it('auto-selects earlier unpaid schedule rows when a later period is selected', function (): void {
    expect(collection_posting_list_page_source())
        ->toContain('if (p <= clickedPeriod) {')
        ->toContain('cb.checked = true;')
        ->toContain('if (p >= clickedPeriod) {')
        ->not->toContain('Please select the earliest unpaid due first.');
});
