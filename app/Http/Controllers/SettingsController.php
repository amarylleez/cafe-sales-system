<?php

namespace App\Http\Controllers;

class SettingsController extends Controller
{
    /**
     * Placeholder settings index.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Settings endpoint not implemented yet.',
        ]);
    }
}
