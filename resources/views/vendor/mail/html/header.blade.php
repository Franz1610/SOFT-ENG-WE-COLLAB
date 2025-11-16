@props(['url'])
@php
    $logo = asset('images/homepage/logo.png');
    $brand = config('app.name', 'WeCollab');
@endphp
<tr>
    <td style="background: linear-gradient(120deg, rgba(0,0,0,0.92), rgba(2,6,23,0.92)); padding: 40px 42px 28px;">
        <a href="{{ $url }}" style="display: inline-flex; align-items: center; gap: 16px; text-decoration: none;">
            <img src="{{ $logo }}" alt="{{ $brand }} logo" width="64" height="64" style="display: block; border-radius: 16px; background: rgba(255,255,255,0.05); padding: 8px;">
            <div style="display: flex; flex-direction: column; line-height: 1.2;">
                <span style="font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: 0.02em;">{{ $brand }}</span>
                <span style="font-size: 14px; color: rgba(248,250,252,0.8);">Space booking made simple</span>
            </div>
        </a>
    </td>
</tr>
