@props(['code'])
<span {{ $attributes->merge(['class' => 'badge text-bg-'.Fmt::statusColor($code)]) }}>{{ Fmt::statusLabel($code) }}</span>
