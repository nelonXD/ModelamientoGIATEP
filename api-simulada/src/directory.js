import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const sourceDirectory = resolve(dirname(fileURLToPath(import.meta.url)), '../data');

export function normalizeRut(value) {
    const compact = String(value ?? '').toUpperCase().replace(/[^0-9K]/g, '');

    return compact.length < 2 ? compact : `${compact.slice(0, -1)}-${compact.slice(-1)}`;
}

export function cleanText(value) {
    return String(value ?? '').normalize('NFC').replace(/[\uFFFD]/g, '').replace(/\s+/g, ' ').trim();
}

export function sanitizeUser(user, dependencies) {
    const dependencyId = Number(user.dependency_id);
    const dependency = dependencies.find((item) => Number(item.id) === dependencyId);
    const name = cleanText(user.nombre_completo)
        || cleanText([user.nombres, user.primer_apellido, user.segundo_apellido].filter(Boolean).join(' '));

    if (!name || !dependency) {
        return null;
    }

    return {
        external_id: cleanText(user.id),
        rut: normalizeRut(user.rut),
        name,
        job_title: cleanText(user.cargo || user.titulo_oficio),
        dependency_id: dependency.id,
        dependency_name: cleanText(dependency.denominacion),
        workplace: cleanText(user.lugar_desempeno),
        establishment: cleanText(user.establecimiento_principal),
    };
}

export async function loadDirectory() {
    const [users, dependencies] = await Promise.all([
        readFile(resolve(sourceDirectory, 'users.json'), 'utf8').then(JSON.parse),
        readFile(resolve(sourceDirectory, 'dependencies.json'), 'utf8').then(JSON.parse),
    ]);
    const cleanDependencies = dependencies
        .map((dependency) => ({
            id: Number(dependency.id),
            tipo: cleanText(dependency.tipo) || 'unidad',
            ...(dependency.establecimiento_id ? { establecimiento_id: Number(dependency.establecimiento_id) } : {}),
            denominacion: cleanText(dependency.denominacion),
        }))
        .filter((dependency) => Number.isInteger(dependency.id) && dependency.denominacion);
    const cleanUsers = users.map((user) => sanitizeUser(user, cleanDependencies)).filter(Boolean);

    return { users: cleanUsers, dependencies: cleanDependencies };
}
