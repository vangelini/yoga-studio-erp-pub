@extends('layouts.base')

@section('body')
    <div class="flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    
                    <!-- Menu di navigazione -->
                    @php
                        $role = auth()->user()->role ?? null;
                        $viewConfig = $dashboardViewConfig ?? [];
                        $showClientAdmin = $viewConfig['show_client_admin'] ?? ($role === 'Admin');
                        $showCourseAdmin = $viewConfig['show_course_admin'] ?? in_array($role, ['Admin', 'Teacher']);
                        $showTeacherAdmin = $viewConfig['show_teacher_admin'] ?? ($role === 'Admin');
                        $showSettings = $viewConfig['show_settings'] ?? ($role === 'Admin');
                        $showNotifications = $viewConfig['show_course_unpaid'] ?? ($role === 'Admin');
                        $showMenuAdminTeacher = in_array($role, ['Admin', 'Teacher']);
                    @endphp
                    @if($showMenuAdminTeacher)
                    <div x-data="{ openNav: false }" class="relative bg-green-100 text-green-800 rounded-lg">
                        <button @click="openNav = !openNav" class="inline-flex items-center gap-2 rounded-lg bg-teal-600/90 px-3 py-2 text-sm font-semibold border border-white/30 shadow-sm hover:bg-teal-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 22 22" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            Menu
                        </button>
                        <div x-show="openNav" x-transition @click.away="openNav = false" class="absolute left-0 mt-2 w-56 rounded-xl border border-stone-200 bg-white text-stone-700 shadow-lg z-50">
                            <ul class="divide-y divide-stone-100 text-sm">
                                <li><a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-stone-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l9-9 9 9M4.5 10.5v9.75A1.5 1.5 0 006 21.75h12a1.5 1.5 0 001.5-1.5V10.5"/></svg>
                                    Home
                                </a></li>
                                @if($showClientAdmin)
                                <li><a href="{{ route('admin.clients.index') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-stone-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 9.75h15m-13.5 3H12m-7.5 3H12m6.75-6v6.75a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 016.75 4.5h9a2.25 2.25 0 012.25 2.25V9.75z" /></svg>
                                    Allieve/i
                                </a></li>
                                @endif
                                @if($showCourseAdmin)
                                <li><a href="{{ route('dashboard.courses') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-stone-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m6-6H6" /></svg>
                                    Corsi
                                </a></li>
                                @endif
                                @if($role === 'Admin')
                                <li><a href="{{ route('admin.accounting.index') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-stone-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18M10 6h11M3 6h4m-4 12h4m6 0h9" /></svg>
                                    Contabilità
                                </a></li>
                                @endif
                                @if($showTeacherAdmin)
                                <li><a href="{{ route('admin.teachers.index') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-stone-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l-3 3m3-3l3 3m-3-3V4m9 5v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9" /></svg>
                                    Insegnanti
                                </a></li>
                                @endif
                                @if($showNotifications)
                                <li><a href="{{ route('notifications.index') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-stone-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                    Centro notifiche
                                </a></li>
                                @endif
                                @if($showSettings)
                                <li><a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-stone-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 3.75a1.5 1.5 0 013 0V5a1.5 1.5 0 01-3 0V3.75zM5.636 5.636a1.5 1.5 0 010 2.121l-.884.884a1.5 1.5 0 01-2.122-2.121l.884-.884a1.5 1.5 0 012.122 0zM3.75 10.5H5a1.5 1.5 0 010 3H3.75a1.5 1.5 0 010-3zM5.636 18.364a1.5 1.5 0 01-2.122 0l-.884-.884a1.5 1.5 0 112.122-2.121l.884.884a1.5 1.5 0 000 2.121zM10.5 18.75V20a1.5 1.5 0 003 0v-1.25a1.5 1.5 0 00-3 0zM18.364 18.364a1.5 1.5 0 002.122 0l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 000 2.121zM20.25 13.5H19a1.5 1.5 0 110-3h1.25a1.5 1.5 0 110 3zM18.364 5.636l.884-.884a1.5 1.5 0 10-2.122-2.121l-.884.884a1.5 1.5 0 002.122 2.121z"/></svg>
                                    Impostazioni
                                </a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @endif
                    <!-- Fine Menu di navigazione -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 sm:gap-3 group transition">
                        <img src="{{ asset('images/yoga-logo.jpg') }}" alt="Shanti Sadhana Logo" class="w-9 h-9 sm:w-11 sm:h-11 rounded-full ring-2 ring-transparent group-hover:ring-teal-200 transition">
                    </a>
                    <div>
                        <h1 class="text-lg sm:text-2xl font-bold text-teal-800 group-hover:text-teal-700 transition">Shanti Sadhana</h1>
                        <p class="text-xs sm:text-sm text-stone-500 -mt-1">Centro Yoga - Area riservata</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    
                    <div class="text-right">
                        <p class="text-xs text-stone-400">Benvenuta/o</p>
                        <p class="font-semibold text-teal-700">{{ auth()->user()->name }}</p>
                        
                    </div>
                    <!-- Centro notifiche -->
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
                        <div style="height:4px;"></div>
                        <div class="text-center flex flex-col sm:flex-row sm:items-center gap-2 text-xs text-stone-500">
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-semibold text-emerald-600">
                                    esci
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- Fine centro notifiche -->
                    <!-- Bottone esci -->
                    
                    <!-- Fine Bottone esci -->
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
