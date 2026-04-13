<?php

namespace App\Filament\Resources\LoanApplications\RelationManagers;

use App\Models\LoanProductRequirement;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $form->schema([
            Select::make('code')
                ->label('Requirement')
                ->options(function () {
                    $typeId = $this->getOwnerRecord()->loan_type_id;
                        
                return \App\Models\LoanTypeRequirement::query()
                ->where('loan_type_id', $typeId)
                ->orderBy('sort_order')
                ->pluck('label', 'code')
                ->toArray();
                })
                ->searchable()
                ->required(),

            FileUpload::make('file_path')
                ->label('File')
                ->disk('public')
                ->directory(fn () => 'loan-applications/' . $this->getOwnerRecord()->loan_application_id)
                ->preserveFilenames()
                ->storeFileNamesIn('original_name')
                ->storeFileSizesIn('file_size')
                ->required(),

            Hidden::make('document_type')->default('requirement')->dehydrated(true),
            Hidden::make('is_generated')->default(false)->dehydrated(true),
            Hidden::make('uploaded_by_user_id')->default(fn () => auth()->id())->dehydrated(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Code')->badge(),
                TextColumn::make('original_name')->label('File'),
                TextColumn::make('created_at')->dateTime()->label('Uploaded'),
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}