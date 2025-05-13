<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SettingsController extends Controller
{
    /**
     * Display the main settings page.
     */
    public function index(): View
    {
        return view('settings.index');
    }

    /**
     * Display the integrated settings page.
     */
    public function generalSettings()
    {
        return redirect()->route('settings.all');
    }

    /**
     * Display the all-in-one settings page.
     */
    public function allSettings(): View
    {
        return view('settings.all');
    }

    /**
     * Display the broadcast settings page.
     */
    public function showBroadcast(): View
    {
        return view('settings.broadcast');
    }

    /**
     * Update the broadcast settings.
     */
    public function updateBroadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'broadcast_driver' => 'required|in:pusher,redis,log,null',
            'pusher_app_id' => 'nullable|string',
            'pusher_app_key' => 'nullable|string',
            'pusher_app_secret' => 'nullable|string',
            'pusher_app_cluster' => 'nullable|string',
            'pusher_host' => 'nullable|string',
            'pusher_port' => 'nullable|numeric',
            'pusher_scheme' => 'nullable|in:http,https',
        ]);

        // Update .env file
        $this->updateEnvironmentFile([
            'BROADCAST_DRIVER' => $validated['broadcast_driver'],
            'PUSHER_APP_ID' => $validated['pusher_app_id'] ?? '',
            'PUSHER_APP_KEY' => $validated['pusher_app_key'] ?? '',
            'PUSHER_APP_SECRET' => $validated['pusher_app_secret'] ?? '',
            'PUSHER_APP_CLUSTER' => $validated['pusher_app_cluster'] ?? 'mt1',
            'PUSHER_HOST' => $validated['pusher_host'] ?? '',
            'PUSHER_PORT' => $validated['pusher_port'] ?? '443',
            'PUSHER_SCHEME' => $validated['pusher_scheme'] ?? 'https',
        ]);

        // Use URL concatenation instead of fragment() method
        return redirect()
            ->to(route('settings.all') . '#broadcast')
            ->with('success', 'Broadcast settings updated successfully.');
    }

    /**
     * Update the environment file with the given data.
     */
    protected function updateEnvironmentFile(array $data): bool
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return false;
        }

        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            // Check if the key exists in the .env file
            if (strpos($content, "{$key}=") !== false) {
                // Replace the key's value
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $content
                );
            } else {
                // Add the key and value
                $content .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $content);

        return true;
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Use URL concatenation instead of fragment() method
        return redirect()
            ->to(route('settings.all') . '#profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Use URL concatenation instead of fragment() method
        return redirect()
            ->to(route('settings.all') . '#password')
            ->with('success', 'Password updated successfully.');
    }
} 