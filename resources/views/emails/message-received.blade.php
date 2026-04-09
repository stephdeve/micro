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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
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
            background-color: #f1f5f9;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .message-info strong {
            color: #334155;
        }
        .message-content {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            font-style: italic;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .action-button:hover {
            transform: translateY(-1px);
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