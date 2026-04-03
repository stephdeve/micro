@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-bell"></i>
        <i class="fas fa-exclamation-triangle"></i>
        <i class="fas fa-info-circle"></i>
    </div>

    @include('layouts.guest')

    <div class="card" style="padding: 1.5rem; max-width: 900px; margin: 2rem auto;">
        <h2>Notifications</h2>
        <p style="color:#8ba9d0; margin-bottom: 1rem;">Dernières notifications (non lues en gras).</p>

        @if($notifications->isEmpty())
            <div style="padding: 1rem; color: #8ba9d0;">Aucune notification pour le moment.</div>
        @else
        <div style="display:flex; flex-direction:column; gap:0.5rem;">
            @foreach($notifications as $note)
            <div style="background: {{ $note->read_at ? '#0f1a29' : '#12233b' }}; border: 1px solid #1f3452; border-radius: 0.7rem; padding: 1rem; display:flex; justify-content:space-between; align-items:center;">
                <div style="flex:1;">
                    <strong style="font-size:1rem; color:#dbe9ff;">{{ $note->data['title'] ?? 'Notification' }}</strong>
                    <div style="font-size:0.9rem; color:#aebed4;">{{ $note->data['message'] ?? '' }}</div>
                    <small style="color:#7b9bb5;">{{ $note->created_at->diffForHumans() }}</small>
                </div>
                <div class="notification-actions">
                    @if(!$note->read_at)
                        <form method="POST" action="{{ route('notifications.markAsRead', ['id' => $note->id]) }}">
                            @csrf
                            <button type="submit" class="btn-icon notification-action" title="Marquer comme lu">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    @endif
                    @if(!empty($note->data['url']) && $note->data['url'] !== '#')
                        <a href="{{ $note->data['url'] }}" class="btn-icon notification-action" title="Aller">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection