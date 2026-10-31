import { Head } from '@inertiajs/react';
import { Bell, Inbox } from 'lucide-react';
import AppLayout from '../../layouts/AppLayout';
import { SharedProps } from '../../types';

type Props = SharedProps & { notifications: { id: string; read_at: string | null; created_at: string | null; data: Record<string, string> }[] };

export default function Index({ notifications }: Props) {
    return <AppLayout><Head title="Notifikasi" /><div className="mb-7"><p className="mb-2 text-sm font-medium text-blue-600">Ruang kerja</p><h1 className="text-2xl font-semibold tracking-tight text-slate-900">Notifikasi</h1><p className="mt-1 text-sm text-slate-500">Pemberitahuan aktivitas dan pembaruan yang relevan untuk Anda.</p></div><div className="rounded-2xl border border-slate-200 bg-white shadow-sm">{notifications.length ? notifications.map((notification) => <div key={notification.id} className="flex gap-3 border-b border-slate-100 p-5"><Bell className="text-blue-600" size={18} /><div><p className="text-sm text-slate-700">{notification.data.message ?? 'Ada notifikasi baru.'}</p><p className="mt-1 text-xs text-slate-400">{notification.created_at ?? '—'}</p></div></div>) : <div className="p-14 text-center"><Inbox className="mx-auto text-slate-300" size={36} /><h2 className="mt-4 font-medium text-slate-800">Belum ada notifikasi</h2><p className="mt-1 text-sm text-slate-500">Notifikasi akan muncul ketika ada aktivitas yang perlu Anda ketahui.</p></div>}</div></AppLayout>;
}
