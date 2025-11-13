@extends('layouts.app')

@section('content')
<section
    x-data="{
        showCreateClient: false,
        expandedClient: @js($initialExpandedClient),
        toggleClient(id) {
            this.expandedClient = this.expandedClient === id ? null : id;
        },
        setInitialClient() {
            const url = new URL(window.location.href);
            const queryClient = Number(url.searchParams.get('client_id'));
            if (queryClient) {
                this.expandedClient = queryClient;
            } else if (!this.expandedClient && url.hash.startsWith('#client-')) {
                const fromHash = Number(url.hash.replace('#client-', ''));
                if (fromHash) {
                    this.expandedClient = fromHash;
                }
            }
            this.scrollToSelected();
        },
        scrollToSelected() {
            if (!this.expandedClient) {
                return;
            }
            this.$nextTick(() => {
                const target = document.getElementById('client-' + this.expandedClient);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },
    }"
    x-init="setInitialClient()"
    class="space-y-10"
>
   
    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
  
    @include('admin.clients.partials.management')

    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:border-stone-300 hover:text-stone-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Torna al dashboard
                </a>
</section>
@endsection
              