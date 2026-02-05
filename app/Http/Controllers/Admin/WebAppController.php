<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use Illuminate\Http\Request;

class WebAppController extends Controller
{
    /**
     * Display a listing of apps
     */
    public function index(Request $request)
    {
        $query = App::with('creator')->withCount('payments');

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $apps = $query->latest()->paginate(15);

        return view('admin.apps.index', compact('apps'));
    }

    /**
     * Show the form for creating a new app
     */
    public function create()
    {
        return view('admin.apps.create');
    }

    /**
     * Store a newly created app
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'success_redirect_url' => 'nullable|url',
            'failure_redirect_url' => 'nullable|url',
        ]);

        $app = App::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'success_redirect_url' => $validated['success_redirect_url'] ?? null,
            'failure_redirect_url' => $validated['failure_redirect_url'] ?? null,
            'access_token' => App::generateAccessToken(),
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.apps.show', $app)
            ->with('success', 'App created successfully!')
            ->with('new_token', $app->access_token);
    }

    /**
     * Display the specified app
     */
    public function show(App $app)
    {
        $app->load('creator')->loadCount(['payments', 'webhookLogs']);
        
        $recentPayments = $app->payments()
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.apps.show', compact('app', 'recentPayments'));
    }

    /**
     * Show the form for editing the specified app
     */
    public function edit(App $app)
    {
        return view('admin.apps.edit', compact('app'));
    }

    /**
     * Update the specified app
     */
    public function update(Request $request, App $app)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'success_redirect_url' => 'nullable|url',
            'failure_redirect_url' => 'nullable|url',
            'is_active' => 'nullable|boolean',
        ]);

        $app->update($validated);

        return redirect()
            ->route('admin.apps.show', $app)
            ->with('success', 'App updated successfully!');
    }

    /**
     * Remove the specified app
     */
    public function destroy(App $app)
    {
        $app->update(['is_active' => false]);

        return redirect()
            ->route('admin.apps.index')
            ->with('success', 'App deactivated successfully!');
    }

    /**
     * Regenerate access token
     */
    public function regenerateToken(App $app)
    {
        $newToken = $app->regenerateToken();

        return redirect()
            ->route('admin.apps.show', $app)
            ->with('success', 'Access token regenerated successfully!')
            ->with('new_token', $newToken);
    }
}
