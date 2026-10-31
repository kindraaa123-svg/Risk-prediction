import { Head } from '@inertiajs/react';
import { Activity, FolderKanban, ShieldAlert, Users } from 'lucide-react';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
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
                <MetricCard label="Proyek" value={metrics.projects} icon={<FolderKanban size={19} />} note={metrics.projects ? 'Terdaftar di portofolio' : 'Belum ada proyek'} />
                <MetricCard label="Proyek berisiko tinggi" value={metrics.highRiskProjects} icon={<ShieldAlert size={19} />} note={metrics.highRiskProjects ? 'Perlu perhatian tim' : 'Tidak ada risiko tinggi'} />
                <MetricCard label="Aktivitas terbaru" value={metrics.activities} icon={<Activity size={19} />} note={metrics.activities ? 'Aktivitas tercatat' : 'Belum ada aktivitas tercatat'} />
            </div>
            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <ChartCard title="Status proyek" description="Distribusi status seluruh proyek di portofolio." data={metrics.projectStatus} />
                <ChartCard title="Status task" description="Distribusi pekerjaan berdasarkan status terkini." data={metrics.taskStatus} />
            </div>
            <section className="mt-6 rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center">
                <h2 className="font-medium text-zinc-900">{metrics.projects ? 'Data operasional tersedia' : 'Ruang kerja siap digunakan'}</h2>
                <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-zinc-500">{metrics.projects ? 'Ringkasan ini diperbarui langsung dari data proyek, risiko, dan aktivitas di database.' : 'Belum ada data proyek di database. Tambahkan proyek untuk mulai mengisi metrik operasional.'}</p>
            </section>
        </AppLayout>
    );
}

function MetricCard({ label, value, icon, note }: { label: string; value: string | number; icon: React.ReactNode; note?: string }) {
    return <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div className="mb-4 flex items-center justify-between text-slate-500"><span className="text-sm">{label}</span><span className="rounded-lg bg-blue-50 p-2 text-blue-600">{icon}</span></div><div className="text-2xl font-semibold tabular-nums text-slate-900">{value}</div>{note && <p className="mt-1 text-xs text-slate-400">{note}</p>}</div>;
}

function ChartCard({ title, description, data }: { title: string; description: string; data: { status: string; total: number }[] }) {
    const chartData = data.map((item) => ({ ...item, label: formatStatus(item.status) }));

    return <section className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 className="font-semibold text-slate-900">{title}</h2><p className="mt-1 text-sm text-slate-500">{description}</p>{chartData.length ? <div className="mt-5 h-64"><ResponsiveContainer width="100%" height="100%"><BarChart data={chartData} margin={{ top: 8, right: 8, left: -20, bottom: 8 }}><CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" /><XAxis dataKey="label" axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12 }} /><YAxis allowDecimals={false} axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12 }} /><Tooltip cursor={{ fill: '#eff6ff' }} contentStyle={{ borderRadius: 10, borderColor: '#e2e8f0', fontSize: 12 }} formatter={(value) => [value, 'Jumlah']} /><Bar dataKey="total" fill="#2563eb" radius={[6, 6, 0, 0]} maxBarSize={48} /></BarChart></ResponsiveContainer></div> : <div className="flex h-64 items-center justify-center text-sm text-slate-400">Belum ada data untuk ditampilkan.</div>}</section>;
}

function formatStatus(status: string): string {
    return ({ active: 'Aktif', planning: 'Perencanaan', completed: 'Selesai', todo: 'To do', in_progress: 'Berjalan', review: 'Review' } as Record<string, string>)[status] ?? status.replaceAll('_', ' ');
}
