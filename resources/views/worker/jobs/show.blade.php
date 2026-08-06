@extends('layouts.worker')

@section('title', $job->title)

@section('content')
@include('jobs._detail')
@endsection
