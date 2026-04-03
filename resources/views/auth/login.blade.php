<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Sécurisée · NetAdmin MikroTik v2.1</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            color: white;
            background-color: #030712;
        }

        .page-background {
            position: fixed;
            inset: 0;
            z-index: -2;
            background-image: url('https://w.wallhaven.cc/full/6o/wallhaven-6oxv86.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .page-overlay {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: linear-gradient(135deg, rgba(3, 7, 18, 0.95) 0%, rgba(3, 7, 18, 0.6) 50%, rgba(3, 7, 18, 0.95) 100%);
        }

        .glow-effect {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0, 188, 212, 0.3), transparent 70%);
            border-radius: 50%;
            z-index: -1;
            top: -150px;
            right: -150px;
            filter: blur(80px);
        }

        .network-bg-icons {
            position: absolute;
            inset: 0;
            z-index: 0;
            opacity: 0.15;
            pointer-events: none;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem 3rem;
            align-items: center;
            justify-content: space-around;
            padding: 1.5rem;
        }

        .bg-icon {
            font-size: clamp(2.5rem, 8vw, 5.5rem);
            color: #4fc3ff;
            filter: drop-shadow(0 0 10px rgba(0, 255, 255, 0.3));
            animation: float 8s ease-in-out infinite;
        }

        .bg-icon:nth-child(2) { animation-delay: -1.5s; font-size: 4rem; color: #bb86fc; }
        .bg-icon:nth-child(3) { animation-delay: -3s; font-size: 6rem; color: #03dac6; }
        .bg-icon:nth-child(4) { animation-delay: -4.5s; font-size: 3.8rem; color: #ffb74d; }
        .bg-icon:nth-child(5) { animation-delay: -1s; font-size: 5rem; color: #4fc3f7; }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg) scale(1); }
            50% { transform: translateY(-20px) rotate(2deg) scale(1.01); }
            100% { transform: translateY(0) rotate(0deg) scale(1); }
        }

        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            margin: 1rem;
            background: rgba(10, 20, 40, 0.65);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border-radius: 2rem;
            border: 1px solid rgba(100, 180, 255, 0.25);
            box-shadow: 0 30px 50px -12px rgba(0,0,0,0.9), 0 0 0 1px rgba(0, 255, 255, 0.1) inset;
            padding: 2rem 1.8rem;
            transition: all 0.25s ease;
        }

        .login-card:hover {
            transform: translateY(-4px) scale(1.008);
            border-color: rgba(0, 230, 200, 0.45);
        }

        h1 {
            font-size: 1.95rem;
            font-weight: 700;
            letter-spacing: -0.8px;
            background: linear-gradient(130deg, #ffffff, #81d4fa);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        .subtitle {
            font-size: 0.9rem;
            color: #a0bcdd;
            margin-bottom: 1.8rem;
            text-align: center;
            padding-bottom: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .input-group {
            margin-bottom: 1.3rem;
            position: relative;
        }

        .input-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.84rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #aac9ff;
            margin-bottom: 0.5rem;
        }

        .input-field {
            width: 100%;
            background: rgba(0, 20, 40, 0.65);
            border: 1.6px solid rgba(0, 180, 255, 0.35);
            border-radius: 1.6rem;
            padding: 0.85rem 1.5rem;
            font-size: 1.03rem;
            color: white;
            outline: none;
            transition: all 0.25s ease;
        }

        .input-field:focus {
            border-color: #00e5ff;
            background: rgba(0, 40, 60, 0.8);
            box-shadow: 0 0 0 4px rgba(0, 230, 255, 0.18);
        }

        .input-field::placeholder {
            color: rgba(170, 210, 255, 0.35);
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.2rem 0 1.6rem;
            font-size: 0.9rem;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #cddfff;
        }

        .remember input {
            width: 1.1rem;
            height: 1.1rem;
            accent-color: #00e5ff;
        }

        .forgot-link {
            color: #8bc9ff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-link:hover {
            color: white;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(145deg, #0077e6, #00bfff);
            border: none;
            border-radius: 2.5rem;
            padding: 0.95rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: white;
            cursor: pointer;
            box-shadow: 0 10px 20px -6px #0055aa, 0 0 18px rgba(0, 191, 255, 0.45);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 28px -8px #0066cc, 0 0 30px rgba(51, 204, 255, 0.7);
        }

        /* Tooltips erreurs */
        .tooltip-wrapper {
            position: relative;
            margin-top: 0.4rem;
            min-height: 1.5rem;
        }

        .error-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(239, 68, 68, 0.93);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            color: white;
            font-size: 0.82rem;
            padding: 0.6rem 1.2rem;
            border-radius: 1rem;
            border: 1px solid rgba(248, 113, 113, 0.45);
            box-shadow: 0 8px 16px -4px rgba(0,0,0,0.6);
            white-space: normal;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 30;
            min-width: 220px;
            max-width: 300px;
            text-align: center;
            line-height: 1.35;
            font-weight: 500;
        }

        .error-tooltip.show {
            opacity: 1;
            visibility: visible;
            bottom: calc(100% + 8px);
        }

        .error-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 8px solid transparent;
            border-top-color: rgba(239, 68, 68, 0.93);
        }

        .global-error-tooltip {
            top: 100%;
            bottom: auto;
            margin-top: 1rem;
            background: rgba(220, 38, 38, 0.94);
            border-color: rgba(248, 113, 113, 0.55);
        }

        .global-error-tooltip::after {
            top: auto;
            bottom: 100%;
            border-top-color: transparent;
            border-bottom-color: rgba(220, 38, 38, 0.94);
        }

        @media (max-width: 480px) {
            .login-card { padding: 1.6rem 1.3rem; max-width: 94%; }
            h1 { font-size: 1.7rem; }
            .btn-login { font-size: 1.05rem; padding: 0.85rem; }
        }
    </style>
</head>
<body>

    <div class="page-background"></div>
    <div class="page-overlay"></div>
    <div class="glow-effect"></div>

    <div class="network-bg-icons">
        <i class="fas fa-wifi bg-icon"></i>
        <i class="fas fa-broadcast-tower bg-icon"></i>
        <i class="fas fa-network-wired bg-icon"></i>
        <i class="fas fa-satellite bg-icon"></i>
        <i class="fas fa-cloud-upload-alt bg-icon"></i>
        <i class="fas fa-sim-card bg-icon"></i>
    </div>

    <div class="login-card">
        <h1>
            <i class="fas fa-server" style="color: #4fc3ff;"></i>
            NetAdmin
        </h1>
        <div class="subtitle">
            <i class="fas fa-shield-alt"></i> Interface MikroTik v2.1 • Haut Débit
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <label for="username"><i class="far fa-user-circle"></i> Identifiant</label>
                <input type="text"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="input-field @error('email') border-red-400 @enderror"
                       placeholder="admin, superuser..."
                       autofocus
                       required>
                <div class="tooltip-wrapper">
                    @error('email')
                        <div class="error-tooltip show">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="input-group">
                <label for="password"><i class="fas fa-key"></i> Clé d'accès</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="input-field @error('password') border-red-400 @enderror"
                       placeholder="••••••••••••"
                       required>
                <div class="tooltip-wrapper">
                    @error('password')
                        <div class="error-tooltip show">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Message d'erreur global (authentification échouée) -->
            <div class="tooltip-wrapper text-center">
                @if ($errors->has('email') || $errors->has('username') || $errors->has('failed') || $errors->any())
                    <div class="error-tooltip global-error-tooltip show">
                        {{ $errors->first('email') ?: $errors->first('username') ?: $errors->first('failed') ?: $errors->first() ?: __('auth.failed') }}
                    </div>
                @endif
            </div>

           

            <button type="submit" class="btn-login">
                <i class="fas fa-door-open"></i> Se connecter 
            </button>
        </form>

       

    </div>

</body>
</html>