<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;

class SettingController extends Controller
{
    public function update(Request $request)
    {
        $settings = $request->except('_token', '_method');
        $group = $request->input('group', 'general');

        foreach ($settings as $key => $value) {
            if ($key === 'group') continue;
            
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui');
    }
}
