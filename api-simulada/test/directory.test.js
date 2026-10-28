import test from 'node:test';
import assert from 'node:assert/strict';
import { cleanText, loadDirectory, normalizeRut, sanitizeUser } from '../src/directory.js';

test('normaliza RUT con puntos, guion y minúscula', () => {
    assert.equal(normalizeRut('20.404.100-8'), '20404100-8');
    assert.equal(normalizeRut('20404100k'), '20404100-K');
});

test('limpia espacios y caracteres de reemplazo del texto institucional', () => {
    assert.equal(cleanText('  CESFAM   Norte\uFFFD  '), 'CESFAM Norte');
});

test('publica usuarios depurados sin credenciales ni datos laborales ajenos al boceto', async () => {
    const directory = await loadDirectory();

    assert.equal(directory.users.length, 5);
    assert.equal(directory.users[0].name, 'ISABEL ANDREA RIOS EJEMPLO');
    assert.equal(directory.users[0].job_title, 'PREVENCIONISTA DE RIESGOS');
    assert.equal(Object.hasOwn(directory.users[0], 'email'), false);
    assert.equal(Object.hasOwn(directory.users[0], 'password'), false);
    assert.equal(Object.hasOwn(directory.users[0], 'role_code'), false);
    assert.equal(Object.hasOwn(directory.users[0], 'role_name'), false);
    assert.equal(directory.dependencies.filter((dependency) => dependency.tipo === 'establecimiento').length, 51);
    assert.equal(directory.dependencies.filter((dependency) => dependency.tipo === 'unidad').length, 153);
    assert.equal(directory.dependencies.find((dependency) => dependency.id === 6021).denominacion, 'DIRECCION COMUNAL DE SALUD');
});

test('no expone usuarios cuya dependencia no está en el catálogo recibido', () => {
    const user = sanitizeUser({ id: 1, rut: '20404100-8', nombres: 'Persona Ejemplo', cargo: 'Prevencionista', dependency_id: 999 }, []);

    assert.equal(user, null);
});
