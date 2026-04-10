<?php

namespace App\Services;

use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class RssFeedService
{
    /**
     * Sincroniza el RSS de Crunchyroll.
     *
     * 1. Consulta el feed RSS por HTTP.
     * 2. Verifica que la respuesta sea correcta.
     * 3. Valida que el XML tenga la estructura esperada.
     * 4. Si el RSS cambió, procesa sus categorías.
     */
    public function syncCrunchyrollRss(): void
    {
        // Realiza la petición HTTP al RSS con:
        // - timeout de 10 segundos (si en 10 segundos no hay respuesta, aborta)
        // - hasta 3 intentos (intenta hasta 3 veces esperando 500 ms entre intentos)
        $response = Http::timeout(10)->retry(3, 500)->get(
            'https://cr-news-api-service.prd.crunchyrollsvc.com/v1/es-ES/rss'
        );

        // Si la respuesta no fue exitosa, registrar el error y detener el proceso
        if (! $response->successful()) {
            throw new Exception('No se pudo consultar el RSS de Crunchyroll');
        }

        // Valida el XML recibido y verifica si el RSS realmente cambió
        $xml = $this->validateRss($response->body());

        //  No hay nada que hacer RSS sin cambios
        if (! $xml) {
            return;
        }

        // Si todo está bien, sincroniza las categorías encontradas en el RSS
        $this->syncCategories($xml);
        $this->syncNews($xml);
    }

    /**
     * Valida que el contenido del RSS sea XML válido.
     * También evita reprocesar el feed si no ha cambiado.
     *
     * @param  string  $body  Contenido XML en texto plano
     * @return SimpleXMLElement  XML parseado
     *
     * @throws Exception Si el XML es inválido o si el RSS no cambió
     */
    protected function validateRss(string $body): SimpleXMLElement
    {
        // Convierte el texto XML en un objeto SimpleXMLElement
        $xml = simplexml_load_string($body);

        // Verifica que el XML exista y que tenga la etiqueta <channel>
        if (! $xml || ! isset($xml->channel)) {
            throw new Exception('El RSS de Crunchyroll no tiene un formato XML válido.');
        }

        // Obtiene la fecha de última actualización del RSS
        $lastBuildDate = (string) ($xml->channel->lastBuildDate ?? '');

        // Recupera la última fecha guardada en caché
        $lastBuildDateGuardado = Cache::get('crunchyroll_last_build_date');

        // Si la fecha es igual a la guardada, significa que el RSS no cambió
        if ($lastBuildDate !== '' && $lastBuildDate === $lastBuildDateGuardado) {
            Log::info('RSS sin cambios, se omite procesamiento');
            return null;
        }

        // Si el RSS trae una fecha válida, se guarda en caché por 1 día
        if ($lastBuildDate !== '') {
            Cache::put('crunchyroll_last_build_date', $lastBuildDate, now()->addDay());
        }

        // Registra que el RSS sí fue actualizado y se va a procesar
        Log::info('RSS actualizado, se procesa información', [
            'lastBuildDate' => $lastBuildDate,
        ]);

        return $xml;
    }

    /**
     * Recorre los items del RSS y guarda sus categorías en la base de datos.
     *
     * Evita procesar categorías repetidas dentro del mismo RSS.
     *
     * @param  SimpleXMLElement  $xml  RSS ya validado
     */
    protected function syncCategories(SimpleXMLElement $xml): void
    {
        // Guarda las categorías ya procesadas para no repetirlas
        $processedCategories = [];

        // Recorre cada item del feed RSS
        foreach ($xml->channel->item as $item) {
            // Si el item no tiene categoría, se omite
            if (! isset($item->category)) {
                continue;
            }

            // Limpia espacios en blanco del nombre de la categoría
            $nombre = trim((string) $item->category);

            // Si la categoría viene vacía, se omite
            if ($nombre === '') {
                continue;
            }

            // Si esta categoría ya fue procesada en esta misma ejecución, se omite
            if (in_array($nombre, $processedCategories, true)) {
                continue;
            }

            // Marca la categoría como procesada
            $processedCategories[] = $nombre;

            // Busca la categoría por nombre o la crea si no existe
            $categoria = Category::firstOrCreate([
                'name' => $nombre,
            ]);

            // Registra información útil sobre la categoría procesada
            Log::info('Categoría procesada', [
                'id' => $categoria->id,
                'name' => $categoria->name,
            ]);
        }
    }


    /**
     * Recorre los items del RSS y guarda las noticias en la base de datos.
     *
     * Evita procesar noticias repetidas dentro del mismo RSS.
     *
     * @param  SimpleXMLElement  $xml  RSS ya validado
     */
    protected function syncNews(SimpleXMLElement $xml): void
    {
        // Guarda las noticias ya procesadas para no repetirlas
        $processedNews = [];

        foreach($xml->channel->item as $item) {
            // Obtenemos y limpiamos el título de la noticia
            $title = trim((string)$item->$title);

            // Si el titulo de la noticia existe, se omite
            if (in_array($title, $processedNews, true)) {
                continue;
            }

            $new = Story::firtOrCreate([
                'title' => $title,
                'description' => (string)$item->$description,
                'content' => (string)$item->$content,
                'image_url' => (string)$item->$image_url,
                'published_at' => (string)$item->$published_at,
                'category_id' => Category::where('name', (string)$item->category)->first()->id,
            ]);

            // Registra información útil sobre la categoría procesada
            Log::info('Noticia procesada', [
                'name' => $item->name,
                'id' => $item->id,
            ]);
        }
    }

}