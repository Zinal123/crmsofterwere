@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Payroll for {{ $employee->name }}</h1>
    <p>Year: {{ $year }}, Month: {{ $month }}</p>

    @if ($earnings)
        <p>Day Rate: {{ $earnings['day_rate'] ?? 'N/A' }}</p>
    @endif
</div>
@endsection
