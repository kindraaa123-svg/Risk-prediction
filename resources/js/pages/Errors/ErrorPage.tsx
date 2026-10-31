import { Head, Link } from '@inertiajs/react';
import { AlertCircle, ArrowLeft, Home, RefreshCw, ShieldAlert } from 'lucide-react';

const messages: Record<number, { title: string; description: string; action: string }> = {
    400: { title: 'Permintaan tidak valid', description: 'Permintaan tidak dapat diproses. Periksa informasi Anda lalu coba lagi.', action: 'Kembali' },
    401: { title: 'Autentikasi diperlukan', description: 'Silakan masuk untuk mengakses halaman ini.', action: 'Masuk' },
    403: { title: 'Akses ditolak', description: 'Anda tidak memiliki izin untuk mengakses halaman ini.', action: 'Kembali ke dasbor' },
    404: { title: 'Halaman tidak ditemukan', description: 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.', action: 'Kembali ke dasbor' },
    419: { title: 'Sesi Anda telah berakhir', description: 'Muat ulang halaman lalu coba lagi.', action: 'Muat ulang halaman' },
    429: { title: 'Terlalu banyak permintaan', description: 'Terlalu banyak permintaan telah dibuat. Silakan coba lagi nanti.', action: 'Kembali' },
    500: { title: 'Terjadi kesalahan', description: 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti.', action: 'Kembali ke dasbor' },
    503: { title: 'Layanan sementara tidak tersedia', description: 'Layanan sedang tidak tersedia. Silakan coba lagi beberapa saat lagi.', action: 'Coba lagi' },
};

export default function ErrorPage({ status }: { status: number }) {
    const message = messages[status] ?? messages[500];
    const isAuth = status === 401;
    const isRetry = [419, 503].includes(status);

    return <main className="flex min-h-screen items-center justify-center bg-slate-50 px-5 py-12"><Head title={`${status} · ${message.title}`} /><div className="w-full max-w-lg text-center"><div className="mx-auto mb-7 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">{status === 403 ? <ShieldAlert size={30} /> : <AlertCircle size={30} />}</div><p className="text-7xl font-semibold tracking-tight text-blue-600">{status}</p><h1 className="mt-5 text-2xl font-semibold tracking-tight text-slate-900">{message.title}</h1><p className="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-500">{message.description}</p><div className="mt-8 flex flex-wrap justify-center gap-3">{isAuth ? <Link href="/login" className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Masuk</Link> : isRetry ? <button type="button" onClick={() => window.location.reload()} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"><RefreshCw size={16} />{message.action}</button> : <Link href={status === 403 || status === 404 || status >= 500 ? '/dashboard' : '#'} onClick={(event) => { if (status < 500 && status !== 403 && status !== 404) { event.preventDefault(); window.history.back(); } }} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"><Home size={16} />{message.action}</Link>}{status !== 401 && <button type="button" onClick={() => window.history.back()} className="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700"><ArrowLeft size={16} />Kembali</button>}</div><p className="mt-12 text-xs text-slate-400">Operasional Proyek Kindra</p></div></main>;
}
