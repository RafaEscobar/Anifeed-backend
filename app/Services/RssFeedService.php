<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssFeedService
{
    public function syncCrunchyrollRss(): void
    {
        $response = Http::get('https://cr-news-api-service.prd.crunchyrollsvc.com/v1/es-ES/rss');

        if (! $response->successful()) {
            Log::error('No se pudo consultar el RSS de Crunchyroll', [
                'status' => $response->status(),
            ]);
            return;
        }

        $xml = simplexml_load_string($response->body());

        if (! $xml || ! isset($xml->channel->item)) {
            Log::warning('El RSS de Crunchyroll no tiene el formato esperado o cambio.');
            return;
        }

        foreach ($xml->channel->item as $item) {
            $title = (string) $item->title;
            Log::info('RSS item encontrado: ' . $title);
        }
    }
}