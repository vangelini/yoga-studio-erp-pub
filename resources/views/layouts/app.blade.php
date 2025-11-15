@extends('layouts.base')

@section('body')
    <div class="flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group transition">
                    <img src="{{ asset('images/yoga-logo.jpg') }}" alt="Shanti Sadhana Logo" class="w-10 h-10 rounded-full ring-2 ring-transparent group-hover:ring-teal-200 transition">
                    <div>
                        <h1 class="text-2xl font-bold text-teal-800 group-hover:text-teal-700 transition">Shanti Sadhana</h1>
                        <p class="text-sm text-stone-500 -mt-1">Find your inner peace.</p>
                    </div>
                </a>
                <div class="flex items-center gap-4">
                    <div
                        x-data="{
                            open: false,
                            loading: true,
                            items: [],
                            unread: 0,
                            csrf: '{{ csrf_token() }}',
                            init() {
                                this.fetchNotifications();
                                setInterval(() => this.fetchNotifications(), 60000);
                            },
                            toggle() {
                                this.open = !this.open;
                                if (this.open) {
                                    this.markVisibleAsRead();
                                }
                            },
                            fetchNotifications() {
                                fetch('{{ route('notifications.feed') }}', { headers: { 'Accept': 'application/json' }})
                                    .then(response => response.ok ? response.json() : Promise.reject())
                                    .then(data => {
                                        this.items = data.notifications || [];
                                        this.unread = data.unread || 0;
                                        this.loading = false;
                                    })
                                    .catch(() => { this.loading = false; });
                            },
                            markVisibleAsRead() {
                                this.items.forEach(item => {
                                    if (!item.is_read) {
                                        fetch(`/notifications/dispatches/${item.id}/read`, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': this.csrf,
                                                'Accept': 'application/json',
                                            },
                                        }).then(() => {
                                            item.is_read = true;
                                            if (this.unread > 0) this.unread -= 1;
                                        });
                                    }
                                });
                            },
                            remove(item) {
                                fetch(`/notifications/dispatches/${item.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': this.csrf,
                                        'Accept': 'application/json',
                                    },
                                }).then(() => {
                                    this.items = this.items.filter(entry => entry.id !== item.id);
                                });
                            }
                        }"
                        class="relative"
                    >
                        <button
                            type="button"
                            class="relative inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 hover:text-teal-600 hover:border-teal-200 transition"
                            @click="toggle()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span x-show="unread > 0" x-text="Math.min(unread, 9)" class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full bg-rose-500 text-white text-[10px] h-4 w-4"></span>
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            class="absolute right-0 mt-3 w-80 max-w-sm rounded-2xl border border-stone-200 bg-white shadow-xl z-50"
                            @click.outside="open = false"
                        >
                            <div class="p-3 border-b border-stone-100 flex items-center justify-between">
                                <p class="text-sm font-semibold text-stone-700">Notifiche</p>
                                <button type="button" class="text-xs text-teal-600 font-semibold" @click="fetchNotifications()">Aggiorna</button>
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-stone-100">
                                <template x-if="loading">
                                    <div class="p-4 text-sm text-stone-500">Caricamento...</div>
                                </template>
                                <template x-if="!loading && items.length === 0">
                                    <div class="p-4 text-sm text-stone-500">Nessuna notifica</div>
                                </template>
                                <template x-for="item in items" :key="item.id">
                                    <div class="p-4 space-y-1">
                                        <p class="text-sm font-semibold text-stone-800" x-text="item.title"></p>
                                        <p class="text-sm text-stone-600" x-text="item.message"></p>
                                        <div class="flex items-center justify-between text-[11px] text-stone-400">
                                            <span x-text="item.created_at_display"></span>
                                            <button type="button" class="text-rose-500 hover:text-rose-600" @click="remove(item)">Cancella</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-stone-500">Benvenuta/o</p>
                        <p class="font-semibold text-teal-700">{{ auth()->user()->name }}</p>
                    </div>
                    <a
                        href="{{ route('account.password.edit') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.104-.896-2-2-2m8 10V9a4 4 0 00-4-4H9a4 4 0 00-4 4v10" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 15h10" />
                        </svg>
                        Cambia password
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-stone-200 text-stone-700 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 container mx-auto px-4 py-8">
           

            @yield('content')
        </main>

        <footer class="bg-white border-t border-stone-200 py-6">
            <div class="container mx-auto px-4 text-center text-stone-500 text-sm">
                &copy; {{ now()->year }} Shanti Sadhana Yoga Center. All Rights Reserved.
            </div>
        </footer>
    </div>
@endsection
