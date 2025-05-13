<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ollama Tester') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    
    <!-- Alpine JS CDN for sidebar functionality -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        // Check for saved theme preference or use the system preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased animated-bg" x-data="{ sidebarOpen: false, toggleTheme() { if (localStorage.theme === 'dark') { localStorage.theme = 'light'; document.documentElement.classList.remove('dark'); } else { localStorage.theme = 'dark'; document.documentElement.classList.add('dark'); } } }">
    <div class="flex min-h-screen">
        <!-- Glassmorphic Sidebar -->
        <div id="sidebar" class="glass fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 ease-in-out md:translate-x-0 md:relative md:flex md:flex-col border-r border-glass-border backdrop-blur-md"
             :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center justify-center h-16 p-4 border-b border-glass-border">
                <a href="{{ route('ollama.servers.index') }}" class="neon-text-primary text-xl font-bold">
                    Ollama Tester
                </a>
            </div>

            <!-- Theme Toggle -->
            <div class="p-3">
                <button 
                    @click="toggleTheme()" 
                    class="w-full flex items-center justify-between px-4 py-2 rounded-md hover:bg-glass-bg/30 transition-colors"
                >
                    <span class="flex items-center">
                        <svg x-show="!document.documentElement.classList.contains('dark')" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="document.documentElement.classList.contains('dark')" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span x-text="document.documentElement.classList.contains('dark') ? 'Light Mode' : 'Dark Mode'"></span>
                    </span>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 flex flex-col overflow-y-auto py-4 px-2">
                <div class="flex flex-col space-y-1">
                    <a href="{{ route('ollama.servers.index') }}" 
                       class="px-4 py-2 rounded-md flex items-center transition-colors {{ request()->routeIs('ollama.servers.*') ? 'neon-text-primary bg-glass-bg/50' : 'text-foreground/70 hover:text-foreground hover:bg-glass-bg/30' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                        </svg>
                        Servers
                    </a>
                    @auth
                    <a href="{{ route('settings.index') }}" 
                       class="px-4 py-2 rounded-md flex items-center transition-colors {{ request()->routeIs('settings.*') ? 'neon-text-primary bg-glass-bg/50' : 'text-foreground/70 hover:text-foreground hover:bg-glass-bg/30' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </a>
                    @endauth
                </div>
            </div>

            <!-- Authentication Links -->
            <div class="p-4 border-t border-glass-border">
                @auth
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-foreground/70">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-foreground/70 hover:text-foreground flex items-center transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex flex-col space-y-2">
                        <a href="{{ route('login') }}" class="text-sm neon-btn-primary w-full py-2 rounded text-center">Login</a>
                        <a href="{{ route('register') }}" class="text-sm neon-btn-secondary w-full py-2 rounded text-center">Register</a>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Mobile Header & Nav Toggle -->
            <div class="md:hidden glass-card sticky top-0 z-40 flex items-center justify-between h-16 px-4 border-b border-glass-border backdrop-blur-md">
                <button @click="sidebarOpen = !sidebarOpen" class="glass p-2 rounded focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="{{ route('ollama.servers.index') }}" class="neon-text-primary text-xl font-bold">
                    Ollama Tester
                </a>
                <div class="w-6"><!-- Spacer for balance --></div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden md:ml-0">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Backdrop overlay for mobile sidebar -->
    <div 
        class="fixed inset-0 bg-glass-bg/50 backdrop-blur-sm z-40 md:hidden transition-opacity duration-300"
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false">
    </div>
    
    @yield('scripts')
</body>
</html> 