<?php

function member_details_table_source(): string
{
    return file_get_contents(dirname(__DIR__, 2).'/app/Filament/Resources/MemberDetails/Tables/MemberDetailsTable.php');
}

it('formats deposit amount input with comma separators while saving a numeric value', function (): void {
    $depositAction = str(member_details_table_source())
        ->after("Action::make('add_deposit')")
        ->before("Action::make('add_share_capital')")
        ->toString();

    $amountField = str($depositAction)
        ->after("TextInput::make('amount')")
        ->before("TextInput::make('notes')")
        ->toString();

    expect($amountField)
        ->toContain("->mask(RawJs::make('\$money(\$input)'))")
        ->toContain("->stripCharacters(',')")
        ->toContain('->numeric()');
});
