<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Tampilkan daftar petugas dan admin.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $users = User::query()
            ->when($search, function ($query, $s) {
                $query->where('nama', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            })
            ->when($role, function ($query, $r) {
                $query->where('role', $r);
            })
            ->orderBy('role', 'asc')
            ->orderBy('nama', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalUsers = User::count();
        $totalSuperAdmin = User::where('role', 'SUPER_ADMIN')->count();
        $totalPetugas = User::where('role', 'PETUGAS')->count();

        return view('admin.users.index', compact(
            'users', 'search', 'role',
            'totalUsers', 'totalSuperAdmin', 'totalPetugas'
        ));
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:SUPER_ADMIN,PETUGAS',
        ]);

        $user = User::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        AuditLog::log('USER_CREATED', "Membuat akun {$user->role}: {$user->nama} ({$user->email})", [
            'created_user_id' => $user->id,
            'role' => $user->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->nama} ({$user->role}) berhasil ditambahkan!");
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Perbarui data user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:SUPER_ADMIN,PETUGAS',
        ]);

        $data = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        AuditLog::log('USER_UPDATED', "Memperbarui akun {$user->nama} ({$user->email})");

        return redirect()->route('admin.users.index')
            ->with('success', "Data akun {$user->nama} berhasil diperbarui!");
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $nama = $user->nama;
        $user->delete();

        AuditLog::log('USER_DELETED', "Menghapus akun panitia: {$nama}");

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$nama} berhasil dihapus.");
    }
}
