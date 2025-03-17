@props(['name', 'class' => 'w-6 h-6', 'color' => 'currentColor'])
@php
@endphp
@switch($name)
    @case('new-dataset')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path d="M14 2v6h6"/>
            <path d="M12 18v-6"/>
            <path d="M9 15h6"/>
        </svg>
        @break

    @case('reduce-dataset')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <line x1="9" y1="12" x2="15" y2="12"/>
            <line x1="9" y1="8" x2="15" y2="8"/>
            <line x1="9" y1="16" x2="15" y2="16"/>
            <line x1="17" y1="3" x2="17" y2="21"/>
        </svg>
        @break

    @case('edit-dataset')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 20h9"/>
            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            <path d="M3 14h4"/>
            <path d="M3 9h4"/>
        </svg>
        @break

    @case('extend-dataset')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="15" height="15" rx="2"/>
            <rect x="8" y="7" width="15" height="15" rx="2"/>
            <path d="M16 13h3"/>
            <path d="M19 10v6"/>
        </svg>
        @break

    @case('delete-dataset')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 6h18"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            <line x1="10" y1="11" x2="10" y2="17"/>
            <line x1="14" y1="11" x2="14" y2="17"/>
        </svg>
        @break
@endswitch
