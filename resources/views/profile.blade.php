@extends('layouts.app')

@section('page-title', 'Pengaturan')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="glass-card">
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="glass-card">
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <div class="glass-card">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
@endsection
