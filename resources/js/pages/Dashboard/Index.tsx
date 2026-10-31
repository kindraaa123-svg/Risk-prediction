import { Head } from '@inertiajs/react';
import { Activity, FolderKanban, ShieldAlert, Users } from 'lucide-react';
import AppLayout from '../../layouts/AppLayout';
import { DashboardProps } from '../../types';

export default function Index({ metrics }: DashboardProps) {
    return (
        <AppLayout>
            <Head title="Dasbor" />
            <div className="mb-8">
                <p className="mb-2 text-sm font-medium text-blue-600">Ringkasan</p>
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">Operasional proyek</h1>
                <p className="mt-1 text-sm text-slate-500">Ringkasan portofolio proyek dan kondisi operasional Anda.</p>
            </div>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <MetricCard label="Pengguna" value={metrics.users} icon={<Users size={19} />} />
                <MetricCard label="Proyek" value="—" icon={<FolderKanban size={19} />} note="Modul proyek belum dikonfigurasi" />
                <MetricCard label="Proyek berisiko tinggi" value="—" icon={<ShieldAlert size={19} />} note="Model risiko belum dilatih" />
                <MetricCard label="Aktivitas terbaru" value="—" icon={<Activity size={19} />} note="Aktivitas akan tampil di sini" />
            </div>
            <section className="mt-6 rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center">
                <h2 className="font-medium text-zinc-900">Ruang kerja siap digunakan</h2>
                <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-zinc-500">Metrik akan terisi dari basis data operasional setelah data proyek dan risiko tersedia.</p>
            </section>
        </AppLayout>
    );
}

function MetricCard({ label, value, icon, note }: { label: string; value: string | number; icon: React.ReactNode; note?: string }) {
    return <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div className="mb-4 flex items-center justify-between text-slate-500"><span className="text-sm">{label}</span><span className="rounded-lg bg-blue-50 p-2 text-blue-600">{icon}</span></div><div className="text-2xl font-semibold tabular-nums text-slate-900">{value}</div>{note && <p className="mt-1 text-xs text-slate-400">{note}</p>}</div>;
}
