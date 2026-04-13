<?php

namespace App\Filament\Pages;

use App\Models\HeroNewsEvent;
use App\Models\NewsEvent;
use App\Models\News;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class NewsEvents extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'News Events';
    protected static string|\UnitEnum|null $navigationGroup = 'Pages';
    protected static ?int $navigationSort = 12;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:NewsEvents') ?? false;
    }

    protected string $view = 'filament.pages.news-events';
    protected static ?string $title = 'News Events';

    public ?string $activeTab = 'hero'; // Add this to track active tab

    public ?string $event_search = null;
    public ?int $editingEventId = null;
    public ?string $event_title = null;
    public ?string $event_date = null;
    public ?string $event_location = null;
    public ?string $event_description = null;
    public ?string $event_category = null;
    public $event_image = null;
    public ?string $hero_badge = null;
    public ?string $hero_header = null;
    public ?string $hero_paragraph = null;
    public ?string $news_search = null;
    public ?int $editingNewsId = null;
    public ?string $news_title = null;
    public ?string $news_excerpt = null;
    public ?string $news_date = null;

    public function mount(): void
    {
        $hero = HeroNewsEvent::first();
        if ($hero) {
            $this->form->fill([
                'hero_badge' => $hero->hero_badge,
                'hero_header' => $hero->hero_header,
                'hero_paragraph' => $hero->hero_paragraph,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('NewsEventsTabs')
                    ->persistTabInQueryString() // Optional: persists tab in URL
                    ->tabs([
                        Tab::make('Hero Section')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Section::make('Hero Details')
                                    ->schema([
                                        TextInput::make('hero_badge')
                                            ->label('Hero Badge')
                                            ->placeholder('Stay Updated')
                                            ->maxLength(50),

                                        TextInput::make('hero_header')
                                            ->label('Hero Header')
                                            ->placeholder('News & Events')
                                            ->maxLength(100),

                                        TextInput::make('hero_paragraph')
                                            ->label('Hero Description')
                                            ->placeholder('Stay informed...')
                                            ->maxLength(255),
                                    ]),
                                Actions::make([
                                    Action::make('saveHero')
                                        ->label('Save Hero Section')
                                        ->action('saveHero')
                                        ->color('primary')
                                        ->button()
                                ])
                            ]),

                        Tab::make('Events')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Section::make('🔍 Select Event to Edit')
                                    ->schema([
                                        Select::make('event_search')
                                            ->label('Choose Event')
                                            ->options(
                                                NewsEvent::orderBy('title')->pluck('title', 'id')->toArray()
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $event = NewsEvent::find($state);
                                                    if ($event) {
                                                        $this->editingEventId = $state;
                                                        $set('event_title', $event->title);
                                                        $set('event_date', $event->date);
                                                        $set('event_location', $event->location);
                                                        $set('event_category', $event->category);
                                                        $set('event_description', $event->description);
                                                        $set('event_image', $event->image);
                                                    }
                                                } else {
                                                    $this->editingEventId = null;
                                                    $set('event_title', '');
                                                    $set('event_date', now()->format('Y-m-d'));
                                                    $set('event_location', '');
                                                    $set('event_category', '');
                                                    $set('event_description', '');
                                                    $set('event_image', null);
                                                }
                                            })
                                            ->placeholder('Search by title...'),
                                    ])
                                    ->collapsible(),

                                Section::make('Event Details')
                                    ->schema([
                                        TextInput::make('event_title')
                                            ->label('Title')
                                            ->required()
                                            ->maxLength(255),

                                        DatePicker::make('event_date')
                                            ->label('Date')
                                            ->required(),

                                        TextInput::make('event_location')
                                            ->label('Location')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('event_category')
                                            ->label('Category')
                                            ->required()
                                            ->maxLength(100),

                                        TextInput::make('event_description')
                                            ->label('Description')
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Section::make('Image')
                                    ->schema([
                                        FileUpload::make('event_image')
                                            ->disk('public')
                                            ->image()
                                            ->directory('news')
                                            ->imagePreviewHeight('150')
                                            ->reorderable()
                                            ->multiple(false)
                                            ->preserveFilenames()
                                    ]),
                                Actions::make([
                                    Action::make('saveEvent')
                                        ->label('Save Event')
                                        ->action('saveEvent')
                                        ->color('primary')
                                        ->button(),
                                    Action::make('newEvent')
                                        ->label('New Event')
                                        ->action('newEvent')
                                        ->color('secondary')
                                        ->button()
                                ])
                                ->extraAttributes(['class' => 'mt-4 gap-2'])
                            ]),

                        Tab::make('News')
                            ->icon('heroicon-o-newspaper')
                            ->schema([
                                Section::make('🔍 Select News to Edit')
                                    ->schema([
                                        Select::make('news_search')
                                            ->label('Choose News')
                                            ->options(
                                                News::orderBy('title')->pluck('title', 'id')->toArray()
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $news = News::find($state);
                                                    if ($news) {
                                                        $this->editingNewsId = $state;
                                                        $set('news_title', $news->title);
                                                        $set('news_excerpt', $news->excerpt);
                                                        $set('news_date', $news->date);
                                                    }
                                                } else {
                                                    $this->editingNewsId = null;
                                                    $set('news_title', '');
                                                    $set('news_excerpt', '');
                                                    $set('news_date', now()->format('Y-m-d'));
                                                }
                                            })
                                            ->placeholder('Search by title...'),
                                    ])
                                    ->collapsible(),
                                TextInput::make('news_title')
                                    ->label('News Title')
                                    ->placeholder('New Branch Opening Soon')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('news_excerpt')
                                    ->label('News Excerpt')
                                    ->placeholder('We are excited to announce the opening of our East Branch in April 2026...')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('news_date')
                                    ->label('News Date')
                                    ->required(),
                                Actions::make([
                                    Action::make('saveNews')
                                        ->label('Save News')
                                        ->action('saveNews')
                                        ->color('primary')
                                        ->button(),
                                    Action::make('newNews')
                                        ->label('New News')
                                        ->action('newNews')
                                        ->color('secondary')
                                        ->button()
                                ])
                                ->extraAttributes(['class' => 'mt-4 gap-2'])
                            ])
                    ]),
            ]);
    }

    public function saveHero(): void
    {
        $data = $this->form->getState();

        // Only validate hero fields
        $this->validate([
            'hero_badge' => 'nullable|max:50',
            'hero_header' => 'nullable|max:100',
            'hero_paragraph' => 'nullable|max:255',
        ]);

        HeroNewsEvent::updateOrCreate(
            ['id' => 1],
            [
                'hero_badge' => $data['hero_badge'] ?? '',
                'hero_header' => $data['hero_header'] ?? '',
                'hero_paragraph' => $data['hero_paragraph'] ?? '',
            ]
        );

        Notification::make()
            ->success()
            ->title('Hero section updated successfully!')
            ->send();
    }

    public function saveEvent(): void
    {
        $data = $this->form->getState();

        // Only validate event fields
        $this->validate([
            'event_title' => 'required|max:255',
            'event_date' => 'required|date',
            'event_location' => 'required|max:255',
            'event_category' => 'required|max:100',
            'event_description' => 'required',
            'event_image' => 'nullable',
        ]);

        $imagePath = null;
        if (!empty($data['event_image'])) {
            if (is_array($data['event_image'])) {
                $imagePath = $data['event_image'][0] ?? null;
            } else {
                $imagePath = $data['event_image'];
            }
        }

        $eventData = [
            'title' => $data['event_title'],
            'date' => $data['event_date'],
            'location' => $data['event_location'],
            'description' => $data['event_description'],
            'category' => $data['event_category'],
            'image' => $imagePath,
        ];

        if ($this->editingEventId) {
            $event = NewsEvent::find($this->editingEventId);
            if ($event) {
                $event->update($eventData);
                $message = 'Event updated successfully!';
            } else {
                $message = 'Event not found!';
                Notification::make()
                    ->danger()
                    ->title($message)
                    ->send();
                return;
            }
        } else {
            $event = NewsEvent::create($eventData);
            $this->editingEventId = $event->id;
            $message = 'Event created successfully!';
        }

        Notification::make()
            ->success()
            ->title($message)
            ->send();

        // Refresh the form with the new event ID
        $this->form->fill([
            'event_search' => $this->editingEventId,
            'event_title' => $eventData['title'],
            'event_date' => $eventData['date'],
            'event_location' => $eventData['location'],
            'event_category' => $eventData['category'],
            'event_description' => $eventData['description'],
            'event_image' => $eventData['image'],
        ]);
    }

    public function saveNews(): void
    {
        $data = $this->form->getState();

        // Only validate news fields
        $this->validate([
            'news_title' => 'required|max:50',
            'news_excerpt' => 'required|max:255',
            'news_date' => 'required|date',
        ]);

        $newsData = [
            'title' => $data['news_title'],
            'excerpt' => $data['news_excerpt'],
            'date' => $data['news_date'],
        ];

        if ($this->editingNewsId) {
            $news = News::find($this->editingNewsId);
            if ($news) {
                $news->update($newsData);
                $message = 'News updated successfully!';
            } else {
                $message = 'News not found!';
                Notification::make()
                    ->danger()
                    ->title($message)
                    ->send();
                return;
            }
        } else {
            $news = News::create($newsData);
            $this->editingNewsId = $news->id;
            $message = 'News created successfully!';
        }

        Notification::make()
            ->success()
            ->title($message)
            ->send();

        // Refresh the form with the new news ID
        $this->form->fill([
            'news_search' => $this->editingNewsId,
            'news_title' => $newsData['title'],
            'news_excerpt' => $newsData['excerpt'],
            'news_date' => $newsData['date'],
        ]);
    }

    public function resetEventForm(): void
    {
        $this->form->fill([
            'event_search' => null,
            'event_title' => '',
            'event_date' => now()->format('Y-m-d'),
            'event_location' => '',
            'event_category' => '',
            'event_description' => '',
            'event_image' => null,
        ]);
        $this->editingEventId = null;
    }

    public function resetNewsForm(): void
    {
        $this->form->fill([
            'news_search' => null,
            'news_title' => '',
            'news_excerpt' => '',
            'news_date' => now()->format('Y-m-d'),
        ]);
        $this->editingNewsId = null;
    }

    public function newEvent(): void
    {
        $this->resetEventForm();
    }

    public function newNews(): void
    {
        $this->resetNewsForm();
    }
}
