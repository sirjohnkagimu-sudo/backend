@extends('layouts.app')

@section('content')
@php
    $header = '';
@endphp

<div class="min-h-screen flex items-center justify-center bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-100 via-white to-emerald-100 px-4">

    <!-- Decorative Blurs -->
    <div class="absolute top-0 left-0 w-72 h-72 bg-indigo-300/30 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-72 h-72 bg-emerald-300/30 rounded-full blur-3xl"></div>

    <div class="relative w-full max-w-md">
        <!-- Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-gray-200 p-8">

            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="w-[50%] h-18 p-2 rounded-2xl bg-gradient-to-br from-white-600 to-emerald-500 flex items-center justify-center shadow-lg">
                        <img
                            src="https://i.imghippo.com/files/ajv8989ujg.png"
                            alt="Edumall"
                            class="h-14"
                        >
                    </div>
                </div>

                <h1 class="text-3xl font-extrabold bg-gradient-to-r from-indigo-600 to-emerald-500 bg-clip-text text-transparent">
                    Reset Password
                </h1>

                <p class="text-sm text-gray-500 mt-2">
                    Secure your Edumall account with a new password
                </p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Email Address
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ $email ?? old('email') }}"
                        required
                        autofocus
                        class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                    >
                    @error('email')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        New Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                    >
                    @error('password')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Confirm Password
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                    >
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full relative overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-emerald-500 py-3 font-semibold text-white shadow-lg transition hover:scale-[1.02] hover:shadow-xl active:scale-95"
                >
                    <span class="relative z-10">Reset Password</span>
                    <span class="absolute inset-0 bg-white/10 opacity-0 hover:opacity-100 transition"></span>
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-gray-400">
                © {{ date('Y') }} <span class="font-semibold text-gray-500">Edumall</span> — Learning made smarter
            </div>
        </div>
    </div>
</div>
@endsection
