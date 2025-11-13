@extends('layouts.app')

@section('content')
 

    @if (in_array(auth()->user()->role, ['Admin', 'Teacher']))
        @include('dashboard.partials.admin')
    @else
        @include('dashboard.partials.client')
    @endif
@endsection
