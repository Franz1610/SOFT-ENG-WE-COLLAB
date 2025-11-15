@php
    $greetingText = $greeting
        ?? match ($level) {
            'error' => __('Whoops!'),
            'success' => __('Great news!'),
            default => __('Hello!'),
        };
@endphp

<x-mail::message>
    <h1 style="margin: 0 0 18px; font-size: 28px; font-weight: 700; color: #0f172a; letter-spacing: -0.02em;">
        {{ $greetingText }}
    </h1>

    @foreach ($introLines as $line)
        <p style="margin: 0 0 18px; color: #1f2937; font-size: 15px; line-height: 1.7;">
            {{ $line }}
        </p>
    @endforeach

    @isset($actionText)
        @php
            $background = match ($level) {
                'success' => '#0f766e',
                'error' => '#b91c1c',
                default => '#000000',
            };
        @endphp

        <div style="margin: 32px 0; text-align: center;">
            <x-mail::button :url="$actionUrl" :color="$background">
                {{ $actionText }}
            </x-mail::button>
        </div>
    @endisset

    @foreach ($outroLines as $line)
        <p style="margin: 0 0 16px; color: #1f2937; font-size: 15px; line-height: 1.7;">
            {{ $line }}
        </p>
    @endforeach

    <p style="margin: 28px 0 0; color: #0f172a; font-weight: 600;">
        @if (! empty($salutation))
            {{ $salutation }}
        @else
            @lang('Regards,')<br>
            {{ config('app.name') }}
        @endif
    </p>

    @isset($actionText)
        <x-slot:subcopy>
            <p style="margin: 0 0 6px; color: #475569; font-size: 13px; line-height: 1.5;">
                @lang(
                    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your browser:",
                    ['actionText' => $actionText]
                )
            </p>
            <p style="word-break: break-all; font-size: 13px; color: #0f172a;">
                <a href="{{ $actionUrl }}" style="color: #111827; text-decoration: underline;">
                    {{ $displayableActionUrl }}
                </a>
            </p>
        </x-slot:subcopy>
    @endisset
</x-mail::message>
