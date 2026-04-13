@php
    use App\Models\SystemSetting;
    use Illuminate\Support\Facades\Storage;

    $appName = SystemSetting::get('app_name', config('app.name'));
    $logo    = SystemSetting::get('logo');
    $font    = SystemSetting::get('font', 'Rajdhani');

    $fontMap = [
        'Rajdhani'   => 'https://fonts.googleapis.com/css2?family=Rajdhani:wght@600&display=swap',
        'Oxanium'    => 'https://fonts.googleapis.com/css2?family=Oxanium:wght@600&display=swap',
        'Orbitron'   => 'https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap',
        'Syne'       => 'https://fonts.googleapis.com/css2?family=Syne:wght@700&display=swap',
        'Exo 2'      => 'https://fonts.googleapis.com/css2?family=Exo+2:wght@600&display=swap',
        'Bebas Neue' => 'https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap',
        'Outfit'     => 'https://fonts.googleapis.com/css2?family=Outfit:wght@600&display=swap',
    ];

    $fontUrl = $fontMap[$font] ?? $fontMap['Rajdhani'];
@endphp

<link href="{{ $fontUrl }}" rel="stylesheet">

<style>
    #brand-logo-svg path       { fill: #111827; transition: fill 0.2s; }
    #brand-logo-text           { color: #111827; transition: color 0.2s; }
    .dark #brand-logo-svg path { fill: #ffffff; }
    .dark #brand-logo-text     { color: #ffffff; }
    .dark #brand-logo-img      { filter: brightness(0) invert(1); }
</style>

<div style="display:flex; flex-direction:row; align-items:center; gap:0.5rem; white-space:nowrap;">

    @if($logo)
        <img
            id="brand-logo-img"
            src="{{ Storage::disk('public')->url($logo) }}"
            alt="{{ $appName }}"
            style="height:1.75rem; width:auto; display:block;"
        />
    @else
        <svg
            id="brand-logo-svg"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            style="height:1.75rem; width:auto; display:block;"
        >
            <path d="M23.275 5.265c0-.852-.132-1.703-.36-2.555-.328-1.016-1.081-1.834-2.031-2.194a9.248 9.248 0 0 0-6.092 0 3.168 3.168 0 0 0-2.03 2.194 9.532 9.532 0 0 0 0 5.077c.326 1.015 1.08 1.834 2.03 2.194a8.04 8.04 0 0 0 3.046.491c1.049 0 2.063-.196 3.046-.491a3.172 3.172 0 0 0 2.031-2.194c.229-.819.36-1.67.36-2.522zm-3.308 0c0 .393-.065.852-.196 1.212-.164.524-.623.95-1.18 1.081a4.233 4.233 0 0 1-1.571 0 1.473 1.473 0 0 1-1.18-1.081 4.025 4.025 0 0 1 0-2.489c.163-.524.622-.95 1.18-1.081a4.233 4.233 0 0 1 1.571 0 1.476 1.476 0 0 1 1.18 1.081c.13.458.196.884.196 1.277m-8.745 13.79a9.552 9.552 0 0 0 0-5.077c-.327-1.016-1.081-1.834-2.03-2.195a9.248 9.248 0 0 0-6.092 0 3.173 3.173 0 0 0-2.031 2.195 9.552 9.552 0 0 0 0 5.077c.328 1.015 1.081 1.834 2.031 2.193a9.248 9.248 0 0 0 6.092 0 3.392 3.392 0 0 0 2.03-2.193m-2.948-2.523c0 .393-.066.852-.197 1.212a1.644 1.644 0 0 1-1.179 1.081 4.238 4.238 0 0 1-1.572 0 1.477 1.477 0 0 1-1.179-1.081 4.04 4.04 0 0 1 0-2.489 1.64 1.64 0 0 1 1.179-1.081 4.196 4.196 0 0 1 1.572 0 1.476 1.476 0 0 1 1.179 1.081c.131.426.197.851.197 1.277m0-11.3h3.308c0-.851-.131-1.703-.36-2.521-.327-1.016-1.081-1.834-2.03-2.194a9.248 9.248 0 0 0-6.092 0C2.084.909 1.331 1.728 1.068 2.743a9.552 9.552 0 0 0 0 5.077c.328 1.015 1.081 1.834 2.031 2.194.982.36 1.998.492 3.046.492 1.048 0 2.063-.197 3.046-.492a3.17 3.17 0 0 0 2.03-2.194c.033-.131.065-.295.131-.426L8.241 5.953c-.033.196-.065.36-.131.557-.163.524-.622.95-1.179 1.081a4.238 4.238 0 0 1-1.572 0A1.478 1.478 0 0 1 4.18 6.51a4.04 4.04 0 0 1 0-2.489c.164-.524.622-.95 1.179-1.082a4.238 4.238 0 0 1 1.572 0A1.476 1.476 0 0 1 8.11 4.021c.098.425.164.818.164 1.211m4.421 8.779a9.442 9.442 0 0 0-.36 2.555V24h3.308v-7.468c0-.393.065-.852.196-1.212.163-.524.622-.95 1.18-1.081a4.191 4.191 0 0 1 1.571 0 1.478 1.478 0 0 1 1.18 1.081 4.04 4.04 0 0 1 0 2.489c-.164.523-.623.95-1.146 1.08a4.196 4.196 0 0 1-1.572 0c-.099-.031-.229-.064-.327-.098l1.113 3.079c1.049 0 2.063-.197 3.046-.491a3.175 3.175 0 0 0 2.031-2.194 9.552 9.552 0 0 0 0-5.077c-.328-1.016-1.081-1.834-2.031-2.195a9.248 9.248 0 0 0-6.092 0c-1.016.263-1.769 1.082-2.097 2.098"/>
        </svg>
    @endif

    <span
        id="brand-logo-text"
        style="font-family:'{{ $font }}', sans-serif; font-size:1.05rem; font-weight:600; letter-spacing:0.06em; line-height:1;"
    >
        {{ $appName }}
    </span>

</div>
