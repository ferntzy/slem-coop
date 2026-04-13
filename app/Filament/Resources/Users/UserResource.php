<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function getRecordRouteKeyName(): ?string
    {
        return 'user_id';
    }

    /**
     * Encrypt the user_id when building the URL.
     * e.g. /users/eyJpdiI6I.../edit
     */
    public static function getRecordRouteKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return Crypt::encryptString($record->user_id);
    }

    /**
     * Decrypt the URL segment back to user_id and resolve the record.
     */
    public static function resolveRecordRouteBinding(
        int|string $key,
        ?\Closure $modifyQuery = null
    ): ?\Illuminate\Database\Eloquent\Model {
        try {
            $decrypted = Crypt::decryptString($key);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        return static::getModel()::where('user_id', $decrypted)->first() ?? abort(404);
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            //'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
