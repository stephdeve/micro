<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message reçu</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f8fafc;
        }
        .container {
            background-color: #ffffff;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #4338ca 100%);
            color: white;
            padding: 32px 22px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .message-info {
            background-color: #eef2ff;
            border-left: 4px solid #3b82f6;
            padding: 18px 22px;
            margin: 22px 0;
            border-radius: 12px;
        }
        .message-info strong {
            color: #334155;
        }
        .message-content {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 14px;
            padding: 22px;
            margin: 24px 0;
            line-height: 1.7;
            color: #1f2937;
        }
        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 34px;
            border-radius: 999px;
            font-weight: 700;
            text-align: center;
            margin: 24px 0;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
            text-shadow: 0 0 1px rgba(255,255,255,0.85);
        }
        .action-button:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(37, 99, 235, 0.28);
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.8;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .header, .content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h1>Nouveau message reçu</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $notifiable->name ?? 'Utilisateur' }}</strong>,</p>

            <p>Vous avez reçu un nouveau message dans votre espace de messagerie.</p>

            <div class="message-info">
                <p><strong>De :</strong> {{ $senderName }}</p>
                <p><strong>Objet :</strong> {{ $subject }}</p>
            </div>

            <div class="message-content">
                {{ $content }}
            </div>

            <div style="text-align: center;">
                <a href="{{ $url }}" class="action-button">
                    <i class="fas fa-eye"></i> Voir le message complet
                </a>
            </div>

            <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
                Merci d'utiliser votre espace de messagerie sécurisé.
            </p>
        </div>

        <div class="footer">
            <p><strong>Gestion Réseau MikroTik</strong></p>
            <p>Plateforme de gestion et surveillance réseau</p>
            <p style="font-size: 12px; margin-top: 15px;">
                © {{ date('Y') }} Gestion Réseau MikroTik. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>