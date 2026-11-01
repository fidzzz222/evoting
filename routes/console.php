<?php

use App\Models\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:sync-status', function () {
    $result = Event::syncAllEventStatuses();
    $this->info("Sinkronisasi status agenda pemilihan berhasil. {$result['closed']} agenda ditutup, {$result['reverted']} agenda dikembalikan ke DRAFT (< 2 paslon), {$result['started']} agenda otomatis dibuka.");
})->purpose('Sinkronisasi status agenda pemilihan otomatis sesuai jadwal WIB (Bekasi, Jawa Barat)');

Schedule::command('events:sync-status')->everyMinute();
