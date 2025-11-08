@extends('layouts.app')

@section('content')
    <div class="mb-10">
        <h2 class="text-3xl font-light text-stone-700">
            Benvenuta/o,
            <span class="font-semibold text-teal-700">{{ Str::before(auth()->user()->name, ' ') }}</span>
        </h2>
        
    </div>

    @if (auth()->user()->role === 'Admin')
        @include('dashboard.partials.admin')
    @elseif (auth()->user()->role === 'Teacher')
        @include('dashboard.partials.teacher')
    @else
        @include('dashboard.partials.client')
    @endif
@endsection
