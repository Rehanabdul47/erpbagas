<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        return view('pages.settings', compact('settings'))->with('activePage', 'settings');
    }

    public function update(Request $request)
    {
        $settings = $request->except('_token', '_method');
        $group = $request->input('group', 'general');

        foreach ($settings as $key => $value) {
            if ($key === 'group') continue;

            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/images'), $filename);
                $value = 'assets/images/' . $filename;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui');
    }
}
