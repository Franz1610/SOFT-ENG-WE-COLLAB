@props([
    'url',
    'color' => '#000000',
    'align' => 'center',
])
@php
    $background = match ($color) {
        'success' => '#0f766e',
        'error' => '#b91c1c',
        'primary' => '#000000',
        default => $color,
    };
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="{{ $align }}">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td>
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           style="display: inline-block; padding: 14px 36px; font-weight: 600; border-radius: 999px; text-decoration: none; letter-spacing: 0.02em; background: {{ $background }}; color: #ffffff; box-shadow: 0 12px 25px rgba(0,0,0,0.25);">
                            {!! $slot !!}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
