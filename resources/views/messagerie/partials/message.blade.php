@php
$isMe = $message->sender_id === auth()->id();
$decryptedContent = $message->decrypted_body ?? $message->decrypt() ?? '[Impossible de déchiffrer]';
@endphp

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}" id="message-{{ $message->id }}">
    <div class="max-w-[70%] {{ $isMe ? 'bg-indigo-600' : 'bg-slate-700' }} rounded-lg px-4 py-3 shadow-lg">
        {{-- Nom de l'expéditeur pour les messages des autres --}}
        @if(!$isMe)
            <p class="text-xs text-indigo-300 font-medium mb-1">{{ $message->sender->name }}</p>
        @endif

        {{-- Contenu du message --}}
        <p class="text-white text-sm leading-relaxed">{{ $decryptedContent }}</p>

        {{-- Pièces jointes --}}
        @if($message->attachments && $message->attachments->count() > 0)
            <div class="mt-2 space-y-2">
                @foreach($message->attachments as $attachment)
                    <a href="{{ route('messagerie.attachment.download', $attachment) }}" 
                       class="flex items-center gap-2 bg-slate-800/50 rounded p-2 hover:bg-slate-800 transition text-sm">
                        <i class="fas {{ $attachment->icon() }} text-slate-400"></i>
                        <span class="text-slate-200 truncate">{{ $attachment->original_filename }}</span>
                        <span class="text-slate-500 text-xs ml-auto">{{ $attachment->formattedSize() }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Métadonnées --}}
        <div class="flex items-center justify-end gap-2 mt-2">
            <span class="text-xs {{ $isMe ? 'text-indigo-200' : 'text-slate-400' }}">
                {{ $message->created_at->format('H:i') }}
            </span>
            
            @if($isMe)
                @php
                    $readCount = $message->recipients()->whereNotNull('read_at')->count();
                    $totalRecipients = $message->recipients()->count();
                @endphp
                
                @if($readCount === $totalRecipients && $totalRecipients > 0)
                    <span class="text-xs text-indigo-300" title="Lu par tous">
                        <i class="fas fa-check-double"></i>
                    </span>
                @elseif($readCount > 0)
                    <span class="text-xs text-indigo-300" title="Lu par {{ $readCount }}/{{ $totalRecipients }}">
                        <i class="fas fa-check"></i>
                    </span>
                @else
                    <span class="text-xs text-indigo-300" title="Envoyé">
                        <i class="fas fa-check"></i>
                    </span>
                @endif

                {{-- Bouton suppression --}}
                <button onclick="deleteMessage({{ $message->id }})" class="text-indigo-300 hover:text-red-400 ml-1">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            @endif
        </div>
    </div>
</div>
