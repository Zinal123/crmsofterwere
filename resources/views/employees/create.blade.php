@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('employees.index')" label="Back to Employees" />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Add Employee</h5>
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                @include('employees._form')
                <x-ui.button variant="success" type="submit" icon="ri-save-line" ariaLabel="Save employee">Save</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
