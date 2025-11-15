@extends('layouts.app')

@section('content')
<div class="space-y-8">
    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6 space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-stone-900">Notifiche</h2>
            <p class="text-sm text-stone-500">Crea messaggi preimpostati da inviare via email, WhatsApp o portale.</p>
            @if($canBroadcastAll)
                <form method="POST" action="{{ route('notifications.pending.resend') }}" class="mt-3 inline-flex">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-100 transition">
                        Reinvia notifica pendenze
                    </button>
                </form>
            @endif
        </div>
        <form method="POST" action="{{ route('notifications.store') }}" class="grid grid-cols-1 gap-4" x-data="{ triggerType: '{{ old('trigger_type', 'manual') }}' }">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Titolo</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="input-field mt-1" required>
                    @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Trigger</label>
                    <select name="trigger_type" class="input-field mt-1" x-model="triggerType">
                        <option value="manual" @selected(old('trigger_type', 'manual') === 'manual')>Invio manuale</option>
                        <option value="event" @selected(old('trigger_type') === 'event')>Evento</option>
                        <option value="scheduled" @selected(old('trigger_type') === 'scheduled')>Programmata</option>
                    </select>
                </div>
                <div x-data="{
                        selected: '{{ old('event_type') }}',
                        options: @js($eventOptions),
                        get current() {
                            return this.options.find(option => option.value === this.selected) || null;
                        }
                    }">
                    <label class="text-xs uppercase font-semibold text-stone-500">Evento collegato</label>
                    <select name="event_type" class="input-field mt-1" x-model="selected">
                        @foreach ($eventOptions as $option)
                            <option value="{{ $option['value'] }}" @selected(old('event_type') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-stone-500 mt-1">Scegli il trigger solo se la notifica è di tipo evento (WhatsApp non verrà inviato).</p>
                    @error('event_type')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    <div class="mt-2 text-xs text-stone-500" x-show="selected === ''" x-cloak>
                        Segnaposto generici sempre disponibili: <code>{{ '{' }}{{ 'user.name' }}{{ '}' }}</code>, <code>{{ '{' }}{{ 'user.email' }}{{ '}' }}</code>, <code>{{ '{' }}{{ 'course.title' }}{{ '}' }}</code>, <code>{{ '{' }}{{ 'payment.due_date' }}{{ '}' }}</code>.
                    </div>
                    <template x-if="current && current.value">
                        <div class="mt-2 rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-600" x-cloak>
                            <p>
                                <span class="font-semibold text-stone-700">Segnaposto evento:</span>
                                <span x-text="current.placeholders.join(', ')"></span>
                            </p>
                            <p class="mt-1" x-text="current.hint"></p>
                        </div>
                    </template>
                </div>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Canali</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach (['portal' => 'Portale', 'email' => 'Email', 'whatsapp' => 'WhatsApp'] as $key => $label)
                            <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                                <input type="checkbox" name="channels[]" value="{{ $key }}" class="rounded text-teal-600 border-stone-300" @checked(collect(old('channels', ['portal']))->contains($key))>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-stone-500 mt-1">Seleziona almeno un canale di invio.</p>
                    @error('channels')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="text-xs uppercase font-semibold text-stone-500">Messaggio</label>
                <textarea name="message_body" rows="4" class="input-field mt-1" placeholder="Testo della notifica">{{ old('message_body') }}</textarea>
                <p class="text-xs text-stone-500 mt-1">Placeholder disponibili: <code>{{ '{' }}{{ 'user.name' }}{{ '}' }}</code>, <code>{{ '{' }}{{ 'course.title' }}{{ '}' }}</code>, <code>{{ '{' }}{{ 'payment.due_date' }}{{ '}' }}</code></p>
                @error('message_body')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4" x-show="triggerType === 'scheduled'" x-cloak>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Frequenza programmata</label>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <input type="number" name="schedule_interval_value" min="1" max="365" value="{{ old('schedule_interval_value') }}" class="input-field w-24" placeholder="Es. 2">
                        <select name="schedule_interval_unit" class="input-field">
                            <option value="">—</option>
                            <option value="hour" @selected(old('schedule_interval_unit') === 'hour')>Ore</option>
                            <option value="day" @selected(old('schedule_interval_unit') === 'day')>Giorni</option>
                            <option value="week" @selected(old('schedule_interval_unit') === 'week')>Settimane</option>
                            <option value="month" @selected(old('schedule_interval_unit') === 'month')>Mesi</option>
                        </select>
                        <input type="time" name="schedule_time" value="{{ old('schedule_time', '09:00') }}" class="input-field w-36">
                    </div>
                    <p class="text-xs text-stone-500 mt-1">Es.: “2 settimane alle 09:00” invia la notifica ogni 14 giorni all’orario scelto.</p>
                    @error('schedule_interval_value')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    @error('schedule_interval_unit')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    @error('schedule_time')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="text-xs text-stone-500">
                    <p class="font-semibold text-stone-600">Suggerimenti</p>
                    <p>Le notifiche programmate partono dall’orario indicato e continuano finché la notifica resta attiva. WhatsApp non viene inviato per i job programmati.</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <p class="text-xs uppercase font-semibold text-stone-500">Destinatari</p>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_teachers" value="1" class="rounded text-teal-600 border-stone-300" @checked(old('target_all_teachers')) @disabled(!$canBroadcastAll)>
                        <span>Tutti gli insegnanti</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_clients" value="1" class="rounded text-teal-600 border-stone-300" @checked(old('target_all_clients')) @disabled(!$canBroadcastAll)>
                        <span>Tutti gli allievi</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="target_all_admins" value="1" class="rounded text-teal-600 border-stone-300" @checked(old('target_all_admins')) @disabled(!$canBroadcastAll)>
                        <span>Tutti gli amministratori</span>
                    </label>
                </div>
                <div>
                    <label class="text-xs uppercase font-semibold text-stone-500">Corsi</label>
                    <select name="course_ids[]" multiple class="input-field mt-1 h-32">
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected(collect(old('course_ids'))->contains($course->id))>{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-stone-500 mt-1">Seleziona i corsi interessati (max 20).</p>
                    @error('course_ids')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-between flex-wrap gap-2">
                <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" name="send_now" value="1" class="rounded text-teal-600 border-stone-300" @checked(old('send_now', true))>
                    <span>Invia subito dopo il salvataggio</span>
                </label>
                <button type="submit" class="btn-primary">Salva notifica</button>
            </div>
        </form>
    </div>

    <div class="card p-6 space-y-4">
        <h3 class="text-xl font-semibold text-stone-900">Notifiche configurate</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-stone-200">
                <thead class="bg-stone-100 text-xs uppercase text-stone-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Titolo</th>
                        <th class="px-3 py-2">Trigger</th>
                        <th class="px-3 py-2">Canali</th>
                        <th class="px-3 py-2">Destinatari</th>
                        <th class="px-3 py-2">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($notifications as $notification)
                        <tr>
                            <td class="px-3 py-2">
                                <p class="font-semibold text-stone-800">{{ $notification->title }}</p>
                                <p class="text-xs text-stone-500">{{ $notification->description }}</p>
                            </td>
                            <td class="px-3 py-2 text-center text-xs">
                                <span class="inline-flex rounded-full bg-stone-100 px-2 py-0.5 font-semibold text-stone-600">{{ ucfirst($notification->trigger_type) }}</span>
                            </td>
                            <td class="px-3 py-2 text-xs">
                                {{ implode(', ', $notification->channels ?? []) }}
                            </td>
                            <td class="px-3 py-2 text-xs">
                                @if($notification->target_all_teachers)
                                    <span class="inline-block bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full text-[11px] mr-1">Docenti</span>
                                @endif
                                @if($notification->target_all_clients)
                                    <span class="inline-block bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full text-[11px] mr-1">Allievi</span>
                                @endif
                                @if($notification->target_all_admins)
                                    <span class="inline-block bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full text-[11px] mr-1">Amministratori</span>
                                @endif
                                @if($notification->courseTargets->isNotEmpty())
                                    <span class="inline-block bg-stone-100 text-stone-600 px-2 py-0.5 rounded-full text-[11px]">{{ $notification->courseTargets->count() }} corsi</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">
                                @if($notification->trigger_type === 'manual' && ($notification->created_by === $user->id || $user->role === 'Admin'))
                                    <form method="POST" action="{{ route('notifications.send', $notification) }}" onsubmit="return confirm('Inviare questa notifica?');" class="inline-flex mr-2">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-teal-200 px-3 py-1.5 text-[11px] font-semibold text-teal-600 hover:bg-teal-50">Invia ora</button>
                                    </form>
                                @endif
                                @if($notification->created_by === $user->id || $user->role === 'Admin')
                                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Eliminare questa notifica?');" class="inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-semibold text-rose-600 hover:bg-rose-50">Elimina</button>
                                    </form>
                                @endif
                                @if($notification->trigger_type === 'scheduled' && ($notification->created_by === $user->id || $user->role === 'Admin'))
                                    <form method="POST" action="{{ route('notifications.toggle', $notification) }}" class="inline-flex ml-2">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-stone-200 px-3 py-1.5 text-[11px] font-semibold {{ $notification->is_active ? 'text-stone-600 hover:bg-stone-100' : 'text-emerald-600 border-emerald-200 hover:bg-emerald-50' }}">
                                            {{ $notification->is_active ? 'Disattiva' : 'Attiva' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-stone-500 text-sm">Nessuna notifica configurata.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold text-stone-900">Ultimi invii</h3>
            @if($canBroadcastAll)
                <form method="POST" action="{{ route('notifications.jobs.purge') }}" onsubmit="return confirm('Svuotare il log degli invii?');" class="inline-flex">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-stone-200 px-3 py-1.5 text-[11px] font-semibold text-stone-600 hover:bg-stone-100">Svuota log</button>
                </form>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-stone-200">
                <thead class="bg-stone-100 text-xs uppercase text-stone-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Notifica</th>
                        <th class="px-3 py-2">Stato</th>
                        <th class="px-3 py-2">Destinatari</th>
                        <th class="px-3 py-2">Inviate</th>
                        <th class="px-3 py-2">Errore</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($jobs as $job)
                        <tr>
                            <td class="px-3 py-2">
                                <p class="font-semibold text-stone-800">{{ $job->notification->title ?? '—' }}</p>
                                <p class="text-xs text-stone-500">{{ optional($job->completed_at) ? $job->completed_at->format('d/m/Y H:i') : 'In coda' }}</p>
                            </td>
                            <td class="px-3 py-2 text-xs text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 {{ $job->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($job->status === 'failed' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600') }}">{{ ucfirst($job->status) }}</span>
                            </td>
                            <td class="px-3 py-2 text-center text-xs">{{ $job->target_count }}</td>
                            <td class="px-3 py-2 text-center text-xs">{{ $job->sent_count }}</td>
                            <td class="px-3 py-2 text-xs text-rose-500">{{ $job->error_message }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-stone-500 text-sm">Nessun invio registrato.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
