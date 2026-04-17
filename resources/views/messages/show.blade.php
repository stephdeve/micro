
@extends('layouts.app')

@section('title', __('Message'))

@section('content')
<div class="main-content" style="min-height: 80vh;">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <div class="card" style="padding: 1rem; max-width: 880px; margin: 0 auto; background: rgba(17, 26, 40, 0.96); border: 1px solid rgba(255,255,255,0.07); border-radius: 1.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.16);">
        <div class="message-header-container" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; row-gap: 0.9rem;">
            <h2 class="message-title" style="margin: 0; font-size: 1.4rem; line-height: 1.2; letter-spacing: 0.01em; max-width: calc(100% - 160px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $message->subject }}</h2>
            <a href="{{ route('messagerie.index', ['folder' => request('folder', 'inbox')]) }}" class="btn-secondary" style="width: auto; padding: 0.5rem 0.95rem; font-size: 0.92rem; border-radius: 999px; min-width: 120px;"><i class="fas fa-arrow-left" style="margin-right: 0.35rem;"></i>{{ __('Back') }}</a>
        </div>

        <div class="message-meta" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.9rem; color: #c7d3e8;">
            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(255,255,255,0.05); border-radius: 999px; font-size: 0.87rem; border: 1px solid rgba(255,255,255,0.06);">
                <strong>{{ __('From') }} :</strong> {{ $message->sender?->name ?? $message->sender?->email ?? __('Unknown') }}
            </span>
            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(255,255,255,0.05); border-radius: 999px; font-size: 0.87rem; border: 1px solid rgba(255,255,255,0.06);">
                <strong>{{ __('To') }} :</strong> {{ $message->receiver?->name ?? $message->receiver?->email ?? __('Unknown') }}
            </span>
            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(255,255,255,0.05); border-radius: 999px; font-size: 0.87rem; border: 1px solid rgba(255,255,255,0.06);">
                <i class="fas fa-lock"></i> {{ $message->is_secure ? __('Encrypted') : __('Unencrypted') }}
            </span>
            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(255,255,255,0.05); border-radius: 999px; font-size: 0.87rem; border: 1px solid rgba(255,255,255,0.06);">
                <i class="fas fa-star" style="color: {{ $message->is_starred ? '#ffaa33' : '#8ba9d0' }}"></i> {{ __('Favorite') }}
            </span>
        </div>

        <div class="message-body" style="margin-top: 1rem; padding: 1rem; background: #0d1725; border-radius: 1.25rem; line-height: 1.72; color: #edf4ff; font-size: 0.95rem; border: 1px solid rgba(255,255,255,0.06);">
            {!! nl2br(e($message->content)) !!}
        </div>

        @if($message->has_attachments && $message->attachments->count())
            <div class="attachments-section" style="margin-top: 1rem;">
                <h4 style="margin-bottom: 0.75rem; font-size: 0.98rem; display: flex; align-items: center; gap: 0.5rem; color: #c7d3e8;"><i class="fas fa-paperclip"></i> {{ __('Attachments') }}</h4>
                <div class="attachments-list" style="display: grid; gap: 0.75rem;">
                    @foreach($message->attachments as $attachment)
                        <a href="{{ route('messagerie.attachments.download', ['messagerie' => $message->id, 'attachment' => $attachment->id]) }}" class="attachment-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.72rem 0.9rem; background: rgba(255,255,255,0.04); border-radius: 1rem; border: 1px solid rgba(255,255,255,0.05); text-decoration: none; color: #cddcff;">
                            <i class="fas fa-file" style="font-size: 1.05rem;"></i>
                            <div class="attachment-info" style="flex: 1; min-width: 0;">
                                <div class="attachment-name" style="font-size: 0.94rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $attachment->filename }}</div>
                                <div class="attachment-size" style="font-size: 0.82rem; color: #8ba9d0;">{{ number_format($attachment->file_size / 1024, 2) }} KB</div>
                            </div>
                            <i class="fas fa-download" style="color: #8ba9d0;"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="message-actions" style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem;">
            @php
                $archiveUrl = $message && $message->id ? route('messagerie.archive', ['messagerie' => $message->id]) : '#';
                $destroyUrl = $message && $message->id ? route('messagerie.destroy', ['messagerie' => $message->id]) : '#';
            @endphp

            <a href="{{ route('messagerie.export', ['messagerie' => $message->id]) }}?format=pdf" class="btn-secondary" style="padding: 0.62rem 0.95rem; font-size: 0.92rem;"><i class="fas fa-download" style="margin-right: 0.35rem;"></i>{{ __('Export PDF') }}</a>

            <form action="{{ $archiveUrl }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-primary" style="padding: 0.62rem 0.95rem; font-size: 0.92rem;" @if($archiveUrl === '#') disabled @endif><i class="fas fa-archive" style="margin-right: 0.35rem;"></i> {{ __('Archive') }}</button>
            </form>
            <form action="{{ $destroyUrl }}" method="POST" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" style="padding: 0.62rem 0.95rem; font-size: 0.92rem;" @if($destroyUrl === '#') disabled @endif><i class="fas fa-trash" style="margin-right: 0.35rem;"></i> {{ __('Trash') }}</button>
            </form>
        </div>
    </div>
</div>


@endsection