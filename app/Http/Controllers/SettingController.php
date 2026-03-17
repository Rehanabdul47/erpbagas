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
            $passwordPolicy = \App\Models\Setting::where('key', 'password_policy')->value('value') ?? 'low';
            
            $rules = ['required', 'string', 'min:6'];
            if ($passwordPolicy === 'medium') {
                $rules = ['required', 'string', 'min:8', 'regex:/[a-zA-Z]/', 'regex:/[0-9]/'];
            } elseif ($passwordPolicy === 'high') {
                $rules = ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'];
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'new_password' => $rules
            ], [
                'new_password.min' => 'Kata sandi baru minimal :min karakter.',
                'new_password.regex' => 'Kata sandi baru tidak memenuhi kriteria kebijakan keamanan (Medium: min 8 karakter alfanumerik. High: min 8 karakter dengan kombinasi huruf besar, kecil, angka, & simbol).'
            ]);

            if ($validator->fails()) {
                return back()->with('error', $validator->errors()->first('new_password'));
            }

            $user = auth()->user();
            $user->password = bcrypt($request->new_password);
            $user->save();
        }

        // Handle file uploads separately
        $allFiles = $request->allFiles();
        foreach ($allFiles as $fileKey => $file) {
            $filename = $fileKey . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images'), $filename);

            Setting::updateOrCreate(
                ['key' => $fileKey],
                ['value' => 'assets/images/' . $filename, 'group' => $group]
            );
        }

        // Get all input data and remove keys we don't want to save as settings
        $data = $request->all();
        $excludeKeys = [
            '_token', '_method', 'current_password', 'new_password', 
            'new_password_confirmation', 'group'
        ];
        
        // Also exclude all file keys
        foreach (array_keys($allFiles) as $fileKey) {
            $excludeKeys[] = $fileKey;
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $excludeKeys)) continue;
            
            // Skip if value is an object (like a file) just in case
            if (is_object($value)) continue;

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui');
    }

    public function manualBackup()
    {
        $data = [
            'timestamp' => now()->toIso8601String(),
            'users' => \App\Models\User::all(),
            'settings' => \App\Models\Setting::all(),
        ];

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $data['roles'] = \Spatie\Permission\Models\Role::all();
            $data['permissions'] = \Spatie\Permission\Models\Permission::all();
        }

        $json = json_encode($data, JSON_PRETTY_PRINT);
        $filename = 'gupala-backup-' . date('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function exportAudit()
    {
        $users = \App\Models\User::all();

        $filename = 'gupala-audit-log-' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Created At', 'Last Updated']);

            foreach ($users as $user) {
                // Check if spatie roles is applied
                $role = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->join(', ') : 'N/A';
                
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $role,
                    $user->created_at,
                    $user->updated_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
