@extends('layouts.app')

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <div class="router-section" style="margin-top: 2rem;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-terminal"></i> Console - {{ $routeur->nom }}</h3>
                <a href="/routeurs" class="btn-add"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
            
            <div style="padding: 2rem;">
                <div style="background: #0f1a24; padding: 1.5rem; border-radius: 1rem; border: 1px solid #2a3f5a;">
                    <h4 style="color: #aaccff; margin-bottom: 1rem;"><i class="fas fa-info-circle"></i> Informations du routeur</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                        <div>
                            <span style="color: #8ba9d0;">Nom:</span><br>
                            <strong style="color: #ffffff;">{{ $routeur->nom }}</strong>
                        </div>
                        <div>
                            <span style="color: #8ba9d0;">Adresse IP:</span><br>
                            <strong style="color: #ffffff;">{{ $routeur->adresse_ip }}</strong>
                        </div>
                        <div>
                            <span style="color: #8ba9d0;">Modèle:</span><br>
                            <strong style="color: #ffffff;">{{ $routeur->modele }}</strong>
                        </div>
                        <div>
                            <span style="color: #8ba9d0;">Statut:</span><br>
                            <strong style="color: {{ $routeur->statut === 'en_ligne' ? '#2ef75b' : '#ffaa33' }};">
                                {{ ucfirst(str_replace('_', ' ', $routeur->statut)) }}
                            </strong>
                        </div>
                    </div>

                    <h4 style="color: #aaccff; margin-bottom: 1rem;"><i class="fas fa-link"></i> Options d'accès</h4>
                    
                    @if($api_available)
                    <div style="margin-bottom: 1rem;">
                        <p style="color: #8ba9d0; margin-bottom: 0.5rem;">
                            <i class="fas fa-globe"></i> Interface Web (WebFig):
                        </p>
                        <a href="{{ $webfig_url }}" target="_blank" class="btn-add" style="display: inline-block;">
                            <i class="fas fa-external-link-alt"></i> Ouvrir WebFig
                        </a>
                        <span style="color: #8ba9d0; margin-left: 1rem; font-size: 0.9rem;">
                            {{ $webfig_url }}
                        </span>
                    </div>
                    @else
                    <div style="background: #1a2c3c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border-left: 4px solid #ffaa33;">
                        <p style="color: #ffaa33;"><i class="fas fa-exclamation-triangle"></i> API non configurée</p>
                        <p style="color: #8ba9d0; font-size: 0.9rem;">Veuillez configurer l'accès API pour accéder à l'interface Web du routeur.</p>
                    </div>
                    @endif

                    <div style="background: #1a2c3c; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #2ef75b;">
                        <p style="color: #aaccff;"><i class="fas fa-terminal"></i> Console SSH/Telnet:</p>
                        <p style="color: #8ba9d0; font-size: 0.9rem; margin: 0.5rem 0 0;">
                            Vous pouvez également vous connecter via SSH ou Telnet à <code style="background: #0f1a24; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">{{ $routeur->adresse_ip }}</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
