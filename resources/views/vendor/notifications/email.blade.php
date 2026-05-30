<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'PiktoFlow' }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #2563eb; padding: 32px 40px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 28px; font-weight: 900; letter-spacing: -0.5px; }
        .header p { margin: 8px 0 0; color: rgba(255,255,255,0.8); font-size: 14px; }
        .body { padding: 40px; }
        .greeting { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 16px; }
        .line { font-size: 16px; color: #374151; line-height: 1.6; margin-bottom: 12px; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff !important; text-decoration: none; font-weight: 700; font-size: 16px; padding: 14px 32px; border-radius: 12px; }
        .expiry { background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 16px; font-size: 14px; color: #92400e; margin-bottom: 20px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 40px; text-align: center; }
        .footer p { margin: 0; font-size: 13px; color: #9ca3af; line-height: 1.6; }
        .footer a { color: #2563eb; text-decoration: none; }
        .url-fallback { background: #f3f4f6; border-radius: 8px; padding: 12px; font-size: 12px; color: #6b7280; word-break: break-all; margin-top: 20px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🧩 PiktoFlow</h1>
        <p>System komunikacji wspomagającej dla dzieci</p>
    </div>
    <div class="body">
        @foreach ($introLines as $line)
            @if ($loop->first)
                <div class="greeting">{{ $line }}</div>
            @else
                <p class="line">{{ $line }}</p>
            @endif
        @endforeach

        @isset($actionText)
            @php
                $color = match($level ?? 'primary') {
                    'success' => '#16a34a',
                    'error'   => '#dc2626',
                    default   => '#2563eb',
                };
            @endphp
            @if (!empty($expireTimeString))
                <div class="expiry">
                    ⏰ Ten link wygaśnie za {{ $expireTimeString }}.
                </div>
            @endif
            <div class="btn-wrap">
                <a href="{{ $actionUrl }}" class="btn" style="background: {{ $color }}">
                    {{ $actionText }}
                </a>
            </div>
        @endisset

        @foreach ($outroLines as $line)
            <p class="line">{{ $line }}</p>
        @endforeach

        <p class="line">Pozdrawiamy,<br><strong>Zespół PiktoFlow</strong></p>

        @isset($actionText)
            <div class="url-fallback">
                Jeśli przycisk nie działa, skopiuj i wklej poniższy adres do przeglądarki:<br>
                <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
            </div>
        @endisset
    </div>
    <div class="footer">
        <p>&copy; {{ date('Y') }} PiktoFlow. Wszelkie prawa zastrzeżone.</p>
    </div>
</div>
</body>
</html>
