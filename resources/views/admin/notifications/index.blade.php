@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bell"></i> Pranešimai</h2>
        @if($notifications->where('read_at', null)->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-check-all"></i> Pažymėti visus kaip perskaitytus
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($notifications->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>Pranešimų nėra
        </div>
    @else
        <div class="list-group">
            @foreach($notifications as $notification)
                <div class="list-group-item {{ is_null($notification->read_at) ? 'list-group-item-primary' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h6 class="mb-0">
                                    <i class="bi bi-calendar3"></i> 
                                    {{ class_basename($notification->type) }}
                                </h6>
                                @if(is_null($notification->read_at))
                                    <span class="badge bg-primary">Naujas</span>
                                @endif
                            </div>
                            <p class="mb-2">{{ $notification->data['message'] ?? 'Naujas pranešimas' }}</p>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            @if(isset($notification->data['url']))
                                <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Peržiūrėti
                                </a>
                            @endif
                            @if(is_null($notification->read_at))
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-check"></i> Pažymėti
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
