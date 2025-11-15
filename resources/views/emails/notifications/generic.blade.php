@component('mail::message')
{{ $body }}

Grazie,<br>
{{ config('app.name') }}
@endcomponent
