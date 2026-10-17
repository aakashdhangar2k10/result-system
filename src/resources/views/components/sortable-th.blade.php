@props(['column', 'label'])

@php
    $isActive = request('sort') === $column;
    $currentDir = request('dir') === 'desc' ? 'desc' : 'asc';
    $nextDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'dir' => $nextDir, 'page' => 1]);
    $icon = $isActive ? ($currentDir === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up';
@endphp

<th {{ $attributes }}>
    <a href="{{ $url }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
        {{ $label }}
        <i class="bi {{ $icon }} {{ $isActive ? '' : 'text-muted' }}"></i>
    </a>
</th>