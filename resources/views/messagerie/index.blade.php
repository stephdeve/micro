@extends('layouts.app')

@section('title', 'Messagerie - ' . ($activeConversation ? $activeConversation->displayName(auth()->id()) : 'Conversations'))

@section('content')
<div class="fixed inset-0 top-0 left-0 right-0 bottom-0 flex bg-slate-900" style="margin-left: 5rem !important;">
    <!-- Sidebar - Liste des conversations -->
    <div class="w-72 bg-slate-800 border-r border-slate-700 flex flex-col flex-shrink-0">
        <!-- Header sidebar -->
        <div class="p-4 border-b border-slate-700 bg-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-comments text-indigo-400"></i>
                    Messagerie
                    @if($totalUnread > 0)
                        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $totalUnread }}</span>
                    @endif
                </h2>
            </div>
            
            <!-- Barre de recherche -->
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Rechercher..." 
                       class="w-full bg-slate-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <i class="fas fa-search absolute left-3 top-2.5 text-slate-400"></i>
            </div>

            <!-- Bouton nouvelle conversation -->
            <div class="mt-3 flex gap-2">
                <button onclick="openNewConversationModal()" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-3 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fas fa-user"></i> Privé
                </button>
                <button onclick="openNewGroupModal()" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white text-sm py-2 px-3 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fas fa-users"></i> Groupe
                </button>
            </div>
        </div>

        <!-- Liste des conversations -->
        <div class="flex-1 overflow-y-auto" id="conversationsList">
            @forelse($conversations as $conversation)
                @php
                    $isActive = $activeConversation && $activeConversation->id === $conversation->id;
                    $unreadCount = $conversation->unread_count ?? 0;
                    $lastMessage = $conversation->lastMessage;
                @endphp
                <a href="{{ route('messagerie.index', ['conversation' => $conversation->id]) }}" 
                   class="block p-4 border-b border-slate-700 hover:bg-slate-700 transition {{ $isActive ? 'bg-slate-700 border-l-4 border-l-indigo-500' : '' }}">
                    <div class="flex items-start gap-3">
                        <!-- Avatar -->
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            @if($conversation->is_group)
                                <i class="fas fa-users"></i>
                            @else
                                @php
                                    $otherMember = $conversation->members->where('id', '!=', auth()->id())->first();
                                    $initials = $otherMember ? strtoupper(substr($otherMember->name, 0, 1)) : '?';
                                @endphp
                                {{ $initials }}
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-white font-semibold truncate">
                                    {{ $conversation->displayName(auth()->id()) }}
                                </h3>
                                @if($lastMessage)
                                    <span class="text-xs text-slate-400">{{ $lastMessage->created_at->diffForHumans() }}</span>
                                @endif
                            </div>
                            
                            <p class="text-sm {{ $unreadCount > 0 ? 'text-white font-medium' : 'text-slate-400' }} truncate">
                                @if($lastMessage)
                                    @if($lastMessage->sender_id === auth()->id())
                                        <span class="text-slate-500">Vous: </span>
                                    @endif
                                    {{ $lastMessage->decrypt() ? Str::limit($lastMessage->decrypt(), 30) : '[Message chiffré]' }}
                                @else
                                    <span class="text-slate-500 italic">Aucun message</span>
                                @endif
                            </p>
                        </div>

                        <!-- Badge non lus -->
                        @if($unreadCount > 0)
                            <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 flex-shrink-0">{{ $unreadCount }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="p-8 text-center">
                    <i class="fas fa-comments text-slate-600 text-4xl mb-3"></i>
                    <p class="text-slate-400">Aucune conversation</p>
                    <p class="text-slate-500 text-sm mt-1">Commencez une nouvelle discussion</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Zone de chat -->
    <div class="flex-1 flex flex-col bg-slate-900">
        @if($activeConversation)
            <!-- Header chat -->
            <div class="p-4 bg-slate-800 border-b border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white">
                        @if($activeConversation->is_group)
                            <i class="fas fa-users"></i>
                        @else
                            @php
                                $otherMember = $activeConversation->members->where('id', '!=', auth()->id())->first();
                                $initials = $otherMember ? strtoupper(substr($otherMember->name, 0, 1)) : '?';
                            @endphp
                            {{ $initials }}
                        @endif
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">{{ $activeConversation->displayName(auth()->id()) }}</h3>
                        <p class="text-xs text-slate-400">
                            @if($activeConversation->is_group)
                                {{ $activeConversation->members->count() }} membres
                            @else
                                @if($otherMember && $otherMember->last_seen_at)
                                    En ligne
                                @else
                                    Hors ligne
                                @endif
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-green-400 flex items-center gap-1">
                        <i class="fas fa-lock"></i> Chiffré AES-256
                    </span>
                    <button class="text-slate-400 hover:text-white p-2">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messagesContainer">
                @foreach($messages as $message)
                    @include('messagerie.partials.message', ['message' => $message])
                @endforeach

                <!-- Pagination si nécessaire -->
                @if($messages->hasMorePages())
                    <div class="text-center py-4">
                        <button onclick="loadMoreMessages()" class="text-slate-400 hover:text-white text-sm">
                            Charger plus de messages
                        </button>
                    </div>
                @endif
            </div>

            <!-- Zone de saisie -->
            <div class="p-4 bg-slate-800 border-t border-slate-700">
                <form id="messageForm" class="flex items-start gap-3" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">

                    <!-- Bouton pièce jointe -->
                    <label class="p-3 text-slate-400 hover:text-white cursor-pointer transition mt-1">
                        <i class="fas fa-paperclip text-lg"></i>
                        <input type="file" name="attachments[]" multiple class="hidden" onchange="handleFileSelect(this)">
                    </label>

                    <!-- Input message -->
                    <div class="flex-1 bg-slate-700 rounded-xl">
                        <textarea name="content" id="messageInput" rows="3" 
                                  class="w-full bg-transparent text-white px-4 py-3 resize-none focus:outline-none min-h-[80px]"
                                  placeholder="Écrivez votre message..."
                                  onkeydown="handleKeyDown(event)"></textarea>
                    </div>

                    <!-- Bouton envoyer -->
                    <button type="submit" class="p-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition mt-1 h-12 w-12 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>

                <!-- Aperçu fichiers sélectionnés -->
                <div id="filePreview" class="hidden mt-3 flex flex-wrap gap-2 pl-12"></div>
            </div>
        @else
            <!-- État vide -->
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center p-8">
                    <div class="w-24 h-24 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-slate-600 text-4xl"></i>
                    </div>
                    <h3 class="text-white text-xl font-semibold mb-2">Bienvenue dans la messagerie</h3>
                    <p class="text-slate-400 max-w-md mx-auto">
                        Sélectionnez une conversation ou créez-en une nouvelle pour commencer à communiquer.
                        Tous les messages sont chiffrés avec AES-256.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal nouvelle conversation -->
