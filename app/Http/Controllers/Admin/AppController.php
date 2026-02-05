<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
    /**
     * Display a listing of apps
     */
    public function index(Request $request)
    {
        $query = App::with('creator')
            ->withCount('payments');

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $apps = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $apps,
        ]);
    }

    /**
     * Store a newly created app
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'webhook_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $app = App::create([
            'name' => $request->name,
            'description' => $request->description,
            'webhook_url' => $request->webhook_url,
            'access_token' => App::generateAccessToken(),
            'created_by' => $request->user()->id,
            'is_active' => true,
        ]);

        // Return the token only once during creation
        $app->makeVisible('access_token');

        return response()->json([
            'success' => true,
            'message' => 'App created successfully',
            'data' => $app,
        ], 201);
    }

    /**
     * Display the specified app
     */
    public function show(App $app)
    {
        $app->load('creator');
        $app->loadCount(['payments', 'webhookLogs']);

        return response()->json([
            'success' => true,
            'data' => $app,
        ]);
    }

    /**
     * Update the specified app
     */
    public function update(Request $request, App $app)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $app->update($request->only([
            'name',
            'description',
            'webhook_url',
            'is_active',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'App updated successfully',
            'data' => $app,
        ]);
    }

    /**
     * Remove the specified app
     */
    public function destroy(App $app)
    {
        $app->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'App deactivated successfully',
        ]);
    }

    /**
     * Regenerate access token
     */
    public function regenerateToken(App $app)
    {
        $newToken = $app->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Access token regenerated successfully',
            'data' => [
                'access_token' => $newToken,
            ],
        ]);
    }
}
