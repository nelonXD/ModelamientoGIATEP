<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class LmStudioModelCatalog
{
    /** @return array{connected: bool, models: list<array{id: string, name: string}>} */
    public function list(): array
    {
        $client = Http::baseUrl(rtrim((string) config('services.lm_studio.url'), '/'))
            ->acceptJson()
            ->timeout(5);
        $token = (string) config('services.lm_studio.token');

        if ($token !== '') {
            $client = $client->withToken($token);
        }

        try {
            $models = collect($client->get('/models')->throw()->json('data', []))
                ->filter(fn (mixed $model): bool => is_array($model) && filled($model['id'] ?? null))
                ->map(fn (array $model): array => [
                    'id' => (string) $model['id'],
                    'name' => (string) ($model['id']),
                ])
                ->unique('id')
                ->values()
                ->all();

            return ['connected' => true, 'models' => $models];
        } catch (ConnectionException|RequestException) {
            return ['connected' => false, 'models' => []];
        }
    }
}
