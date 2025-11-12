@extends('layouts.app')

@section('content')
 

    @if (auth()->user()->role === 'Admin')
        @include('dashboard.partials.admin')
    @elseif (auth()->user()->role === 'Teacher')
        @include('dashboard.partials.teacher')
    @else
        @include('dashboard.partials.client')
    @endif
@endsection
