@extends('layouts.app')

@section('title', 'Ma Messagerie')

@section('content')
<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-envelope"></i> Ma Messagerie</h1>
        <p>Mes conversations</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-inbox"></i> Messages</h3>
            <a href="{{ route('messagerie.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nouveau
            </a>
        </div>
        <div class="card-body">
            @forelse($messages ?? [] as $msg)
                <div class="message-item {{ $msg->is_read ? '' : 'unread' }}">
                    <div class="message-avatar">
                        {{ substr($msg->sender_id === $user->id ? $msg->receiver->name : $msg->sender->name, 0, 2) }}
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <strong>{{ $msg->sender_id === $user->id ? 'À: ' . $msg->receiver->name : 'De: ' . $msg->sender->name }}</strong>
                            <small>{{ $msg->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="message-subject">{{ $msg->sujet ?? 'Sans sujet' }}</p>
                        <p class="message-preview">{{ Str::limit($msg->contenu, 100) }}</p>
                    </div>
                    <div class="message-actions">
                        <a href="{{ route('messagerie.show', $msg) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center">Aucun message.</p>
            @endforelse

            @if(($messages ?? false) && method_exists($messages, 'links'))
                <div class="pagination-wrapper">
                    {{ $messages->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
    .card-header { padding: 1rem 1.2rem; border-bottom: 1px solid #eee; background: #f8f9fa; display: flex; justify-content: space-between; align-items: center; }
    .card-header h3 { margin: 0; font-size: 1rem; }
    .card-body { padding: 1.2rem; }
    .message-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid #eee; }
    .message-item.unread { background: #e8f4fd; border-left: 3px solid #3498db; }
    .message-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; }
    .message-content { flex: 1; }
    .message-header { display: flex; justify-content: space-between; margin-bottom: 0.3rem; }
    .message-header small { color: #6c757d; }
    .message-subject { margin: 0; font-weight: 500; color: #2c3e50; }
    .message-preview { margin: 0.3rem 0 0 0; color: #6c757d; font-size: 0.9rem; }
    .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; }
    .btn-primary { background: #3498db; color: #fff; }
    .btn-sm { padding: 0.3rem 0.6rem; font-size: 0.85rem; }
    .text-muted { color: #6c757d; }
    .text-center { text-align: center; }
    .pagination-wrapper { margin-top: 1rem; }
</style>
@endsection
