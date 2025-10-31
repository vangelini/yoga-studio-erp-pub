@extends('layouts.app')

@section('content')
    <div class="mb-10">
        <h2 class="text-3xl font-light text-stone-700">
            Welcome,
            <span class="font-semibold text-teal-700">{{ Str::before(auth()->user()->name, ' ') }}</span>
        </h2>
        <p class="text-stone-500 mt-2">You are logged in as a <span class="font-medium">{{ auth()->user()->role }}</span>.</p>
    </div>

    @if (auth()->user()->role === 'Admin')
        @include('dashboard.partials.admin')
    @elseif (auth()->user()->role === 'Teacher')
        @include('dashboard.partials.teacher')
    @else
        @include('dashboard.partials.client')
    @endif
@endsection
