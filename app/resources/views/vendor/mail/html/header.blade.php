@props(['url'])
<tr>
<td class="header">

<a href="{{ rtrim(config('app.frontend_url'), '/') }}" style="display: inline-block;">
@if (trim($slot) === 'Cermax')
<img src="https://cermaxdonice.com/upload/gz125/logos/logo-1681480149.webp" class="logo" alt="Cermax Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
