<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Car Rental') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2">Car Rental Admin</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Login with your phone number</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white dark:bg-[#161615] rounded-lg shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] p-8">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-[#fff2f2] dark:bg-[#1D0002] border border-red-200 dark:border-red-900 rounded-lg">
                    <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900 rounded-lg">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Phone Number Input -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium mb-2">
                        Phone Number
                    </label>
                    <input 
                        type="tel" 
                        name="phone_number" 
                        id="phone_number" 
                        value="{{ old('phone_number') }}"
                        placeholder="+96170123456"
                        required
                        autofocus
                        class="w-full px-4 py-3 bg-[#FDFDFC] dark:bg-[#0a0a0a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433] transition-colors"
                    >
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        Enter phone with country code (e.g., +961...)
                    </p>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required
                        class="w-full px-4 py-3 bg-[#FDFDFC] dark:bg-[#0a0a0a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433] transition-colors"
                    >
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded border-[#e3e3e0] dark:border-[#3E3E3A] text-[#f53003] focus:ring-[#f53003]"
                        >
                        <span class="ml-2 text-sm">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-[#f53003] dark:text-[#FF4433] hover:underline">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full px-5 py-3 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-lg font-medium hover:bg-black dark:hover:bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2"
                >
                    Login
                </button>

                <!-- Register Link -->
                @if (Route::has('register'))
                    <div class="text-center text-sm">
                        <span class="text-[#706f6c] dark:text-[#A1A09A]">Don't have an account?</span>
                        <a href="{{ route('register') }}" class="ml-1 text-[#f53003] dark:text-[#FF4433] hover:underline font-medium">
                            Register here
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Back to Home Link -->
        <div class="text-center mt-6">
            <a href="{{ url('/') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors">
                ← Back to Home
            </a>
        </div>
    </div>
</body>
</html>