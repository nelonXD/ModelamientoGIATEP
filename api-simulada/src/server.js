import { createServer } from 'node:http';
import { loadDirectory, normalizeRut } from './directory.js';

const port = Number(process.env.PORT || 8091);

function sendJson(response, status, body) {
    response.writeHead(status, {
        'Content-Type': 'application/json; charset=utf-8',
        'Cache-Control': 'no-store',
        'X-Content-Type-Options': 'nosniff',
    });
    response.end(JSON.stringify(body));
}

const server = createServer(async (request, response) => {
    const url = new URL(request.url || '/', `http://${request.headers.host || '127.0.0.1'}`);

    if (request.method !== 'GET') {
        sendJson(response, 405, { message: 'Método no permitido.' });
        return;
    }

    try {
        if (url.pathname === '/api/v1/health') {
            sendJson(response, 200, { status: 'ok', service: 'GIATEP Directorio Institucional Simulado' });
            return;
        }

        const directory = await loadDirectory();
        if (url.pathname === '/api/v1/users') {
            const rut = url.searchParams.get('rut');
            const users = directory.users.filter((user) => ! rut || user.rut === normalizeRut(rut));
            sendJson(response, 200, { data: users, meta: { total: users.length, source: 'simulated-directory' } });
            return;
        }

        const userMatch = url.pathname.match(/^\/api\/v1\/users\/([^/]+)$/);
        if (userMatch) {
            const rut = normalizeRut(decodeURIComponent(userMatch[1]));
            const user = directory.users.find((item) => item.rut === rut);
            if (! user) {
                sendJson(response, 404, { message: 'No se encontró un usuario institucional habilitado.' });
                return;
            }

            sendJson(response, 200, { data: user });
            return;
        }

        if (url.pathname === '/api/v1/dependencies') {
            sendJson(response, 200, { data: directory.dependencies, meta: { total: directory.dependencies.length } });
            return;
        }

        const dependencyMatch = url.pathname.match(/^\/api\/v1\/dependencies\/(\d+)$/);
        if (dependencyMatch) {
            const dependency = directory.dependencies.find((item) => item.id === Number(dependencyMatch[1]));
            if (! dependency) {
                sendJson(response, 404, { message: 'No se encontró la dependencia.' });
                return;
            }

            sendJson(response, 200, { data: dependency });
            return;
        }

        sendJson(response, 404, { message: 'Ruta API no encontrada.' });
    } catch {
        sendJson(response, 500, { message: 'No fue posible leer el directorio simulado.' });
    }
});

server.listen(port, '127.0.0.1', () => {
    process.stdout.write(`GIATEP API simulada disponible en http://127.0.0.1:${port}/api/v1\n`);
});
