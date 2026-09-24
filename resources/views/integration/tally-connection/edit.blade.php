@extends('layouts.master')
@section('title') Tally Connection @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Integration @endslot
@slot('title') Tally Connection @endslot
@endcomponent

<div class="row">
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tally Server Connection</h5>
                @if ($connection)
                    @php
                        $badge = $connection->status === 'connected' ? 'success' : ($connection->status === 'failed' ? 'danger' : 'secondary');
                    @endphp
                    <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ ucfirst($connection->status) }}</span>
                @endif
            </div>
            <div class="card-body">
                @if ($connection?->last_error)
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line align-middle me-1"></i>{{ $connection->last_error }}
                </div>
                @endif
                @if ($connection?->status === 'connected')
                <div class="alert alert-success">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i>Connected &mdash; last checked {{ $connection->last_checked_at->diffForHumans() }}.
                </div>
                @endif

                <form action="{{ route('tally-connection.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="host" class="form-label">Server Host / IP <span class="text-danger">*</span></label>
                            <input type="text" id="host" name="host" class="form-control @error('host') is-invalid @enderror" value="{{ old('host', $connection?->host) }}" required>
                            @error('host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="port" class="form-label">Port <span class="text-danger">*</span></label>
                            <input type="number" id="port" name="port" class="form-control @error('port') is-invalid @enderror" value="{{ old('port', $connection?->port ?? 9000) }}" required>
                            @error('port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">Tally Company Name <span class="text-danger">*</span></label>
                            <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $connection?->company_name) }}" required>
                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Tally Username <span class="text-danger">*</span></label>
                            <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $connection?->username) }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Tally Password {!! $connection ? '' : '<span class="text-danger">*</span>' !!}</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ $connection ? 'Leave blank to keep current password' : '' }}" {{ $connection ? '' : 'required' }}>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="ri-save-line align-middle me-1"></i> Save</button>
                        </div>
                    </div>
                </form>

                @if ($connection)
                <form action="{{ route('tally-connection.connect') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary"><i class="ri-plug-line align-middle me-1"></i> Connect</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
