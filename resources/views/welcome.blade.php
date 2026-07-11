@php
    if (auth()->check()) {
        header('Location: /dashboard');
        exit;
    } else {
        header('Location: /login');
        exit;
    }
@endphp
