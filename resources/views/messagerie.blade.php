@extends('layouts.app')

@section('title', 'Messagerie')

@php
    $header_buttons = '';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <!-- Statistiques messagerie -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-envelope-open-text"></i> Messages totaux</div>
            <div class="stat-value">{{ $stats['total'] ?? 1342 }}</div>
            <div class="stat-change">Depuis la création</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-envelope" style="color: #ffaa33;"></i> Non lus</div>
            <div class="stat-value">{{ $stats['non_lus'] ?? 12 }}</div>
            <div class="stat-change"><i class="fas fa-exclamation-circle"></i> {{ $stats['importants'] ?? 3 }} importants</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-users"></i> Utilisateurs</div>
            <div class="stat-value">{{ $stats['utilisateurs'] ?? 8 }}</div>
            <div class="stat-change">Connectés actuellement</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-shield-alt"></i> Chiffrement</div>
            <div class="stat-value" style="color: #2ef79b;">{{ $stats['chiffrement'] ?? 'TLS 1.3' }}</div>
            <div class="stat-change">{{ $stats['algorithme'] ?? 'AES-256-GCM' }}</div>
        </div>
    </div>

    <!-- Interface de messagerie -->
    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem; margin-top: 2rem;">
        <!-- Sidebar messagerie -->
        <div class="card" style="padding: 1rem;">
            <div style="margin-bottom: 1.5rem;">
                <button class="btn-primary" style="width: 100%;" onclick="openComposeModal()">
                    <i class="fas fa-pen"></i> Nouveau message
                </button>
            </div>
            
            <div class="nav-menu" style="gap: 0.3rem;">
                <a href="{{ route('messagerie.index', ['folder' => 'inbox']) }}" 
                   class="nav-item {{ $folder == 'inbox' ? 'active' : '' }}" 
                   style="justify-content: flex-start;">
                    <i class="fas fa-inbox"></i> Boîte de réception
                    @if($stats['non_lus'] > 0)
                        <span class="badge-message" style="margin-left: auto;">{{ $stats['non_lus'] }}</span>
                    @endif
                </a>
                <a href="{{ route('messagerie.index', ['folder' => 'sent']) }}" 
                   class="nav-item {{ $folder == 'sent' ? 'active' : '' }}" 
                   style="justify-content: flex-start;">
                    <i class="fas fa-paper-plane"></i> Envoyés
                </a>
                <a href="{{ route('messagerie.index', ['folder' => 'starred']) }}" 
                   class="nav-item {{ $folder == 'starred' ? 'active' : '' }}" 
                   style="justify-content: flex-start;">
                    <i class="fas fa-star"></i> Favoris
                </a>
                <a href="{{ route('messagerie.index', ['folder' => 'archive']) }}" 
                   class="nav-item {{ $folder == 'archive' ? 'active' : '' }}" 
                   style="justify-content: flex-start;">
                    <i class="fas fa-archive"></i> Archives
                </a>
                <a href="{{ route('messagerie.index', ['folder' => 'trash']) }}" 
                   class="nav-item {{ $folder == 'trash' ? 'active' : '' }}" 
                   style="justify-content: flex-start;">
                    <i class="fas fa-trash"></i> Corbeille
                </a>
            </div>

            <hr style="border-color: #263f55; margin: 1.5rem 0;">

            <h4 style="margin-bottom: 1rem;">Filtres rapides</h4>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label class="remember">
                    <input type="checkbox" onchange="filterMessages('unread')" {{ request('unread') ? 'checked' : '' }}> Non lus
                </label>
                <label class="remember">
                    <input type="checkbox" onchange="filterMessages('attachments')" {{ request('attachments') ? 'checked' : '' }}> Avec pièce jointe
                </label>
                <label class="remember">
                    <input type="checkbox" onchange="filterMessages('starred')" {{ request('starred') ? 'checked' : '' }}> Marqués
                </label>
                <label class="remember">
                    <input type="checkbox" onchange="filterMessages('urgent')" {{ request('urgent') ? 'checked' : '' }}> Urgents
                </label>
            </div>

            <hr style="border-color: #263f55; margin: 1.5rem 0;">

            <h4 style="margin-bottom: 1rem;">Étiquettes</h4>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="{{ route('messagerie.index', ['tag' => 'reseau']) }}" 
                   style="color: #cddcff; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #00ccff; border-radius: 3px;"></span> 
                    Réseau
                </a>
                <a href="{{ route('messagerie.index', ['tag' => 'securite']) }}" 
                   style="color: #cddcff; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #ffaa33; border-radius: 3px;"></span> 
                    Sécurité
                </a>
                <a href="{{ route('messagerie.index', ['tag' => 'maintenance']) }}" 
                   style="color: #cddcff; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #2ef75b; border-radius: 3px;"></span> 
                    Maintenance
                </a>
                <a href="{{ route('messagerie.index', ['tag' => 'alertes']) }}" 
                   style="color: #cddcff; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #904eff; border-radius: 3px;"></span> 
                    Alertes
                </a>
            </div>
        </div>

        <!-- Liste des messages -->
        <div class="card" style="padding: 0;">
            <!-- Barre de recherche -->
            <div style="padding: 1rem; border-bottom: 1px solid #1d3347;">
                <form method="GET" action="{{ route('messagerie.index') }}" id="search-form">
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="search" class="input-field" placeholder="Rechercher dans les messages..." 
                               value="{{ request('search') }}" style="flex: 1;">
                        <button type="submit" class="btn-icon"><i class="fas fa-search"></i></button>
                        <a href="{{ route('messagerie.index', ['folder' => $folder]) }}" class="btn-icon"><i class="fas fa-times"></i></a>
                    </div>
                    @foreach(request()->except(['search', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>

            <!-- Liste des messages -->
            <div style="max-height: 500px; overflow-y: auto;">
                @forelse($messages as $message)
                <div class="message-item {{ !$message->is_read ? 'unread' : '' }}" 
                     style="margin: 0; border-radius: 0; border-left: 4px solid {{ $message->priority == 'urgente' ? '#ff5e7c' : ($message->priority == 'haute' ? '#ffaa33' : '#00ccff') }}; 
                            {{ !$message->is_read ? 'background: #1a2a3a;' : '' }}
                            cursor: pointer;"
                     onclick="window.location='{{ route('messagerie.show', $message) }}'">
                    <div style="display: flex; gap: 1rem; align-items: center; padding: 0.5rem;">
                        <input type="checkbox" onclick="event.stopPropagation()" value="{{ $message->id }}">
                        <i class="fas fa-star {{ $message->is_starred ? 'text-warning' : '' }}" 
                           style="color: {{ $message->is_starred ? '#ffaa33' : '#4f6682' }}; cursor: pointer;"
                           onclick="event.stopPropagation(); toggleStar({{ $message->id }})"></i>
                        <div style="flex: 1;">
                            <div class="message-header">
                                <span class="message-sender">
                                    <i class="fas fa-{{ $message->sender_id == Auth::id() ? 'user-edit' : 'user-secret' }}"></i> 
                                    {{ $message->sender->name ?? $message->sender->email ?? 'Inconnu' }}
                                </span>
                                <span style="color: #8ba9d0;">{{ $message->created_at->format('H:i') }}</span>
                            </div>
                            <div style="font-weight: {{ !$message->is_read ? '600' : '400' }};">
                                {{ $message->subject }}
                            </div>
                            <div class="message-preview">
                                @if($message->is_secure)
                                    <i class="fas fa-lock" style="color: #2ef7b0; font-size: 0.7rem;"></i>
                                    <span>Message chiffré — ouvrez pour lire</span>
                                @else
                                    {{ Str::limit($message->content, 60) }}
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($message->has_attachments)
                                <i class="fas fa-paperclip" style="color: #8ba9d0;"></i>
                            @endif
                            @if(!$message->is_read)
                                <span style="display: inline-block; width: 10px; height: 10px; background: #00ccff; border-radius: 50%; margin-left: 0.5rem;"></span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 3rem; color: #8ba9d0;">
                    <i class="fas fa-envelope-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>Aucun message dans cette boîte</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($messages->hasPages())
            <div style="padding: 1rem; border-top: 1px solid #1d3347; display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #8ba9d0;">
                    {{ $messages->firstItem() }}-{{ $messages->lastItem() }} sur {{ $messages->total() }} messages
                </span>
                <div style="display: flex; gap: 0.5rem;">
                    @if($messages->onFirstPage())
                        <button class="btn-icon" style="width: 36px; height: 36px;" disabled><i class="fas fa-chevron-left"></i></button>
                    @else
                        <a href="{{ $messages->previousPageUrl() }}" class="btn-icon" style="width: 36px; height: 36px;"><i class="fas fa-chevron-left"></i></a>
                    @endif
                    
                    @foreach($messages->getUrlRange(max(1, $messages->currentPage() - 2), min($messages->lastPage(), $messages->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="btn-icon" style="width: 36px; height: 36px; {{ $page == $messages->currentPage() ? 'background: #0066cc;' : '' }}">
                            {{ $page }}
                        </a>
                    @endforeach
                    
                    @if($messages->hasMorePages())
                        <a href="{{ $messages->nextPageUrl() }}" class="btn-icon" style="width: 36px; height: 36px;"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <button class="btn-icon" style="width: 36px; height: 36px;" disabled><i class="fas fa-chevron-right"></i></button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL COMPOSER UN MESSAGE -->
<div id="composeModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #132231; border-radius: 2rem; padding: 2rem; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto; border: 1px solid #2e4b6b;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 style="color: white;"><i class="fas fa-pen"></i> Nouveau message</h3>
            <button onclick="closeComposeModal()" style="background: none; border: none; color: #8ba9d0; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form id="composeForm" method="POST" action="{{ route('messagerie.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Destinataire *</label>
                <select name="receiver_id" id="receiver_id" class="input-field" required style="width: 100%;">
                    <option value="">Sélectionner un destinataire</option>
                    @foreach($users as $user)
                        @if($user->id != Auth::id())
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Objet *</label>
                <input type="text" name="subject" id="subject" class="input-field" required placeholder="Objet du message">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Priorité</label>
                <select name="priority" id="priority" class="input-field">
                    <option value="basse">Basse</option>
                    <option value="normale" selected>Normale</option>
                    <option value="haute">Haute</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Message *</label>
                <textarea name="content" id="content" class="input-field" rows="8" required placeholder="Votre message..."></textarea>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Pièces jointes</label>
                <div style="border: 2px dashed #2e4b6b; border-radius: 1rem; padding: 1.5rem; text-align: center;">
                    <input type="file" name="attachments[]" id="attachments" multiple style="display: none;" onchange="updateFileList()">
                    <button type="button" onclick="document.getElementById('attachments').click()" class="btn-add">
                        <i class="fas fa-paperclip"></i> Choisir des fichiers
                    </button>
                    <div id="fileList" style="margin-top: 1rem; color: #8ba9d0; font-size: 0.9rem;"></div>
                    <small style="color: #6f8aac;">Taille max: 10MB par fichier</small>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="remember">
                    <input type="checkbox" name="is_secure" value="1" checked> 
                    <span>Chiffrer le message (TLS 1.3)</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeComposeModal()" class="btn-icon" style="width: auto; padding: 0 1.5rem; border-radius: 2rem;">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    animation: modalSlideIn 0.3s ease;
}

.message-item.unread {
    background: #1a2a3a;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Script messagerie chargé');
    
    const modal = document.getElementById('composeModal');
    const form = document.getElementById('composeForm');

    // Ouvrir le modal de composition
    window.openComposeModal = function() {
        console.log('📝 Ouverture du modal de composition');
        if (modal) {
            form.reset();
            document.getElementById('fileList').innerHTML = '';
            modal.style.display = 'flex';
        }
    };

    window.closeComposeModal = function() {
        console.log('🔒 Fermeture du modal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    // Mettre à jour la liste des fichiers
    window.updateFileList = function() {
        const files = document.getElementById('attachments').files;
        const fileList = document.getElementById('fileList');
        fileList.innerHTML = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const size = (file.size / 1024).toFixed(2);
            fileList.innerHTML += `<div style="margin: 0.3rem 0;"><i class="fas fa-file"></i> ${file.name} (${size} KB)</div>`;
        }
    };

    // Soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('📤 Envoi du message');
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(async response => {
            if (!response.ok) {
                const text = await response.text();
                let err;
                try {
                    err = JSON.parse(text);
                } catch (e) {
                    err = { message: text || 'Erreur serveur' };
                }

                throw {
                    status: response.status,
                    ...err
                };
            }

            return response.json();
        })
        .then(data => {
            console.log('✅ Message envoyé:', data);
            closeComposeModal();
            window.location.reload();
        })
        .catch(error => {
            console.error('❌ Erreur:', error);

            if (error.errors) {
                let errorMessage = 'Erreurs de validation:\n';
                for (let field in error.errors) {
                    errorMessage += `- ${field}: ${error.errors[field].join(', ')}\n`;
                }
                alert(errorMessage);
                return;
            }

            if (error.message) {
                alert(`Erreur message: ${error.message}`);
                return;
            }

            alert(`Erreur lors de l\'envoi du message (${error.status || 'inconnu'})`);
        });
    });

    // Filtrer les messages
    window.filterMessages = function(type) {
        const url = new URL(window.location.href);
        if (url.searchParams.has(type)) {
            url.searchParams.delete(type);
        } else {
            url.searchParams.set(type, '1');
        }
        window.location.href = url.toString();
    };

    // Marquer comme favori
    window.toggleStar = function(id) {
        fetch(`/messagerie/${id}/star`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('⭐ Favori mis à jour');
            // Rafraîchir l'étoile dans l'UI
            const star = event.target;
            star.style.color = data.is_starred ? '#ffaa33' : '#4f6682';
        })
        .catch(error => console.error('❌ Erreur:', error));
    };

    // Fermer le modal si on clique en dehors
    window.onclick = function(event) {
        if (event.target == modal) {
            closeComposeModal();
        }
    };
});
</script>
@endsection