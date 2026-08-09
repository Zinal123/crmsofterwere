@extends('layouts.master')
@section('title')
Cash Book
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Expenses
@endslot
@slot('title')
Cash Book
@endslot
@endcomponent

<x-ui.back-link :route="route('expenses.index')" label="Back to Daily Expenses" />

<x-ui.data-table-card title="Cash Book">
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-end">Receipt (In)</th>
                    <th class="text-end">Payment (Out)</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date->format('d M Y') }}</td>
                    <td>{{ $transaction->category->name ?? '—' }}</td>
                    <td>{{ $transaction->description ?: '—' }}</td>
                    <td class="text-end text-success">{{ $transaction->type === 'receipt' ? \App\Support\IndianNumber::format($transaction->amount) : '—' }}</td>
                    <td class="text-end text-danger">{{ $transaction->type === 'payment' ? \App\Support\IndianNumber::format($transaction->amount) : '—' }}</td>
                    <td class="text-end fw-semibold {{ $transaction->running_balance < 0 ? 'text-danger' : '' }}">{{ \App\Support\IndianNumber::format($transaction->running_balance) }}</td>
                </tr>
                @empty
                <tr><td colspan="6"><x-ui.empty-state icon="ri-book-2-line" message="No transactions recorded yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.data-table-card>
@endsection