<div id="newConversationModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-slate-800 rounded-lg w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-white">Nouvelle conversation</h3>
            <button onclick="closeModal('newConversationModal')" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('messagerie.conversation.create') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">Sélectionner un utilisateur</label>
                <select name="user_id" class="w-full bg-slate-700 text-white rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Choisir --</option>
                    @foreach($users as $userItem)
                        <option value="{{ $userItem->id }}">{{ $userItem->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('newConversationModal')" class="px-4 py-2 text-slate-300 hover:text-white">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Démarrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal nouveau groupe -->
<div id="newGroupModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-slate-800 rounded-lg w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-white">Nouveau groupe</h3>
            <button onclick="closeModal('newGroupModal')" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('messagerie.group.create') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">Nom du groupe</label>
                <input type="text" name="name" required class="w-full bg-slate-700 text-white rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">Membres</label>
                <div class="max-h-40 overflow-y-auto bg-slate-700 rounded-lg p-2">
                    @foreach($users as $userItem)
                        <label class="flex items-center gap-2 p-2 hover:bg-slate-600 rounded cursor-pointer">
                            <input type="checkbox" name="members[]" value="{{ $userItem->id }}" class="rounded">
                            <span class="text-white">{{ $userItem->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('newGroupModal')" class="px-4 py-2 text-slate-300 hover:text-white">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Créer</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Scrollbar personnalisée */
    #conversationsList::-webkit-scrollbar,
    #messagesContainer::-webkit-scrollbar {
        width: 6px;
    }
    #conversationsList::-webkit-scrollbar-track,
    #messagesContainer::-webkit-scrollbar-track {
        background: #1e293b;
    }
    #conversationsList::-webkit-scrollbar-thumb,
    #messagesContainer::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 3px;
    }
    #conversationsList::-webkit-scrollbar-thumb:hover,
    #messagesContainer::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
</style>

<script>
    let lastMessageId = {{ $messages->last() ? $messages->last()->id : 'null' }};
    let pollingInterval;
    let selectedFiles = [];

    // Polling temps réel
    function startPolling() {
        @if($activeConversation)
        pollingInterval = setInterval(() => {
            fetch('{{ route('messagerie.poll') }}?conversation_id={{ $activeConversation->id }}&last_message_id=' + (lastMessageId || ''))
                .then(r => r.json())
                .then(data => {
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            appendMessage(msg);
                            lastMessageId = msg.id;
                        });
                    }
                })
                .catch(e => console.error('Polling error:', e));
        }, 3000); // Poll toutes les 3 secondes
        @endif
    }

    function stopPolling() {
        if (pollingInterval) clearInterval(pollingInterval);
    }

    // Envoi de message
    document.getElementById('messageForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        const formData = new FormData(this);
        const content = formData.get('content').trim();
        
        // Ajouter les fichiers sélectionnés au FormData
        selectedFiles.forEach((file, index) => {
            formData.append('attachments[]', file);
            console.log('Adding file:', file.name, file.size);
        });
        
        console.log('Content:', content, 'Files:', selectedFiles.length);
        
        if (!content && selectedFiles.length === 0) {
            console.log('Empty message, not sending');
            return;
        }

        const btnSubmit = this.querySelector('button[type="submit"]');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('{{ route('messagerie.store') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => {
            console.log('Response status:', r.status);
            if (!r.ok) {
                return r.text().then(text => { throw new Error('HTTP ' + r.status + ': ' + text); });
            }
            return r.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                document.getElementById('messageInput').value = '';
                clearFilePreview();
                
                if (data.html) {
                    appendMessageHtml(data.html);
                }
            } else {
                alert('Erreur: ' + (data.error || 'Échec de l\'envoi'));
            }
        })
        .catch(e => {
            console.error('Send error:', e);
            alert('Erreur lors de l\'envoi: ' + e.message);
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
    });

    function appendMessage(msg) {
        const container = document.getElementById('messagesContainer');
        const isMe = msg.sender_id === {{ auth()->id() }};
        
        const html = `
            <div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
                <div class="max-w-[70%] ${isMe ? 'bg-indigo-600' : 'bg-slate-700'} rounded-lg px-4 py-2">
                    ${!isMe ? `<p class="text-xs text-indigo-300 mb-1">${msg.sender.name}</p>` : ''}
                    <p class="text-white">${msg.decrypted_body || '[Message chiffré]'}</p>
                    <p class="text-xs ${isMe ? 'text-indigo-200' : 'text-slate-400'} text-right mt-1">${msg.time_formatted}</p>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        container.scrollTop = container.scrollHeight;
    }

    function appendMessageHtml(html) {
        const container = document.getElementById('messagesContainer');
        container.insertAdjacentHTML('beforeend', html);
        container.scrollTop = container.scrollHeight;
    }

    // Gestion des fichiers
    function handleFileSelect(input) {
        selectedFiles = Array.from(input.files);
        updateFilePreview();
    }

    function updateFilePreview() {
        const preview = document.getElementById('filePreview');
        if (selectedFiles.length === 0) {
            preview.classList.add('hidden');
            return;
        }
        
        preview.innerHTML = selectedFiles.map((file, i) => `
            <div class="bg-slate-700 rounded px-3 py-1 flex items-center gap-2 text-sm text-white">
                <i class="fas fa-file text-slate-400"></i>
                <span class="truncate max-w-[150px]">${file.name}</span>
                <button type="button" onclick="removeFile(${i})" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
        
        preview.classList.remove('hidden');
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFilePreview();
    }

    function clearFilePreview() {
        selectedFiles = [];
        document.getElementById('filePreview').classList.add('hidden');
    }

    // Raccourcis clavier
    function handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('messageForm').dispatchEvent(new Event('submit'));
        }
    }

    // Modals
    function openNewConversationModal() {
        document.getElementById('newConversationModal').classList.remove('hidden');
        document.getElementById('newConversationModal').classList.add('flex');
    }

    function openNewGroupModal() {
        document.getElementById('newGroupModal').classList.remove('hidden');
        document.getElementById('newGroupModal').classList.add('flex');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    // Delete message
    function deleteMessage(messageId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch(`/messagerie/${messageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.ok) {
                // Remove the message element from DOM
                const msgEl = document.getElementById(`message-${messageId}`);
                if (msgEl) msgEl.remove();
            } else {
                console.error('Failed to delete message');
            }
        })
        .catch(error => {
            console.error('Error deleting message:', error);
        });
    }

    // Auto-resize textarea
    document.getElementById('messageInput')?.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Scroll to bottom on load
    window.addEventListener('load', () => {
        const container = document.getElementById('messagesContainer');
        if (container) container.scrollTop = container.scrollHeight;
        startPolling();
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', stopPolling);
</script>
@endsection
