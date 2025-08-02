<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Http\Requests\AppSettingRequest;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AppSettingController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = $this->settingsService->getAllSettingsGrouped();

        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Settings/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AppSettingRequest $settingRequest, Request $request)
    {
        $data = $settingRequest->safe()->toArray();

        $file = $request->hasFile('file') ? $request->file('file') : null;

        $setting = $this->settingsService->createOrUpdateSetting($data, $file);

        return redirect()->route('settings.index')->with('success', 'Setting berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(AppSetting $setting)
    {
        return Inertia::render('Settings/Show', [
            'setting' => $setting
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AppSetting $setting)
    {
        return Inertia::render('Settings/Edit', [
            'setting' => $setting
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AppSetting $setting)
    {
        // Validate only value and file
        $request->validate([
            'value' => 'nullable|string',
            'file' => 'nullable|file|max:2048|mimes:jpg,jpeg,png,gif,ico,svg'
        ]);

        $data = ['value' => $request->get('value')];
        $file = $request->hasFile('file') ? $request->file('file') : null;

        $updatedSetting = $this->settingsService->createOrUpdateSetting($data, $file, $setting);

        return redirect()->route('settings.index')->with('success', 'Setting berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AppSetting $setting)
    {
        // Delete associated file if exists
        if ($setting->value && str_starts_with($setting->value, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $setting->value);
            Storage::disk('public')->delete($oldPath);
        }

        $setting->delete();

        return redirect()->route('settings.index')->with('success', 'Setting berhasil dihapus');
    }

    /**
     * Update multiple settings at once
     */
    public function updateBatch(Request $request)
    {
        $settings = $request->get('settings', []);

        $this->settingsService->updateBatch($settings);

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan');
    }
}
