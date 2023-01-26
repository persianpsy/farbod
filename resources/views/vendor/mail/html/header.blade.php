<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://panel.persianpsychology.com/assets/logo.8d776125.png" class="logo" alt="Persian Psychology">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
