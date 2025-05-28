<x-mail::message>

<div style="text-align:center; margin-bottom:20px;">
    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="width:120px;" />
</div>

{{-- Greeting --}}
@if (! empty($greeting))
# <span style="color:#2d3748; font-size:24px;">{{ $greeting }}</span>
@else
# <span style="color:#2d3748; font-size:24px;">
    @if ($level === 'error')
        {{ __('Whoops!') }}
    @else
        {{ __('Hello!') }}
    @endif
</span>
@endif

{{-- Divider --}}
<hr style="border:none; border-top:1px solid #e2e8f0; margin:20px 0;" />

{{-- Intro Lines --}}
@foreach ($introLines as $line)
<p style="font-size:16px; color:#4a5568; line-height:1.5; margin: 10px 0;">{{ $line }}</p>
@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
    $btnColor = $color === 'primary' ? '#3182ce' : ($color === 'success' ? '#38a169' : '#e53e3e');
?>
<div style="text-align:center; margin:30px 0;">
    <a href="{{ $actionUrl }}" style="background-color: {{ $btnColor }}; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 4px; display: inline-block; font-weight:600;">
        {{ $actionText }}
    </a>
</div>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
<p style="font-size:16px; color:#4a5568; line-height:1.5; margin: 10px 0;">{{ $line }}</p>
@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
<p style="font-size:16px; color:#4a5568; line-height:1.5;">{{ $salutation }}</p>
@else
<p style="font-size:16px; color:#4a5568; line-height:1.5;">
    {{ __('Regards,') }}<br>
    <strong>{{ config('app.name') }}</strong>
</p>
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
<p style="font-size:14px; color:#a0aec0; line-height:1.4;">
    @lang(
        "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your web browser:",
        ['actionText' => $actionText]
    )
</p>
<p style="font-size:14px; color:#3182ce; word-break:break-all;">{{ $displayableActionUrl }}</p>
</x-slot:subcopy>
@endisset

</x-mail::message>
