<?php

namespace App\Services;

use App\Support\Rut;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class InstitutionDirectoryClient
{
    /** @return array<string, int|string>|null */
    public function findEligibleUser(string $rut): ?array
    {
        $baseUrl = rtrim((string) config('services.institution_directory.url'), '/');

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.institution_directory.timeout', 3))
                ->get($baseUrl.'/users/'.rawurlencode(Rut::normalize($rut)));
        } catch (ConnectionException $exception) {
            throw new RuntimeException('El directorio institucional simulado no está disponible.', previous: $exception);
        }

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException('El directorio institucional respondió con un error.');
        }

        $source = $response->json('data');
        if (! is_array($source) || Rut::normalize((string) ($source['rut'] ?? '')) !== Rut::normalize($rut)) {
            return null;
        }

        $dependencyId = filter_var($source['dependency_id'] ?? null, FILTER_VALIDATE_INT);
        if (! $dependencyId) {
            return null;
        }

        try {
            $dependencyResponse = Http::acceptJson()
                ->timeout((int) config('services.institution_directory.timeout', 3))
                ->get($baseUrl.'/dependencies/'.$dependencyId);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No se pudieron consultar las dependencias institucionales.', previous: $exception);
        }

        if (! $dependencyResponse->successful()) {
            throw new RuntimeException('No se pudieron consultar las dependencias institucionales.');
        }

        $dependency = $dependencyResponse->json('data');
        $name = $this->cleanText($source['name'] ?? null);
        $jobTitle = $this->cleanText($source['job_title'] ?? null);
        $dependencyName = is_array($dependency) ? $this->cleanText($dependency['denominacion'] ?? null) : '';
        $role = $this->roleFromJobTitle($jobTitle);

        if (! $name || ! $jobTitle || ! $dependencyName || ! $role) {
            return null;
        }

        return [
            'external_id' => $this->cleanText($source['external_id'] ?? ''),
            'rut' => Rut::normalize($rut),
            'name' => $name,
            'job_title' => $jobTitle,
            'role_code' => $role['code'],
            'role_name' => $role['name'],
            'dependency_id' => (int) $dependencyId,
            'dependency_name' => $dependencyName,
            'workplace' => $this->cleanText($source['workplace'] ?? ''),
            'establishment' => $this->cleanText($source['establishment'] ?? ''),
        ];
    }

    private function cleanText(mixed $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
    }

    /** @return array{code: string, name: string}|null */
    private function roleFromJobTitle(string $jobTitle): ?array
    {
        $title = mb_strtoupper($jobTitle, 'UTF-8');

        if (preg_match('/PREVEN|SALUD OCUPACIONAL/u', $title)) {
            return ['code' => 'prevencion', 'name' => 'Prevencionista'];
        }

        if (preg_match('/DIRECTOR(A)?\s+DE\s+SALUD|ALCALD/u', $title)) {
            return ['code' => 'alta-direccion', 'name' => 'Alta Dirección'];
        }

        if (preg_match('/JEFE|JEFATURA|SUPERVISOR/u', $title)) {
            return ['code' => 'jefatura', 'name' => 'Jefatura directa'];
        }

        return null;
    }
}
