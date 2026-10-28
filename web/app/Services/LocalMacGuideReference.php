<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;

class LocalMacGuideReference
{
    /** @return array{title: string, version: string, source: string, pages: list<int>, excerpts: list<array{page: int, section: string, text: string}>} */
    public function retrieve(string $story): array
    {
        $path = database_path('data/referencias/guia_mac_isp_v1.json');
        if (! is_file($path)) {
            throw new RuntimeException('No se encuentra la extracción local de la Guía MAC del ISP.');
        }

        $document = json_decode((string) file_get_contents($path), true);
        if (! is_array($document) || ! is_array($document['pages'] ?? null)) {
            throw new RuntimeException('La extracción local de la Guía MAC no tiene un formato válido.');
        }

        $corePages = [7, 9, 10, 11, 21, 22, 23, 28];
        $terms = collect(preg_split('/[^\pL\pN]+/u', Str::lower($story)) ?: [])
            ->filter(fn (string $term): bool => mb_strlen($term) >= 5)->unique()->values();
        $ranked = collect($document['pages'])->map(function (array $page) use ($terms, $corePages): array {
            $text = Str::lower((string) ($page['text'] ?? ''));
            $matches = $terms->sum(fn (string $term): int => substr_count($text, $term));
            $page['score'] = ($matches * 2) + (in_array((int) $page['page'], $corePages, true) ? 1 : 0);

            return $page;
        });

        $selected = $ranked->whereIn('page', $corePages)
            ->concat($ranked->whereNotIn('page', $corePages)->sortByDesc('score')->filter(fn (array $page): bool => $page['score'] > 0)->take(2))
            ->unique('page')->sortBy('page')->values();
        $excerpts = $selected->map(fn (array $page): array => [
            'page' => (int) $page['page'],
            'section' => trim((string) ($page['section'] ?? 'Contenido de la guía')) ?: 'Contenido de la guía',
            'text' => Str::limit(trim((string) ($page['text'] ?? '')), 2200, '…'),
        ])->filter(fn (array $excerpt): bool => $excerpt['text'] !== '')->values()->all();

        return [
            'title' => (string) ($document['title'] ?? 'Guía de Investigación de Accidentes'),
            'version' => (string) ($document['version'] ?? 'Versión local'),
            'source' => (string) ($document['source_file'] ?? 'Documento fuente'),
            'pages' => array_column($excerpts, 'page'),
            'excerpts' => $excerpts,
        ];
    }
}
