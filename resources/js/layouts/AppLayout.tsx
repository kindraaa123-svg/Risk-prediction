import { Link, usePage } from '@inertiajs/react';
import { Activity, Bell, ChevronRight, ClipboardList, DatabaseBackup, FileBarChart, FolderKanban, KeyRound, LayoutDashboard, LogOut, Menu, Settings, ShieldAlert, ShieldCheck, SlidersHorizontal, Users } from 'lucide-react';
import { PropsWithChildren, useState } from 'react';
import { SharedProps } from '../types';

export default function AppLayout({ children }: PropsWithChildren) {
    const { app, auth, systemSettings } = usePage<SharedProps>().props;
    const can = (permission: string) => auth.roles?.includes('Superadmin') || auth.permissions?.includes(permission);
    const [open, setOpen] = useState(false);

    return (
        <div className="min-h-screen bg-zinc-50">
            <aside className={`fixed inset-y-0 left-0 z-40 w-64 border-r border-zinc-200 bg-white transition-transform lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="flex h-16 items-center gap-3 border-b border-zinc-100 px-6">
                    {systemSettings.logoUrl ? <img src={systemSettings.logoUrl} alt={systemSettings.websiteName} className="h-8 w-8 rounded-lg object-contain" /> : <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">{systemSettings.websiteShortName.slice(0, 1)}</div>}
                    <span className="font-semibold tracking-tight">{systemSettings.websiteName}</span>
                </div>
                <nav className="space-y-1 p-4">
                    <NavItem href="/dashboard" icon={<LayoutDashboard size={18} />} label="Dasbor" />
                    <NavGroup label="Ruang kerja" />
                    {can('projects.view') && <NavItem href="/projects" icon={<FolderKanban size={18} />} label="Proyek" />}
                    {can('tasks.view') && <NavItem href="/tasks" icon={<ClipboardList size={18} />} label="Tugas" />}
                    {can('risk-predictions.view') && <NavItem href="/risk-prediction" icon={<ShieldAlert size={18} />} label="Prediksi risiko" />}
                    {can('risk-mitigations.manage') && <NavItem href="/risk-mitigation" icon={<ShieldCheck size={18} />} label="Mitigasi risiko" />}
                    {can('reports.view') && <NavItem href="/reports" icon={<FileBarChart size={18} />} label="Laporan" />}
                    <NavGroup label="Akun" />
                    <NavItem href="/notifications" icon={<Bell size={18} />} label="Notifikasi" />
                    <NavItem href="/profile" icon={<Settings size={18} />} label="Profil" />
                    {auth.roles?.includes('Superadmin') && <NavGroup label="Administrasi" />}
                    {can('users.view') && <NavItem href="/admin/users" icon={<Users size={18} />} label="Manajemen pengguna" />}
                    {can('roles.manage') && <NavItem href="/admin/access-control" icon={<KeyRound size={18} />} label="Kontrol akses" />}
                    {can('activity-logs.view') && <NavItem href="/admin/activity-logs" icon={<Activity size={18} />} label="Log aktivitas" />}
                    {can('backups.view') && <NavItem href="/admin/database-backups" icon={<DatabaseBackup size={18} />} label="Cadangan basis data" />}
                    {can('system-settings.view') && <NavItem href="/admin/system-settings" icon={<SlidersHorizontal size={18} />} label="Pengaturan sistem" />}
                </nav>
            </aside>

            <div className="lg:pl-64">
                <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-zinc-200 bg-white/95 px-4 backdrop-blur lg:px-8">
                    <button className="rounded-md p-2 text-zinc-600 hover:bg-zinc-100 lg:hidden" onClick={() => setOpen(!open)} aria-label="Toggle navigation">
                        <Menu size={20} />
                    </button>
                    <div className="ml-auto flex items-center gap-4 text-sm">
                        <span className="hidden text-zinc-500 sm:inline">{auth.user?.name}</span>
                        <Link href="/logout" method="post" as="button" className="rounded-md p-2 text-zinc-600 hover:bg-zinc-100" aria-label="Keluar">
                            <LogOut size={18} />
                        </Link>
                    </div>
                </header>
                <main className="p-4 lg:p-8">{children}</main>
            </div>
        </div>
    );
}

function NavItem({ href, icon, label }: { href: string; icon: React.ReactNode; label: string }) {
    return <Link href={href} className="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">{icon}<span>{label}</span><ChevronRight size={14} className="ml-auto opacity-0 transition group-hover:opacity-100" /></Link>;
}

function NavGroup({ label }: { label: string }) {
    return <p className="px-3 pb-1 pt-5 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">{label}</p>;
}
