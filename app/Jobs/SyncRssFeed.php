<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
// Trait que permite despachar el Job fácilmente
use Illuminate\Foundation\Bus\Dispatchable;
// Servicio que se encargará de la lógica de sincronización del RSS
use App\Services\RssFeedService;

/**
 * Class SyncRssFeed
 *
 * Este Job se encarga de:
 * 1. Hacer una petición HTTP al RSS de Crunchyroll vía servicio RssFeedService.
 */
class SyncRssFeed implements ShouldQueue
{
    use Dispatchable;

    /**
     * Método principal que se ejecuta cuando el Job es procesado por el worker.
     */
    public function handle(RssFeedService $rssFeedService): void
    {
        $rssFeedService->syncCrunchyrollRss();
    }
}