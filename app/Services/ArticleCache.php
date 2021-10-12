<?php

namespace App\Services;

use App\Exceptions\ArticleNotFoundException;
use App\Models\Article;
use App\Services\ArticleParser\Pipeline\IsolateTags;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleCache
{
    public static function populate()
    {
        return cache()->remember('articles_cache', config('magmaglass.cache_ttl'), function() {
            $articles = collect(static::loadDirectory('/'));

            return [
                'articles' => $articles,
                'links' => self::buildLinks($articles)
            ];
        });
    }

    public static function allWithTag($tag)
    {
        return static::populate()['articles']->filter(function($article) use ($tag) {
            return $article['tags']->contains("#$tag");
        });
    }

    public static function buildLinks($articles)
    {
        return $articles->map(function($article) {
            $links = [];

            foreach($article['links'] as $link) {
                $links[] = [$article['title'], $link];
            }

            return $links;
        })->values()->flatten(1);
    }

    public static function loadDirectory(string $directory): array
    {
        return collect(Storage::disk('articles')->allFiles())
            ->reject(fn($path) => Str::startsWith($path, '.obsidian/'))
            ->mapWithKeys(function($path) {
                $content = Str::of(Storage::disk('articles')->get($path));
                $tags = $content->matchAll(IsolateTags::$pattern);
                $links = $content->matchAll('/\[\[(.+?)\]\]/u')->reject(fn($string) => Str::endsWith($string, ['.png', '.jpg', '.jpeg', '.gif', '.bmp', '.tif', '.tiff']));

                return [
                    basename($path) => [
                        'path' => $path,
                        'title' => pathinfo($path)['filename'],
                        'tags' => $tags,
                        'links' => $links
                    ]
                ];
            })
            ->toArray();
    }

    public static function hasArticle($articleName): bool
    {
        return static::populate()['articles']->has($articleName . '.md');
    }

    /**
     * Retreives a given Article by name
     *
     * @param $articleName
     * @return Article
     * @throws ArticleNotFoundException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getByArticleName($articleName): Article
    {
        $info = static::populate()->get($articleName . '.md');
        $articlePath = $info['path'];
        if(!Storage::disk('articles')->exists($articlePath)) {
            throw new ArticleNotFoundException("No article '$articleName' was found.");
        }

        return new Article($articleName, $articlePath, Storage::disk('articles')->get($articlePath), $info['tags']);
    }

    /**
     * Retreives a given Article by path
     *
     * @param $articlePath
     * @return Article
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getByArticlePath($articlePath): Article
    {
        $localPath = Str::endsWith($articlePath, '.md')
            ? $articlePath
            : $articlePath . '.md';

        $fullArticlePath = Storage::disk('articles')->path($localPath);
        if(!Storage::disk('articles')->exists($localPath)) {
            if($articlePath == 'Home') {
                return new Article('', '', '_You can create a note in the root of your vault called **"Home"** to customize this page._');
            }

            return new Article('Oooops!', $fullArticlePath, "## The file '$articlePath' doesn't exist yet!");
        }

        $details = pathinfo($fullArticlePath);

        return new Article($details['filename'], $fullArticlePath, Storage::disk('articles')->get($localPath));
    }

    public static function hasImage(string $imageName)
    {
        return static::populate()['articles']->has($imageName);
    }
}
