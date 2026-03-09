<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
Schedule::command('app:sync-ecommerce-orders')->everyFiveMinutes();

Schedule::call(function () {
    if (\App\Models\Setting::where('key', 'auto_backup')->value('value') === 'true') {
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
        
        \Illuminate\Support\Facades\Storage::disk('local')->put("backups/{$filename}", $json);
    }
})->daily();

Schedule::command('app:send-daily-journal')->dailyAt('23:50');
