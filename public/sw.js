/**
 * PedeLivre OS — Service Worker Oficial do PWA do Lojista
 * Suporte a instalação nativa (Android/iOS/Desktop), cache inteligente,
 * notificações operacionais em segundo plano e reconexão resiliente.
 */

const CACHE_VERSION = 'pedelivre-os-v2.4.0';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

const PRECACHE_URLS = [
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/icons/apple-touch-icon.png',
    '/icons/badge-96.png'
];

// 1. Instalação e Pré-cache de ativos essenciais
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(PRECACHE_URLS).catch(() => {});
        })
    );
});

// 2. Ativação e limpeza de caches antigos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== STATIC_CACHE && key !== RUNTIME_CACHE)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// 3. Interceptação de Requisições
self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Ignorar chamadas de API em tempo real para sempre buscar dados frescos do servidor
    if (url.pathname.startsWith('/api/')) {
        return;
    }

    // Navegação HTML (/painel, /login, etc.): Network-First com fallback para cache ou tela offline elegante
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(request);
                    if (cached) return cached;
                    return new Response(
                        `<!DOCTYPE html>
                        <html lang="pt-BR" class="h-full bg-[#060911] text-white">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <title>Sem conexão — PedeLivre OS</title>
                            <style>
                                body { margin: 0; font-family: system-ui, -apple-system, sans-serif; background: #060911; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; text-align: center; }
                                .card { max-width: 420px; background: #0d1322; border: 1px solid #1e293b; border-radius: 24px; padding: 32px 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6); }
                                .icon { width: 64px; height: 64px; border-radius: 18px; background: rgba(244,63,94,0.15); border: 1px solid rgba(244,63,94,0.3); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px; }
                                h1 { font-size: 20px; font-weight: 800; margin: 0 0 8px; }
                                p { font-size: 13px; color: #94a3b8; line-height: 1.6; margin: 0 0 24px; }
                                button { background: #f43f5e; color: #fff; border: none; border-radius: 14px; padding: 12px 24px; font-weight: 800; font-size: 13px; cursor: pointer; width: 100%; }
                            </style>
                        </head>
                        <body>
                            <div class="card">
                                <div class="icon">📡</div>
                                <h1>Conexão temporariamente indisponível</h1>
                                <p>O PedeLivre OS está aguardando o retorno da sua internet para sincronizar os pedidos em tempo real.</p>
                                <button onclick="window.location.reload()">Tentar reconectar agora</button>
                            </div>
                        </body>
                        </html>`,
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                })
        );
        return;
    }

    // Ativos estáticos (ícones, imagens, fontes, scripts CDN): Stale-While-Revalidate
    if (
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/assets/') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdn.jsdelivr.net')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request)
                    .then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            const copy = networkResponse.clone();
                            caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
                        }
                        return networkResponse;
                    })
                    .catch(() => cachedResponse);
                return cachedResponse || fetchPromise;
            })
        );
    }
});

// 4. Disparo de Notificações Nativas do Sistema Operacional (via postMessage ou Push)
self.addEventListener('message', (event) => {
    const data = event.data || {};

    if (data.type === 'SKIP_WAITING') {
        self.skipWaiting();
        return;
    }

    if (data.type === 'SHOW_NOTIFICATION') {
        const title = data.title || 'Novo Alerta — PedeLivre OS';
        const options = {
            body: data.body || 'Você tem uma nova atualização no painel da loja.',
            icon: '/icons/icon-192.png',
            badge: '/icons/badge-96.png',
            tag: data.tag || 'pedelivre-alerta',
            renotify: true,
            requireInteraction: Boolean(data.requireInteraction ?? true),
            vibrate: [350, 120, 350, 120, 450],
            data: {
                url: data.url || '/painel',
                modulo: data.modulo || 'pedidos',
                pedidoId: data.pedidoId || null
            },
            actions: data.actions || [
                { action: 'open', title: '⚡ Abrir Painel' }
            ]
        };

        event.waitUntil(self.registration.showNotification(title, options));
    }
});

// 5. Suporte a Web Push Nativo
self.addEventListener('push', (event) => {
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        payload = { title: 'PedeLivre OS', body: event.data ? event.data.text() : 'Novo pedido recebido!' };
    }

    const title = payload.title || '🔔 Novo Pedido no PedeLivre!';
    const options = {
        body: payload.body || 'Toque para visualizar e aceitar o pedido imediatamente.',
        icon: '/icons/icon-192.png',
        badge: '/icons/badge-96.png',
        tag: payload.tag || 'pedelivre-push',
        renotify: true,
        requireInteraction: true,
        vibrate: [400, 150, 400, 150, 500],
        data: {
            url: payload.url || '/painel',
            modulo: payload.modulo || 'pedidos'
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// 6. Ação ao tocar na Notificação do Celular / Desktop
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetData = event.notification.data || {};
    const targetUrl = targetData.url || '/painel';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes('/painel') && 'focus' in client) {
                    client.postMessage({
                        type: 'NOTIFICATION_CLICKED',
                        modulo: targetData.modulo || 'pedidos',
                        pedidoId: targetData.pedidoId || null
                    });
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }
        })
    );
});
