@php
    $appName = config('app.name', 'WeCollab');
    $heroImage = asset('images/homepage/hero.png');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $appName }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        body, table, td {
            font-family: 'Instrument Sans', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #030712;
            background-image: linear-gradient(135deg, rgba(3,7,18,0.95), rgba(15,23,42,0.95)), url('{{ $heroImage }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
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

        .body {
            padding: 40px 42px 10px;
        }

        @media only screen and (max-width: 600px) {
            .card {
                border-radius: 20px;
            }

            .body {
                padding: 28px 24px 6px;
            }
        }

        h1, h2, h3 {
            color: #0f172a;
        }

        p {
            color: #1f2937;
            line-height: 1.6;
            font-size: 15px;
            margin: 0 0 16px;
        }

        a {
            color: #111827;
        }

        .content-cell {
            width: 100%;
        }

        .subcopy {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid rgba(148,163,184,0.35);
            color: #475569;
            font-size: 13px;
        }

        .footer {
            padding: 0 42px 36px;
            color: #94a3b8;
            font-size: 12px;
            text-align: left;
        }

        .footer a {
            color: #111827;
        }

        .button {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .button-primary {
            background: #000000;
            color: #ffffff !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.23);
        }

        .button-secondary {
            background: #f1f5f9;
            color: #0f172a !important;
        }
    </style>
    {!! $head ?? '' !!}
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="card" cellpadding="0" cellspacing="0" role="presentation">
                {!! $header ?? '' !!}
                <tr>
                    <td class="body">
                        <table class="content-cell" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td>
                                    {!! $slot !!}

                                    {!! $subcopy ?? '' !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                {!! $footer ?? '' !!}
            </table>
        </td>
    </tr>
</table>
</body>
</html>
