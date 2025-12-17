<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    /**
     * Update or create settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_logo' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}
