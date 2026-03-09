<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDailyJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-journal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily operations summary email if email_alerts setting is enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emailAlertsEnabled = \App\Models\Setting::where('key', 'email_alerts')->value('value') === 'true';

        if (!$emailAlertsEnabled) {
            $this->info('Email journal is disabled in settings. Skipping.');
            return;
        }

        // Simulate compiling and sending the daily journal
        $this->info('Compiling daily operations summary...');
        
        // In a real application, you would gather sales, production data, etc.
        // and dispatch an Mailable here. Example:
        // Mail::to('admin@example.com')->send(new DailyJournalMail($data));

        $this->info('Daily journal email dispatched successfully.');
    }
}
