@extends('layouts.app')

@section('title', 'Mon Espace')

@section('content')
<div class="main-content">
    <div class="no-profile-container">
        <div class="no-profile-card">
            <div class="icon-container">
                <i class="fas fa-user-slash"></i>
            </div>
            <h1>Profil non configuré</h1>
            <p>Votre compte utilisateur n'est pas encore lié à un profil employé dans le système.</p>
            <div class="info-box">
                <h3><i class="fas fa-info-circle"></i> Que faire ?</h3>
                <ul>
                    <li>Contactez votre administrateur réseau</li>
                    <li>Fournissez votre adresse MAC si vous en avez une</li>
                    <li>Demandez la création de votre profil employé</li>
                </ul>
            </div>
            <div class="user-info">
                <p><strong>Votre email:</strong> {{ $user->email }}</p>
                <p><strong>Votre nom:</strong> {{ $user->name }}</p>
            </div>
            <div class="actions">
                <a href="mailto:admin@bht.com" class="btn-primary">
                    <i class="fas fa-envelope"></i> Contacter l'admin
                </a>
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    <i class="fas fa-home"></i> Retour au dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .no-profile-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
    }

    .no-profile-card {
        background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
        border-radius: 1.5rem;
        border: 1px solid #2e4b6b;
        padding: 3rem;
        text-align: center;
        max-width: 500px;
        width: 100%;
    }

    .icon-container {
        width: 100px;
        height: 100px;
        background: rgba(255,170,51,0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
    }
    .icon-container i {
        font-size: 3rem;
        color: #ffaa33;
    }

    .no-profile-card h1 {
        color: #fff;
        margin-bottom: 1rem;
    }
    .no-profile-card > p {
        color: #6b7f96;
        margin-bottom: 2rem;
    }

    .info-box {
        background: rgba(0,0,0,0.2);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: left;
    }
    .info-box h3 {
        color: #00a6ff;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .info-box ul {
        color: #8ba9d0;
        margin: 0;
        padding-left: 1.5rem;
    }
    .info-box li {
        margin-bottom: 0.5rem;
    }

    .user-info {
        background: rgba(0,166,255,0.1);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 2rem;
    }
    .user-info p {
        margin: 0.5rem 0;
        color: #8ba9d0;
    }
    .user-info strong {
        color: #fff;
    }

    .actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .btn-primary, .btn-secondary {
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 1rem;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }
    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid #2e4b6b;
    }
</style>
@endsection
