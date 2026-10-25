<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'SUPER_ADMIN');

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($admins);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = DB::transaction(function () use ($validated, $request) {
            $admin = User::create([
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'SUPER_ADMIN',
            ]);

            AuditLog::log(
                'CREATE_ADMIN',
                "Menambahkan Super Admin baru '{$admin->nama}' ({$admin->email})",
                ['admin_id' => $admin->id, 'email' => $admin->email],
                $request->user()
            );

            return $admin;
        });

        return response()->json([
            'message' => 'Admin baru berhasil ditambahkan',
            'data' => $admin,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $admin = User::where('role', 'SUPER_ADMIN')->findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        DB::transaction(function () use ($admin, $validated, $request) {
            if (isset($validated['nama'])) {
                $admin->nama = $validated['nama'];
            }

            if (isset($validated['email'])) {
                $admin->email = $validated['email'];
            }

            if (! empty($validated['password'])) {
                $admin->password = Hash::make($validated['password']);
            }

            $admin->save();

            AuditLog::log(
                'UPDATE_ADMIN',
                "Memperbarui data Super Admin '{$admin->nama}' ({$admin->email})",
                ['admin_id' => $admin->id, 'email' => $admin->email],
                $request->user()
            );
        });

        return response()->json([
            'message' => 'Data admin berhasil diperbarui',
            'data' => $admin,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $admin = User::where('role', 'SUPER_ADMIN')->findOrFail($id);

        if ($admin->id === $request->user()->id) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun Super Admin yang sedang digunakan.',
            ], 422);
        }

        $superAdminCount = User::where('role', 'SUPER_ADMIN')->count();
        if ($superAdminCount <= 1) {
            return response()->json([
                'message' => 'Tidak dapat menghapus Super Admin terakhir dari sistem.',
            ], 422);
        }

        $email = $admin->email;
        $nama = $admin->nama;

        DB::transaction(function () use ($admin, $id, $email, $nama, $request) {
            $admin->delete();

            AuditLog::log(
                'DELETE_ADMIN',
                "Menghapus Super Admin '{$nama}' ({$email})",
                ['admin_id' => $id, 'email' => $email],
                $request->user()
            );
        });

        return response()->json([
            'message' => 'Super Admin berhasil dihapus',
        ]);
    }
}
