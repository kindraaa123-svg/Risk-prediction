import { Head } from '@inertiajs/react';
import { Construction, FolderKanban } from 'lucide-react';
import AppLayout from '../../layouts/AppLayout';
import { SharedProps } from '../../types';

type Props = SharedProps & { module: { title: string; eyebrow: string; description: string } };

export default function Index({ module }: Props) {
    return <AppLayout><Head title={module.title} /><div className="mb-7"><p className="mb-2 text-sm font-medium text-blue-600">{module.eyebrow}</p><h1 className="text-2xl font-semibold tracking-tight text-slate-900">{module.title}</h1><p className="mt-1 text-sm text-slate-500">{module.description}</p></div><div className="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center shadow-sm"><div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><FolderKanban size={26} /></div><h2 className="mt-5 text-lg font-semibold text-slate-800">Modul siap digunakan</h2><p className="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">Belum ada data operasional untuk ditampilkan di halaman ini. Setelah workflow modul ini diaktifkan, data nyata akan muncul di sini—tidak ada metrik simulasi.</p><div className="mt-5 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs text-slate-500"><Construction size={14} /> Data source belum tersedia</div></div></AppLayout>;
}
