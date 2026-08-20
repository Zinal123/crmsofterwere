@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('root')" label="Back to Dashboard" />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">All Notifications</h5>
            <div class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    @include('layouts.partials.notification-item', ['notification' => $notification])
                @empty
                    <x-ui.empty-state icon="ri-notification-off-line" message="No notifications yet." />
                @endforelse
            </div>
            @if($notifications->hasPages())
                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
