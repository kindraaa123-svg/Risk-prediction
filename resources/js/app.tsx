import { createInertiaApp, router } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

let websiteName = document.title;

createInertiaApp({
    title: (title) => (title ? `${title} · ${websiteName}` : websiteName),
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx', { eager: true });
        return pages[`./pages/${name}.tsx`] as { default: React.ComponentType };
    },
    setup({ el, App, props }) {
        const applyFavicon = (url?: string | null) => {
            if (!url) return;
            let link = document.querySelector<HTMLLinkElement>('link[rel="icon"]');
            if (!link) { link = document.createElement('link'); link.rel = 'icon'; document.head.appendChild(link); }
            link.href = `${url}${url.includes('?') ? '&' : '?'}v=${Date.now()}`;
        };
        const initialSettings = (props as { systemSettings?: { websiteName?: string; faviconUrl?: string | null } }).systemSettings;
        websiteName = initialSettings?.websiteName ?? websiteName;
        applyFavicon(initialSettings?.faviconUrl);
        router.on('navigate', (event) => {
            const settings = (event.detail.page.props as { systemSettings?: { websiteName?: string; faviconUrl?: string | null } }).systemSettings;
            websiteName = settings?.websiteName ?? websiteName;
            applyFavicon(settings?.faviconUrl);
        });
        if (el) {
            createRoot(el).render(<App {...props} />);
        }
    },
});
