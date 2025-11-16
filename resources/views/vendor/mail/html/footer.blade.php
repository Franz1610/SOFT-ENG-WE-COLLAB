@php
    $brand = config('app.name', 'WeCollab');
@endphp
<tr>
    <td class="footer" style="padding: 0 42px 38px; background: #ffffff;">
        <p style="margin: 0 0 8px; font-size: 12px; color: #94a3b8;">© {{ date('Y') }} {{ $brand }}. All rights reserved.</p>
        <p style="margin: 0; font-size: 12px; color: #94a3b8;">You're receiving this because you have an account with {{ $brand }}.</p>
    </td>
</tr>
