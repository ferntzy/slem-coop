<?php

namespace App\Filament\Pages;

use App\Models\ContactPageSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ManageContactPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Contact ';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:ManageContactPage') ?? false;
    }

    protected string $view = 'filament.pages.manage-contact-page';

    public array $data = [];

    public function mount(): void
    {
        $s = ContactPageSetting::getSetting();

        $this->form->fill([
            'hero_badge' => $s->hero_badge,
            'hero_title' => $s->hero_title,
            'hero_subtitle' => $s->hero_subtitle,
            'phone' => $s->phone,
            'email' => $s->email,
            'address' => $s->address,
            'hours' => $s->hours,
            'facebook_url' => $s->facebook_url,
            'twitter_url' => $s->twitter_url,
            'instagram_url' => $s->instagram_url,
            'linkedin_url' => $s->linkedin_url,
            'maps_embed_url' => $s->maps_embed_url,
            'maps_lat' => $s->maps_lat,
            'maps_lng' => $s->maps_lng,
            'branches' => $s->branches ?? [],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('tabs')
                    ->tabs([

                        // ── Hero ─────────────────────────────────────────────
                        Tab::make('Hero Section')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Hero Text')
                                    ->description('The banner text shown at the top of the Contact page.')
                                    ->schema([
                                        TextInput::make('hero_badge')
                                            ->label('Badge Text')
                                            ->placeholder('e.g. Get in Touch')
                                            ->maxLength(100),

                                        TextInput::make('hero_title')
                                            ->label('Main Title')
                                            ->placeholder('e.g. Contact Us')
                                            ->required()
                                            ->maxLength(150),

                                        TextInput::make('hero_subtitle')
                                            ->label('Subtitle')
                                            ->maxLength(255),
                                    ])
                                    ->columns(1),
                            ]),

                        // ── Contact Info ──────────────────────────────────────
                        Tab::make('Contact Info')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Primary Contact Details')
                                    ->description('Displayed as the 4 info cards on the Contact page.')
                                    ->schema([
                                        TextInput::make('phone')
                                            ->label('Phone Number')
                                            ->placeholder('(123) 456-7890')
                                            ->tel()
                                            ->maxLength(50),

                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->placeholder('info@coop.com')
                                            ->email()
                                            ->maxLength(150),

                                        TextInput::make('address')
                                            ->label('Main Office Address')
                                            ->placeholder('123 Main Street, City')
                                            ->maxLength(255),

                                        TextInput::make('hours')
                                            ->label('Office Hours')
                                            ->placeholder('Mon-Fri 9AM-5PM')
                                            ->maxLength(100),
                                    ])
                                    ->columns(2),
                            ]),

                        // ── Social & Map ──────────────────────────────────────
                        Tab::make('Social & Map')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('Social Media Links')
                                    ->description('Leave blank to hide a social icon on the Contact page.')
                                    ->schema([
                                        TextInput::make('facebook_url')
                                            ->label('Facebook URL')
                                            ->url()
                                            ->nullable()
                                            ->placeholder('https://facebook.com/yourpage'),

                                        TextInput::make('twitter_url')
                                            ->label('Twitter / X URL')
                                            ->url()
                                            ->nullable()
                                            ->placeholder('https://twitter.com/yourpage'),

                                        TextInput::make('instagram_url')
                                            ->label('Instagram URL')
                                            ->url()
                                            ->nullable()
                                            ->placeholder('https://instagram.com/yourpage'),

                                        TextInput::make('linkedin_url')
                                            ->label('LinkedIn URL')
                                            ->url()
                                            ->nullable()
                                            ->placeholder('https://linkedin.com/company/yourpage'),
                                    ])
                                    ->columns(2),

                                Section::make('Branch Location on Map')
                                    ->description('Search for your address or click on the map to pin your location. The coordinates are saved and rendered as an embed on the Contact page.')
                                    ->schema([
                                        TextInput::make('maps_search')
                                            ->label('Search Address')
                                            ->placeholder('e.g. 123 Main Street, Manila')
                                            ->live(debounce: 500)
                                            ->suffixIcon('heroicon-o-magnifying-glass')
                                            ->dehydrated(false), // not saved — just drives the map

                                        ViewField::make('map_picker')
                                            ->label('')
                                            ->view('filament.forms.components.map-picker')
                                            ->columnSpanFull(),

                                        TextInput::make('maps_lat')
                                            ->hidden(),

                                        TextInput::make('maps_lng')
                                            ->hidden(),

                                        TextInput::make('maps_embed_url')
                                            ->hidden(),
                                    ])
                                    ->columns(2),
                            ]),

                        // ── Branches ──────────────────────────────────────────
                        Tab::make('Branches')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make('Branch Locator')
                                    ->description('Each entry appears as a branch card at the bottom of the Contact page.')
                                    ->schema([
                                        Repeater::make('branches')
                                            ->label('')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Branch Name')
                                                    ->required()
                                                    ->maxLength(100),

                                                TextInput::make('phone')
                                                    ->label('Phone')
                                                    ->tel()
                                                    ->maxLength(50),

                                                TextInput::make('address')
                                                    ->label('Address')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),

                                                TextInput::make('hours')
                                                    ->label('Operating Hours')
                                                    ->placeholder('Mon-Fri 9AM-5PM')
                                                    ->maxLength(100),
                                            ])
                                            ->columns(2)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Branch')
                                            ->addActionLabel('Add Branch')
                                            ->defaultItems(0),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Remove any non-DB fields (maps_search is dehydrated:false but safety-strip anyway)
        unset($data['maps_search']);

        // Use direct update on the existing row — never re-insert defaults
        $setting = ContactPageSetting::find(1);

        if ($setting) {
            $setting->update($data);
        } else {
            ContactPageSetting::create(array_merge(['id' => 1], $data));
        }

        Notification::make()
            ->title('Contact page saved')
            ->body('Changes will now reflect on the landing page.')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Manage Contact Page';
    }
}
