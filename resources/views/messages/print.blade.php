<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimer le message - {{ $message->subject }}</title>
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f6fb;
            color: #111827;
        }
        .print-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px;
            background: #ffffff;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 1.55rem;
        }
        .meta {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            margin-bottom: 20px;
        }
        .meta div {
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            font-size: 0.95rem;
        }
        .content {
            line-height: 1.8;
            font-size: 1rem;
            white-space: pre-wrap;
        }
        .attachments {
            margin-top: 24px;
        }
        .attachments h2 {
            margin-bottom: 12px;
            font-size: 1rem;
        }
        .attachments ul {
            padding-left: 20px;
            margin: 0;
        }
        .attachments li {
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .controls button,
        .controls a {
            appearance: none;
            border: none;
            background: #111827;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .controls a {
            background: #3b82f6;
        }
        @media print {
            body {
                background: white;
            }
            .controls {
                display: none;
            }
            .print-wrapper {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-wrapper">
        <div class="controls no-print">
            <button type="button" onclick="window.print()">Imprimer</button>
            <a href="{{ url()->previous() }}">Retour</a>
        </div>

        <div class="header">
            <div>
                <h1>{{ $message->subject }}</h1>
                <p style="margin: 8px 0 0; color: #6b7280;">{{ $message->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="meta">
            <div><strong>De :</strong> {{ $message->sender?->name ?? $message->sender?->email ?? 'Inconnu' }}</div>
            <div><strong>À :</strong> {{ $message->receiver?->name ?? $message->receiver?->email ?? 'Inconnu' }}</div>
            <div><strong>Chiffré :</strong> {{ $message->is_secure ? 'Oui' : 'Non' }}</div>
            <div><strong>Favori :</strong> {{ $message->is_starred ? 'Oui' : 'Non' }}</div>
        </div>

        <div class="content">{!! nl2br(e($message->content)) !!}</div>

        @if($message->has_attachments && $message->attachments->count())
            <div class="attachments">
                <h2>Pièces jointes</h2>
                <ul>
                    @foreach($message->attachments as $attachment)
                        <li>{{ $attachment->filename }} ({{ number_format($attachment->file_size / 1024, 2) }} KB)</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</body>
</html>
