@php
    $greetingText = $greeting
        ?? match ($level) {
            'error' => __('Whoops!'),
            'success' => __('Great news!'),
            default => __('Hello!'),
        };
    
    $buttonBackground = match ($level) {
        'success' => '#0f766e',
        'error' => '#b91c1c',
        default => '#000000',
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('app.name') }}</title>
    <style>
        body, table, td {
            font-family: 'Instrument Sans', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #030712;
            background-image: linear-gradient(135deg, rgba(3,7,18,0.95), rgba(15,23,42,0.95));
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .wrapper {
            width: 100%;
            padding: 32px 12px;
        }

        .card {
            width: 100%;
            max-width: 620px;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .header {
            background: #000000;
            padding: 20px 42px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 700;
        }

        .body {
            padding: 40px 42px;
        }

        .greeting {
            margin: 0 0 18px;
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .content-text {
            margin: 0 0 18px;
            color: #1f2937;
            font-size: 15px;
            line-height: 1.7;
        }

        .button-container {
            margin: 32px 0;
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 14px 36px;
            font-weight: 600;
            border-radius: 999px;
            text-decoration: none;
            letter-spacing: 0.02em;
            background: {{ $buttonBackground }};
            color: #ffffff !important;
            box-shadow: 0 12px 25px rgba(0,0,0,0.25);
        }

        .subcopy {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid rgba(148,163,184,0.35);
            color: #475569;
            font-size: 13px;
            line-height: 1.5;
        }

        .footer {
            padding: 0 42px 36px;
            color: #94a3b8;
            font-size: 12px;
            text-align: left;
        }

        @media only screen and (max-width: 600px) {
            .card {
                border-radius: 20px;
            }
            .header, .body, .footer {
                padding-left: 24px;
                padding-right: 24px;
            }
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="card" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <h1>{{ config('app.name') }}</h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td class="body">
                            <h2 class="greeting">{{ $greetingText }}</h2>
                            
                            @foreach ($introLines as $line)
                                <p class="content-text">{{ $line }}</p>
                            @endforeach

                            @isset($actionText)
                                <div class="button-container">
                                    <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
                                </div>
                            @endisset

                            @foreach ($outroLines as $line)
                                <p class="content-text">{{ $line }}</p>
                            @endforeach

                            <p style="margin: 28px 0 0; color: #0f172a; font-weight: 600;">
                                @if (! empty($salutation))
                                    {{ $salutation }}
                                @else
                                    {{ __('Regards,') }}<br>
                                    {{ config('app.name') }}
                                @endif
                            </p>

                            @isset($actionText)
                                <div class="subcopy">
                                    <p style="margin: 0 0 6px;">
                                        {{ __("If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your browser:", ['actionText' => $actionText]) }}
                                    </p>
                                    <p style="word-break: break-all;">
                                        <a href="{{ $actionUrl }}" style="color: #111827; text-decoration: underline;">
                                            {{ $displayableActionUrl }}
                                        </a>
                                    </p>
                                </div>
                            @endisset
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
