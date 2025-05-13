@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="flex flex-col md:flex-row space-y-6 md:space-y-0 md:space-x-8">
        <!-- Settings Navigation Sidebar -->
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Settings</h2>
                </div>
                
                <div class="flex flex-col divide-y">
                    <a href="#profile" class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150 text-sm font-medium text-gray-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profile
                    </a>
                    <a href="#password" class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150 text-sm font-medium text-gray-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Password
                    </a>
                    <a href="#appearance" class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150 text-sm font-medium text-gray-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        Appearance
                    </a>
                    <a href="#broadcast" class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150 text-sm font-medium text-gray-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                        Broadcast Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="flex-1">
            <!-- Profile Section -->
            <section id="profile" class="mb-10">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h2 class="text-xl font-semibold text-gray-800">Profile Information</h2>
                        <p class="text-sm text-gray-600 mt-1">Update your account's profile information and email address.</p>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('settings.profile.update') }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PATCH')
                            
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input id="name" name="name" type="text" value="{{ Auth::user()->name }}" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                @error('name')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input id="email" name="email" type="email" value="{{ Auth::user()->email }}" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                @error('email')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
            
            <!-- Password Section -->
            <section id="password" class="mb-10">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h2 class="text-xl font-semibold text-gray-800">Update Password</h2>
                        <p class="text-sm text-gray-600 mt-1">Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('settings.password.update') }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-2">
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input id="current_password" name="current_password" type="password" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                @error('current_password')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input id="password" name="password" type="password" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                @error('password')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                            </div>
                            
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
            
            <!-- Appearance Section -->
            <section id="appearance" class="mb-10">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h2 class="text-xl font-semibold text-gray-800">Appearance</h2>
                        <p class="text-sm text-gray-600 mt-1">Customize the appearance of your dashboard.</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            <div class="space-y-4">
                                <label class="block text-sm font-medium text-gray-700">Theme</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <!-- Light Theme Option -->
                                    <div class="appearance-option" data-theme="light">
                                        <div class="flex flex-col items-center justify-between rounded-md border-2 border-gray-200 hover:border-indigo-500 p-4 hover:bg-gray-50 cursor-pointer">
                                            <div class="rounded-md border border-gray-200 bg-white p-2">
                                                <div class="space-y-2">
                                                    <div class="h-2 w-8 rounded-lg bg-gray-900"></div>
                                                    <div class="h-2 w-[80px] rounded-lg bg-gray-300"></div>
                                                    <div class="h-2 w-[120px] rounded-lg bg-gray-300"></div>
                                                </div>
                                            </div>
                                            <span class="mt-2 font-medium">Light</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Dark Theme Option -->
                                    <div class="appearance-option" data-theme="dark">
                                        <div class="flex flex-col items-center justify-between rounded-md border-2 border-gray-600 hover:border-indigo-500 bg-gray-900 p-4 hover:bg-gray-800 cursor-pointer">
                                            <div class="rounded-md border border-gray-700 bg-gray-900 p-2">
                                                <div class="space-y-2">
                                                    <div class="h-2 w-8 rounded-lg bg-gray-100"></div>
                                                    <div class="h-2 w-[80px] rounded-lg bg-gray-700"></div>
                                                    <div class="h-2 w-[120px] rounded-lg bg-gray-700"></div>
                                                </div>
                                            </div>
                                            <span class="mt-2 font-medium text-white">Dark</span>
                                        </div>
                                    </div>
                                    
                                    <!-- System Theme Option -->
                                    <div class="appearance-option" data-theme="system">
                                        <div class="flex flex-col items-center justify-between rounded-md border-2 border-gray-200 hover:border-indigo-500 p-4 hover:bg-gray-50 cursor-pointer">
                                            <div class="rounded-md border border-gray-200 bg-white p-2">
                                                <div class="flex space-x-2">
                                                    <div class="space-y-2 w-1/2">
                                                        <div class="h-2 w-6 rounded-lg bg-gray-300"></div>
                                                        <div class="h-2 w-10 rounded-lg bg-gray-300"></div>
                                                    </div>
                                                    <div class="space-y-2 w-1/2 bg-gray-900 rounded-md p-1">
                                                        <div class="h-2 w-6 rounded-lg bg-gray-500"></div>
                                                        <div class="h-2 w-10 rounded-lg bg-gray-500"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="mt-2 font-medium">System</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Broadcast Settings Section -->
            <section id="broadcast" class="mb-10">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h2 class="text-xl font-semibold text-gray-800">Broadcast Settings</h2>
                        <p class="text-sm text-gray-600 mt-1">Configure Pusher for real-time updates and notifications.</p>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('settings.broadcast.update') }}" method="POST" class="space-y-6">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="mb-4">
                                        <label for="broadcast_driver" class="block text-sm font-medium text-gray-700 mb-1">Broadcast Driver</label>
                                        <select name="broadcast_driver" id="broadcast_driver" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                            <option value="pusher" {{ env('BROADCAST_DRIVER') === 'pusher' ? 'selected' : '' }}>Pusher</option>
                                            <option value="redis" {{ env('BROADCAST_DRIVER') === 'redis' ? 'selected' : '' }}>Redis</option>
                                            <option value="log" {{ env('BROADCAST_DRIVER') === 'log' ? 'selected' : '' }}>Log</option>
                                            <option value="null" {{ env('BROADCAST_DRIVER') === 'null' ? 'selected' : '' }}>Null</option>
                                        </select>
                                        <p class="text-gray-500 text-xs mt-1">Select the broadcast driver to use for real-time updates.</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="pusher_app_id" class="block text-sm font-medium text-gray-700 mb-1">Pusher App ID</label>
                                        <input type="text" name="pusher_app_id" id="pusher_app_id" value="{{ env('PUSHER_APP_ID') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="pusher_app_key" class="block text-sm font-medium text-gray-700 mb-1">Pusher App Key</label>
                                        <input type="text" name="pusher_app_key" id="pusher_app_key" value="{{ env('PUSHER_APP_KEY') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="pusher_app_secret" class="block text-sm font-medium text-gray-700 mb-1">Pusher App Secret</label>
                                        <input type="password" name="pusher_app_secret" id="pusher_app_secret" value="{{ env('PUSHER_APP_SECRET') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="mb-4">
                                        <label for="pusher_app_cluster" class="block text-sm font-medium text-gray-700 mb-1">Pusher App Cluster</label>
                                        <input type="text" name="pusher_app_cluster" id="pusher_app_cluster" value="{{ env('PUSHER_APP_CLUSTER', 'mt1') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                        <p class="text-gray-500 text-xs mt-1">Common clusters: us2, eu, ap1, ap2, etc.</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="pusher_host" class="block text-sm font-medium text-gray-700 mb-1">Pusher Host (optional)</label>
                                        <input type="text" name="pusher_host" id="pusher_host" value="{{ env('PUSHER_HOST') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                        <p class="text-gray-500 text-xs mt-1">Only needed for self-hosted Pusher servers.</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="pusher_port" class="block text-sm font-medium text-gray-700 mb-1">Pusher Port</label>
                                        <input type="text" name="pusher_port" id="pusher_port" value="{{ env('PUSHER_PORT', '443') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="pusher_scheme" class="block text-sm font-medium text-gray-700 mb-1">Pusher Scheme</label>
                                        <select name="pusher_scheme" id="pusher_scheme" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                                            <option value="https" {{ env('PUSHER_SCHEME') === 'https' ? 'selected' : '' }}>HTTPS</option>
                                            <option value="http" {{ env('PUSHER_SCHEME') === 'http' ? 'selected' : '' }}>HTTP</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 bg-gray-50 p-4 rounded border">
                                <h3 class="font-medium text-gray-700 mb-2">Testing Connection</h3>
                                <p class="text-sm text-gray-600 mb-2">After saving your Pusher configuration, you can test the connection to ensure real-time updates are working.</p>
                                <button type="button" id="testConnection" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded text-sm">
                                    Test Connection
                                </button>
                                <div id="connectionStatus" class="mt-2 text-sm"></div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                                    Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Theme selection
        const appearanceOptions = document.querySelectorAll('.appearance-option');
        
        // Get the current theme from localStorage or default to light
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        // Apply active class to current theme
        appearanceOptions.forEach(option => {
            const theme = option.getAttribute('data-theme');
            const optionEl = option.querySelector('div');
            
            if (theme === currentTheme) {
                optionEl.classList.add('border-indigo-500');
                optionEl.classList.remove('border-gray-200', 'border-gray-600');
            }
            
            option.addEventListener('click', function() {
                // Remove active class from all options
                appearanceOptions.forEach(opt => {
                    const el = opt.querySelector('div');
                    el.classList.remove('border-indigo-500');
                    el.classList.add(theme === 'dark' ? 'border-gray-600' : 'border-gray-200');
                });
                
                // Add active class to clicked option
                optionEl.classList.add('border-indigo-500');
                optionEl.classList.remove('border-gray-200', 'border-gray-600');
                
                // Update theme
                applyTheme(theme);
            });
        });
        
        function applyTheme(theme) {
            // Save to localStorage
            localStorage.setItem('theme', theme);
            
            // Apply theme to document
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                // System preference
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        }
        
        // Apply the current theme
        applyTheme(currentTheme);
        
        // Test Pusher connection
        const testButton = document.getElementById('testConnection');
        const connectionStatus = document.getElementById('connectionStatus');
        
        if (testButton && connectionStatus) {
            testButton.addEventListener('click', function() {
                connectionStatus.innerHTML = '<span class="text-blue-600">Testing connection...</span>';
                
                const appId = document.getElementById('pusher_app_id').value;
                const appKey = document.getElementById('pusher_app_key').value;
                const appSecret = document.getElementById('pusher_app_secret').value;
                const appCluster = document.getElementById('pusher_app_cluster').value;
                
                if (!appId || !appKey || !appSecret || !appCluster) {
                    connectionStatus.innerHTML = '<span class="text-red-600">Please fill in all required Pusher fields.</span>';
                    return;
                }
                
                try {
                    const pusher = new Pusher(appKey, {
                        cluster: appCluster,
                        encrypted: true
                    });
                    
                    pusher.connection.bind('connected', function() {
                        connectionStatus.innerHTML = '<span class="text-green-600">✓ Connection successful! Pusher is properly configured.</span>';
                        setTimeout(() => {
                            pusher.disconnect();
                        }, 2000);
                    });
                    
                    pusher.connection.bind('failed', function() {
                        connectionStatus.innerHTML = '<span class="text-red-600">✗ Connection failed. Please check your Pusher credentials.</span>';
                    });
                    
                } catch (error) {
                    connectionStatus.innerHTML = `<span class="text-red-600">✗ Error: ${error.message}</span>`;
                }
            });
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 20,
                        behavior: 'smooth'
                    });
                    
                    // Add active state to clicked nav item
                    document.querySelectorAll('.flex.flex-col.divide-y a').forEach(link => {
                        link.classList.remove('bg-gray-50');
                    });
                    this.classList.add('bg-gray-50');
                }
            });
        });
    });
</script>
@endsection
@endsection 