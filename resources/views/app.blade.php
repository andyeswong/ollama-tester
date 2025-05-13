<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
            
            /* Add animated gradient background */
            body {
                background-image: 
                  radial-gradient(circle at 10% 20%, hsla(220, 100%, 70%, 0.1) 0%, transparent 20%),
                  radial-gradient(circle at 80% 30%, hsla(280, 100%, 75%, 0.1) 0%, transparent 25%),
                  radial-gradient(circle at 40% 70%, hsla(160, 100%, 65%, 0.1) 0%, transparent 30%);
                background-size: 100% 100%;
                background-attachment: fixed;
            }
            
            /* Glassmorphism loader */
            .glass-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                background-color: hsla(220, 20%, 97%, 0.4);
            }
            
            .dark .glass-loader {
                background-color: hsla(220, 20%, 5%, 0.4);
            }
            
            .glass-loader-content {
                text-align: center;
                background-color: hsla(220, 20%, 100%, 0.25);
                border-radius: 1rem;
                padding: 2rem;
                border: 1px solid hsla(220, 20%, 100%, 0.3);
                box-shadow: 0 8px 32px hsla(220, 20%, 5%, 0.1);
            }
            
            .dark .glass-loader-content {
                background-color: hsla(220, 20%, 10%, 0.25);
                border: 1px solid hsla(220, 20%, 30%, 0.3);
                box-shadow: 0 8px 32px hsla(220, 20%, 0%, 0.2);
            }
            
            .glass-loader-spinner {
                display: inline-block;
                width: 50px;
                height: 50px;
                border: 3px solid transparent;
                border-top-color: hsla(220, 100%, 65%, 1);
                border-radius: 50%;
                animation: spin 1s linear infinite;
                box-shadow: 0 0 15px hsla(220, 100%, 65%, 0.5);
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased animated-bg">
        <div id="app-loading" class="glass-loader">
            <div class="glass-loader-content">
                <div class="glass-loader-spinner"></div>
                <p class="mt-4 text-foreground">Loading...</p>
            </div>
        </div>
        
        @inertia

        <script>
            // Remove loader once the page has loaded
            window.addEventListener('load', function() {
                const loader = document.getElementById('app-loading');
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        loader.style.display = 'none';
                    }, 500);
                }
            });
        </script>
    </body>
</html>
