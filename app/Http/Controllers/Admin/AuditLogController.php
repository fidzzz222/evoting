<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AuditLogController extends Controller
{
    // daftar riwayat log aktivitas sistem
    public function index(Request $request)
    {
        $search = $request->query('search');
        $action = $request->query('action');

        $logs = AuditLog::query()
            ->with(['user', 'voter'])
            ->when($search, function ($query, $s) {
                $query->where(function ($q) use ($s) {
                    $q->where('description', 'like', "%{$s}%")
                      ->orWhere('ip_address', 'like', "%{$s}%");
                });
            })
            ->when($action, function ($query, $a) {
                $query->where('action', $a);
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $actionTypes = AuditLog::select('action')->distinct()->pluck('action');

        $totalLogs = AuditLog::count();
        $todayLogs = AuditLog::whereDate('created_at', today())->count();
        $securityEvents = AuditLog::where(function ($q) {
            $q->where('action', 'like', '%DELETE%')
              ->orWhere('action', 'like', '%PRUNE%')
              ->orWhere('action', 'like', '%SECURITY%')
              ->orWhere('action', 'like', '%CLEAR%');
        })->count();

        return view('admin.logs.index', compact(
            'logs', 'search', 'action', 'actionTypes',
            'totalLogs', 'todayLogs', 'securityEvents'
        ));
    }

    // hapus log lawas
    public function prune(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $deleted = AuditLog::where('created_at', '<', now()->subDays($days))->delete();

        AuditLog::log('LOGS_PRUNED', "Membersihkan {$deleted} catatan audit trail yang lebih dari {$days} hari");

        return back()->with('success', "Berhasil membersihkan {$deleted} catatan log lama.");
    }

    // bersihkan cache aplikasi
    public function clearCache()
    {
        Artisan::call('optimize:clear');
        
        AuditLog::log('SYSTEM_CACHE_CLEARED', 'Melakukan pembersihan cache sistem (optimize:clear)');

        return back()->with('success', 'Cache sistem (view, route, config) berhasil dibersihkan dan dioptimalkan!');
    }
}
