{{-- resources/views/filament/tables/columns/avatar-with-initials.blade.php --}}

@php
    $url      = $getState()['url']      ?? null;
    $initials = $getState()['initials'] ?? '?';

    // Generate a consistent background color from the initials
    $colors = [
        '#3A8E0D', '#2563C7', '#7C3AED', '#DB2777',
        '#D97706', '#0891B2', '#DC2626', '#059669',
    ];
    $colorIndex = abs(crc32($initials)) % count($colors);
    $bgColor    = $colors[$colorIndex];
@endphp

<div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; flex-shrink: 0;">
    @if ($url)
        <img
            src="{{ $url }}"
            alt="Avatar"
            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        />
        {{-- Fallback shown if image fails to load --}}
        <div style="
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: {{ $bgColor }};
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            align-items: center;
            justify-content: center;
            font-family: sans-serif;
        ">
            {{ $initials }}
        </div>
    @else
        {{-- No image at all — show initials --}}
        <div style="
            display: flex;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: {{ $bgColor }};
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            align-items: center;
            justify-content: center;
            font-family: sans-serif;
        ">
            {{ $initials }}
        </div>
    @endif
</div>