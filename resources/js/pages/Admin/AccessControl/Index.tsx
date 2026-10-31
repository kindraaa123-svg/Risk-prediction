import { Head, router } from '@inertiajs/react';
import { Check, LockKeyhole, Save, ShieldCheck } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import { Toast } from '../../../components/Toast';
import { SharedProps } from '../../../types';

type Permission = { name: string };
type Role = { id: number; name: string; permissions: Permission[] };
type Props = SharedProps & { roles: Role[]; permissions: string[] };

export default function Index({ roles, permissions }: Props) {
    const [selectedRoleId, setSelectedRoleId] = useState(roles[0]?.id ?? 0);
    const [checked, setChecked] = useState<string[]>(roles[0]?.permissions.map((permission) => permission.name) ?? []);
    const [saving, setSaving] = useState(false);
    const [toast, setToast] = useState<string | null>(null);
    const selectedRole = roles.find((role) => role.id === selectedRoleId);
    const grouped = useMemo(() => permissions.reduce<Record<string, string[]>>((groups, permission) => { const [module, action] = permission.split('.'); (groups[module] ??= []).push(action); return groups; }, {}), [permissions]);
    const modules = Object.entries(grouped);

    useEffect(() => {
        setChecked(selectedRole?.permissions.map((permission) => permission.name) ?? []);
    }, [selectedRoleId]);

    const toggle = (permission: string) => setChecked((current) => current.includes(permission) ? current.filter((item) => item !== permission) : [...current, permission]);
    const toggleModule = (modulePermissions: string[]) => {
        const allChecked = modulePermissions.every((permission) => checked.includes(permission));
        setChecked((current) => allChecked ? current.filter((permission) => !modulePermissions.includes(permission)) : Array.from(new Set([...current, ...modulePermissions])));
    };
    const save = () => {
        if (!selectedRole || selectedRole.name === 'Superadmin') return;
        setSaving(true);
        router.put(`/admin/access-control/roles/${selectedRole.id}`, { permissions: checked }, { preserveScroll: true, onSuccess: (page) => { setToast(String(((page.props as unknown as SharedProps).flash.success) ?? 'Hak akses berhasil disimpan.')); }, onError: () => setToast('Hak akses gagal disimpan.'), onFinish: () => setSaving(false) });
    };

    return <AppLayout><Head title="Access control" />{toast && <Toast message={toast} onClose={() => setToast(null)} />}<div className="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p className="mb-2 text-sm font-medium text-blue-600">Administration</p><h1 className="text-2xl font-semibold tracking-tight text-slate-900">Access control</h1><p className="mt-1 text-sm text-slate-500">Atur hak akses role melalui permission matrix.</p></div><button type="button" onClick={save} disabled={saving || selectedRole?.name === 'Superadmin'} className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"><Save size={17} />{saving ? 'Saving...' : 'Save changes'}</button></div><div className="mb-5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center"><div className="flex items-center gap-3"><div className="rounded-lg bg-blue-50 p-2 text-blue-600"><ShieldCheck size={19} /></div><div><p className="text-sm font-semibold text-slate-800">Role yang diatur</p><p className="text-xs text-slate-500">Pilih role untuk mengubah permission.</p></div></div><select value={selectedRoleId} onChange={(event) => setSelectedRoleId(Number(event.target.value))} className="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 sm:ml-auto sm:min-w-56">{roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}</select></div>{selectedRole?.name === 'Superadmin' && <div className="mb-5 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"><LockKeyhole size={17} />Permission Superadmin dilindungi untuk mencegah lockout admin.</div>}<div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div className="overflow-x-auto"><table className="w-full min-w-[680px] text-left text-sm"><thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th className="px-5 py-3 font-medium">Module</th><th className="px-5 py-3 font-medium">Permission</th><th className="w-28 px-5 py-3 text-center font-medium">Allow</th></tr></thead><tbody className="divide-y divide-slate-100">{modules.map(([module, actions]) => { const modulePermissions = actions.map((action) => `${module}.${action}`); const allChecked = modulePermissions.every((permission) => checked.includes(permission)); return <tr key={module} className="align-top"><td className="px-5 py-4 font-semibold capitalize text-slate-800">{module.replace(/-/g, ' ')}</td><td className="px-5 py-4"><div className="flex flex-wrap gap-2">{actions.map((action) => <span key={action} className="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600">{action}</span>)}</div></td><td className="px-5 py-4 text-center"><button type="button" onClick={() => toggleModule(modulePermissions)} disabled={selectedRole?.name === 'Superadmin'} className={`inline-flex h-7 w-7 items-center justify-center rounded-md border ${allChecked ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-transparent'} disabled:cursor-not-allowed`} aria-label={`Toggle ${module} permissions`}>{allChecked && <Check size={16} />}</button></td></tr>; })}</tbody></table></div><div className="flex items-center gap-2 border-t border-slate-100 px-5 py-4 text-xs text-slate-500"><span className="h-2 w-2 rounded-full bg-blue-600" />{checked.length} permission dipilih untuk {selectedRole?.name ?? 'role'}</div></div></AppLayout>;
}
