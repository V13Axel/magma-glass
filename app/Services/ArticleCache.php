<?php

namespace App\Services;

use App\Exceptions\ArticleNotFoundException;
use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleCache
{
    public static function populate()
    {
        return cache()->remember('articles_cache', config('magmaglass.cache_ttl'), function() {
            return static::loadDirectory('/');
        });
    }

    public static function loadDirectory(string $directory): array
    {
        return collect(Storage::disk('articles')->allFiles())
            ->reject(fn($path) => Str::startsWith($path, '.obsidian/'))
            ->mapWithKeys(fn($path) => [basename($path) => $path])
            ->toArray();
    }

    public static function getByArticleName($articleName)
    {
        $articlePath = collect(static::populate())->get($articleName . '.md');
        if(!Storage::disk('articles')->exists($articlePath)) {
            throw new ArticleNotFoundException("No article '$articleName' was found.");
        }

        return new Article($articleName, Storage::disk('articles')->get($articlePath));
    }
}
