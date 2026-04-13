<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\AboutPageSetting;

class ManageAboutPage extends Page implements HasForms
{
    use InteractsWithForms;

    // Match exact types from Filament v4 base Page class
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-information-circle';
    protected static ?string                 $navigationLabel = 'About';
    protected static string|\UnitEnum|null $navigationGroup = 'Pages';
    protected static ?int                    $navigationSort  = 10;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:ManageAboutPage') ?? false;
    }

    // $view in Filament v4 is non-static — do NOT declare as static
    protected string $view = 'filament.pages.manage-about-page';

    public array $data = [];

    public function mount(): void
    {
        $s = AboutPageSetting::getSetting();

        $this->form->fill([
            'hero_badge'             => $s->hero_badge,
            'hero_title'             => $s->hero_title,
            'hero_subtitle'          => $s->hero_subtitle,
            'vision'                 => $s->vision,
            'mission'                => $s->mission,
            'history'                => $s->history       ?? [],
            'core_values'            => $s->core_values   ?? [],
            'testimonials'           => $s->testimonials  ?? [],
            'board_members'          => $s->board_members ?? [],
            'org_general_assembly'   => $s->org_general_assembly,
            'org_board_of_directors' => $s->org_board_of_directors,
            'org_management_team'    => $s->org_management_team,
            'org_operational_staff'  => $s->org_operational_staff,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('tabs')
                    ->tabs([

                        Tab::make('Hero Section')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Hero Text')
                                    ->description('The banner text shown at the top of the About page.')
                                    ->schema([
                                        TextInput::make('hero_badge')
                                            ->label('Badge Text')
                                            ->placeholder('e.g. Our Story')
                                            ->maxLength(100),

                                        TextInput::make('hero_title')
                                            ->label('Main Title')
                                            ->placeholder('e.g. About Us')
                                            ->required()
                                            ->maxLength(150),

                                        TextInput::make('hero_subtitle')
                                            ->label('Subtitle')
                                            ->maxLength(255),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Vision & Mission')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Textarea::make('vision')
                                            ->label('Our Vision')
                                            ->rows(5)
                                            ->required(),

                                        Textarea::make('mission')
                                            ->label('Our Mission')
                                            ->rows(5)
                                            ->required(),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('History')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Timeline')
                                    ->description('Each entry appears as a timeline milestone on the About page.')
                                    ->schema([
                                        Repeater::make('history')
                                            ->label('')
                                            ->schema([
                                                TextInput::make('year')
                                                    ->label('Year')
                                                    ->required()
                                                    ->maxLength(10)
                                                    ->columnSpan(1),

                                                TextInput::make('title')
                                                    ->label('Milestone Title')
                                                    ->required()
                                                    ->maxLength(100)
                                                    ->columnSpan(2),

                                                Textarea::make('desc')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(3)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                trim(($state['year'] ?? '') . ' — ' . ($state['title'] ?? ''))
                                            )
                                            ->addActionLabel('Add Milestone')
                                            ->defaultItems(0),
                                    ]),
                            ]),

                        Tab::make('Core Values')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Section::make('Values')
                                    ->description('Each value appears as a card on the About page.')
                                    ->schema([
                                        Repeater::make('core_values')
                                            ->label('')
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label('Title')
                                                    ->required()
                                                    ->maxLength(50),

                                                TextInput::make('icon')
                                                    ->label('Lucide Icon Name')
                                                    ->placeholder('e.g. Target, Users, Award, Star')
                                                    ->helperText('Must match a Lucide React icon name exactly.')
                                                    ->maxLength(50),

                                                Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? 'Value')
                                            ->addActionLabel('Add Value')
                                            ->defaultItems(0),
                                    ]),
                            ]),

                        Tab::make('Testimonials')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->schema([
                                Section::make('Member Testimonials')
                                    ->description('Quotes displayed in the "What Our Members Say" section.')
                                    ->schema([
                                        Repeater::make('testimonials')
                                            ->label('')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Member Name')
                                                    ->required()
                                                    ->maxLength(100),

                                                Textarea::make('feedback')
                                                    ->label('Testimonial Text')
                                                    ->rows(3)
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(1)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['name'] ?? 'Testimonial')
                                            ->addActionLabel('Add Testimonial')
                                            ->defaultItems(0),
                                    ]),
                            ]),

                        Tab::make('Board of Directors')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Section::make('Board Members')
                                    ->description('Each entry renders as a photo card on the About page.')
                                    ->schema([
                                        Repeater::make('board_members')
                                            ->label('')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Full Name')
                                                    ->required()
                                                    ->maxLength(100),

                                                TextInput::make('position')
                                                    ->label('Position / Title')
                                                    ->required()
                                                    ->maxLength(100),

                                                FileUpload::make('photo')
                                                    ->label('Photo')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->disk('public')
                                                    ->directory('about/board')
                                                    ->maxSize(2048)
                                                    ->helperText('Max 2MB. Recommended: square crop.')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                trim(($state['name'] ?? 'Member') . ' — ' . ($state['position'] ?? ''))
                                            )
                                            ->addActionLabel('Add Board Member')
                                            ->defaultItems(0),
                                    ]),
                            ]),

                        Tab::make('Org Structure')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make('Organizational Structure')
                                    ->description('Edit the description text for each level of the org chart.')
                                    ->schema([
                                        Textarea::make('org_general_assembly')
                                            ->label('General Assembly')
                                            ->rows(3),

                                        Textarea::make('org_board_of_directors')
                                            ->label('Board of Directors')
                                            ->rows(3),

                                        Textarea::make('org_management_team')
                                            ->label('Management Team')
                                            ->rows(3),

                                        Textarea::make('org_operational_staff')
                                            ->label('Operational Staff')
                                            ->rows(3),
                                    ])
                                    ->columns(1),
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

        AboutPageSetting::updateOrCreate(['id' => 1], $data);

        Notification::make()
            ->title('About page saved')
            ->body('Changes will now reflect on the landing page.')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Manage About Page';
    }
}
