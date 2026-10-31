import { Head, useForm } from '@inertiajs/react';
import { Mail, Phone, Save, UserRound } from 'lucide-react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../layouts/AppLayout';
import { Toast } from '../../components/Toast';
import { SharedProps } from '../../types';

type Props = SharedProps & { user: { name: string; email: string; phone: string | null; status: string; created_at: string | null } };

export default function Index({ user }: Props) {
    const [toast, setToast] = useState<string | null>(null);
    const form = useForm({ name: user.name, email: user.email, phone: user.phone ?? '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put('/profile', { preserveScroll: true, onSuccess: () => setToast('Profile berhasil diperbarui.'), onError: () => setToast('Profile gagal diperbarui.') });
    };

    return <AppLayout><Head title="Profile" />{toast && <Toast message={toast} onClose={() => setToast(null)} />}<div className="mb-7"><p className="mb-2 text-sm font-medium text-blue-600">Account</p><h1 className="text-2xl font-semibold tracking-tight text-slate-900">Profile</h1><p className="mt-1 text-sm text-slate-500">Perbarui informasi akun Anda secara langsung.</p></div><form onSubmit={submit} className="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div className="mb-6 flex items-center gap-4 border-b border-slate-100 pb-6"><div className="flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-xl font-semibold text-blue-700">{form.data.name.slice(0, 1).toUpperCase() || <UserRound size={22} />}</div><div><h2 className="font-semibold text-slate-900">Informasi profile</h2><p className="text-sm text-slate-500">Status akun: {user.status === 'active' ? 'Active' : 'Inactive'} · Bergabung {user.created_at ?? '—'}</p></div></div><div className="grid gap-5 md:grid-cols-2"><Field label="Nama" value={form.data.name} error={form.errors.name} onChange={(value) => form.setData('name', value)} /><Field label="Email" type="email" value={form.data.email} error={form.errors.email} onChange={(value) => form.setData('email', value)} /><Field label="Nomor telepon" value={form.data.phone} error={form.errors.phone} onChange={(value) => form.setData('phone', value)} /></div><div className="mt-6 flex justify-end border-t border-slate-100 pt-5"><button type="submit" disabled={form.processing} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"><Save size={17} />{form.processing ? 'Menyimpan...' : 'Save changes'}</button></div></form></AppLayout>;
}

function Field({ label, value, onChange, error, type = 'text' }: { label: string; value: string; onChange: (value: string) => void; error?: string; type?: string }) {
    return <label className="block text-sm font-medium text-slate-700">{label}<div className="relative mt-2"><input type={type} value={value} onChange={(event) => onChange(event.target.value)} className="w-full rounded-lg border border-slate-300 px-3 py-2.5 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100" />{type === 'email' && <Mail className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" size={17} />}{label === 'Nomor telepon' && <Phone className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" size={17} />}</div>{error && <span className="mt-1 block text-xs text-red-600">{error}</span>}</label>;
}
