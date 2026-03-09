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
        $settings = $request->except('_token', '_method', 'current_password', 'new_password', 'new_password_confirmation');
        $group = $request->input('group', 'general');

        if ($group === 'security' && $request->filled('current_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, auth()->user()->password)) {
                return back()->with('error', 'Kata sandi saat ini tidak cocok.');
            }
            if ($request->new_password !== $request->new_password_confirmation) {
                return back()->with('error', 'Konfirmasi kata sandi baru tidak cocok.');
            }
            if (strlen($request->new_password) < 6) {
                return back()->with('error', 'Kata sandi baru minimal 6 karakter.');
            }

            $user = auth()->user();
            $user->password = bcrypt($request->new_password);
            $user->save();
        }

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
