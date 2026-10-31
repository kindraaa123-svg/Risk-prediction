<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()->with('roles')->latest()->get()->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'role' => $user->getRoleNames()->first(),
                'created_at' => $user->created_at?->toDateString(),
            ]),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $temporaryPassword = Str::password(14);

        DB::transaction(function () use ($data, $temporaryPassword): void {
            $data['password'] = $temporaryPassword;
            $user = User::create($data);
            $user->syncRoles([$data['role']]);
        });

        return back()->with('success', 'User berhasil dibuat. Password sementara: '.$temporaryPassword);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        unset($data['role']);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($data, $request, $user): void {
            $user->update($data);
            $user->syncRoles([$request->validated('role')]);
        });

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->can('users.delete'), 403);
        abort_if($user->is(auth()->user()), 422, 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        abort_if($user->hasRole('Superadmin') && User::role('Superadmin')->where('status', 'active')->count() <= 1, 422, 'Superadmin aktif terakhir tidak dapat dihapus.');

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->can('users.update'), 403);

        $temporaryPassword = Str::password(14);
        $user->forceFill(['password' => $temporaryPassword])->save();

        return back()->with('success', 'Password '.$user->name.' berhasil direset. Password sementara: '.$temporaryPassword);
    }
}
