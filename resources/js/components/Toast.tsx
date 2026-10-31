import { AlertCircle, CheckCircle2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export function Toast({ message, onClose, type }: { message: string; onClose: () => void; type?: 'success' | 'error' }) {
    const isError = type === 'error' || /gagal|failed|error|tidak dapat/i.test(message);
    const [progress, setProgress] = useState(100);

    useEffect(() => {
        const startedAt = Date.now();
        const interval = window.setInterval(() => {
            const elapsed = Date.now() - startedAt;
            setProgress(Math.max(0, 100 - (elapsed / 3000) * 100));
        }, 30);
        const timeout = window.setTimeout(onClose, 3000);
        return () => { window.clearInterval(interval); window.clearTimeout(timeout); };
    }, [onClose]);

    return <div className={`fixed right-4 top-4 z-[80] w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-xl border bg-white shadow-xl ${isError ? 'border-red-200' : 'border-emerald-200'}`}><div className="flex items-start gap-3 p-4">{isError ? <AlertCircle className="mt-0.5 shrink-0 text-red-600" size={19} /> : <CheckCircle2 className="mt-0.5 shrink-0 text-emerald-600" size={19} />}<p className="flex-1 text-sm font-medium text-slate-700">{message}</p><button type="button" onClick={onClose} className="text-slate-400 hover:text-slate-700" aria-label="Tutup notifikasi"><X size={17} /></button></div><div className={`h-1 ${isError ? 'bg-red-100' : 'bg-emerald-100'}`}><div className={`h-full transition-[width] duration-75 ${isError ? 'bg-red-500' : 'bg-emerald-500'}`} style={{ width: `${progress}%` }} /></div></div>;
}
