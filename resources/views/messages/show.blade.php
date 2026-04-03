@extends('layouts.app')

@section('title', 'Message')

@section('content')
<div class="main-content" style="min-height: 80vh;">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <div class="card" style="padding: 1.5rem;">
        <div class="message-header-container">
            <h2 class="message-title">{{ $message->subject }}</h2>
            <a href="{{ route('messagerie.index', ['folder' => request('folder', 'inbox')]) }}" class="btn-secondary" style="width: auto; padding: 0.65rem 1.2rem;"><i class="fas fa-arrow-left" style="margin-right: 0.4rem;"></i>Retour</a>
        </div>

        <div class="message-meta">
            <span><strong>De :</strong> {{ $message->sender?->name ?? $message->sender?->email ?? 'Inconnu' }}</span>
            <span><strong>À :</strong> {{ $message->receiver?->name ?? $message->receiver?->email ?? 'Inconnu' }}</span>
            <span><i class="fas fa-lock"></i> {{ $message->is_secure ? 'Chiffré' : 'Non chiffré' }}</span>
            <span><i class="fas fa-star" style="color: {{ $message->is_starred ? '#ffaa33' : '#8ba9d0' }}"></i> Favori</span>
        </div>

        <div class="message-body">
            {!! nl2br(e($message->content)) !!}
        </div>

        @if($message->has_attachments && $message->attachments->count())
            <div class="attachments-section">
                <h4><i class="fas fa-paperclip"></i> Pièces jointes</h4>
                <div class="attachments-list">
                    @foreach($message->attachments as $attachment)
                        <a href="{{ route('messagerie.attachments.download', ['messagerie' => $message->id, 'attachment' => $attachment->id]) }}" class="attachment-item">
                            <i class="fas fa-file"></i>
                            <div class="attachment-info">
                                <div class="attachment-name">{{ $attachment->filename }}</div>
                                <div class="attachment-size">{{ number_format($attachment->file_size / 1024, 2) }} KB</div>
                            </div>
                            <i class="fas fa-download"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="message-actions">
            @php
                $archiveUrl = $message && $message->id ? route('messagerie.archive', ['messagerie' => $message->id]) : '#';
                $destroyUrl = $message && $message->id ? route('messagerie.destroy', ['messagerie' => $message->id]) : '#';
            @endphp

            <form action="{{ $archiveUrl }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary" @if($archiveUrl === '#') disabled @endif><i class="fas fa-archive"></i> Archiver</button>
            </form>
            <form action="{{ $destroyUrl }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" @if($destroyUrl === '#') disabled @endif><i class="fas fa-trash"></i> Corbeille</button>
            </form>
        </div>
    </div>
</div>
@endsection