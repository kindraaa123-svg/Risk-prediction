import { X } from 'lucide-react';
import { PropsWithChildren } from 'react';

export function Modal({ title, children, onClose, wide = false }: PropsWithChildren<{ title: string; onClose: () => void; wide?: boolean }>) {
    return <div className="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/40 p-4" role="dialog" aria-modal="true"><div className={`w-full ${wide ? 'max-w-2xl' : 'max-w-md'} max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl`}><div className="flex items-center justify-between border-b border-slate-200 px-6 py-4"><h2 className="font-semibold text-slate-900">{title}</h2><button type="button" onClick={onClose} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup modal"><X size={18} /></button></div>{children}</div></div>;
}

export function ConfirmModal({ title, message, confirmLabel = 'Konfirmasi', onConfirm, onClose, danger = false }: { title: string; message: string; confirmLabel?: string; onConfirm: () => void; onClose: () => void; danger?: boolean }) {
    return <Modal title={title} onClose={onClose}><div className="p-6"><p className="text-sm leading-6 text-slate-600">{message}</p><div className="mt-6 flex justify-end gap-3"><button type="button" onClick={onClose} className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button><button type="button" onClick={onConfirm} className={`rounded-lg px-4 py-2.5 text-sm font-semibold text-white ${danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'}`}>{confirmLabel}</button></div></div></Modal>;
}
