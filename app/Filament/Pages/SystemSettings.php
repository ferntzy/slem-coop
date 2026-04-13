<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SystemSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'System Settings';
    protected static ?string $title           = 'System Settings';
    protected static ?string $slug            = 'system-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort     = 99999999;

    protected string $view = 'filament.pages.system-settings';

    // ── Appearance ────────────────────────────────────────────────────────────
    public string $app_name         = '';
    public string $primary_color    = '#0d9488';
    public string $font             = 'Rajdhani';
    public string $topbar_font_size = '14';
    public array  $logo             = [];
    public array  $favicon          = [];



    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:SystemSettings') ?? false;
    }

    public function mount(): void
    {
        // Appearance
        $this->app_name         = SystemSetting::get('app_name', config('app.name')) ?? '';
        $this->primary_color    = SystemSetting::get('primary_color', '#0d9488') ?? '#0d9488';
        $this->font             = SystemSetting::get('font', 'Rajdhani') ?? 'Rajdhani';
        $this->topbar_font_size = SystemSetting::get('topbar_font_size', '14') ?? '14';

        $logo    = SystemSetting::get('logo');
        $favicon = SystemSetting::get('favicon');

        $this->logo    = $logo    ? [$logo]    : [];
        $this->favicon = $favicon ? [$favicon] : [];
    }

    public function getLogoUrl(): ?string
    {
        $path = $this->logo[0] ?? null;
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function getFaviconUrl(): ?string
    {
        $path = $this->favicon[0] ?? null;
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([


                Section::make('Appearance')
                    ->icon('heroicon-o-paint-brush')
                    ->collapsible()
                    ->schema([

                        TextInput::make('app_name')
                            ->label('Application Name')
                            ->required()
                            ->live()
                            ->prefixIcon('heroicon-o-building-office')
                            ->columnSpanFull(),

                        ColorPicker::make('primary_color')
                            ->label('Primary Color')
                            ->live()
                            ->columnSpanFull(),

                        Select::make('font')
                            ->label('System Font')
                            ->live()
                            ->options([
                                'Rajdhani'   => 'Rajdhani',
                                'Oxanium'    => 'Oxanium',
                                'Orbitron'   => 'Orbitron',
                                'Syne'       => 'Syne',
                                'Exo 2'      => 'Exo 2',
                                'Bebas Neue' => 'Bebas Neue',
                                'Outfit'     => 'Outfit',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('topbar_font_size')
                            ->label('Topbar Username Font Size')
                            ->helperText('Controls the size of the username text in the top navigation bar.')
                            ->live()
                            ->type('range')
                            ->minValue(10)
                            ->maxValue(24)
                            ->step(1)
                            ->suffix('px')
                            ->extraInputAttributes([
                                'min'   => '10',
                                'max'   => '24',
                                'step'  => '1',
                                'style' => 'width:100%; accent-color: var(--primary-color, #0d9488);',
                            ])
                            ->columnSpanFull(),

                        FileUpload::make('logo')
                            ->label('System Logo')
                            ->helperText('SVG, PNG or JPG. SVG recommended for crisp scaling.')
                            ->disk('public')
                            ->directory('system')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->acceptedFileTypes([
                                'image/svg+xml',
                                'image/png',
                                'image/jpeg',
                                'image/jpg',
                            ])
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->helperText('Recommended: 32×32 PNG or SVG.')
                            ->disk('public')
                            ->directory('system')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->acceptedFileTypes([
                                'image/png',
                                'image/svg+xml',
                                'image/x-icon',
                                'image/vnd.microsoft.icon',
                            ])
                            ->maxSize(512)
                            ->columnSpanFull(),

                    ]),

            ]);
    }

    public function save(): void
    {
        // Appearance
        $logo    = is_array($this->logo)    ? ($this->logo[0]    ?? null) : ($this->logo    ?: null);
        $favicon = is_array($this->favicon) ? ($this->favicon[0] ?? null) : ($this->favicon ?: null);

        SystemSetting::set('app_name',         $this->app_name);
        SystemSetting::set('primary_color',    $this->primary_color);
        SystemSetting::set('font',             $this->font);
        SystemSetting::set('topbar_font_size', $this->topbar_font_size);

        if ($logo !== null)    SystemSetting::set('logo',    $logo);
        if ($favicon !== null) SystemSetting::set('favicon', $favicon);


        Artisan::call('view:clear');

        Notification::make()
            ->title('Settings saved!')
            ->success()
            ->send();

        $this->redirect(static::getUrl(), navigate: false);
    }
}
