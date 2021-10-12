<?php

namespace App\Services\ArticleParser\Pipeline;

use App\Models\Article;
use App\Services\ArticleCache;

class IsolateTags extends \App\Services\ArticleParser\Pipe
{

    public function parse(Article $article): Article
    {
        return $article->setContent(preg_replace_callback('/(#+[a-zA-Z0-9(_)]{1,})/m', [static::class, 'replaceWikiLinks'], $article->content));
    }

    private static function replaceWikilinks($matches)
    {
//        return sprintf("[%s](%s)", $matches[1], route('tag', ['tag' => $matches[1]]));
        return sprintf("<a class='px-2 mr-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' href='%s'>%s</a>", route('tag', ['tag' => $matches[1]]), $matches[1]);
    }
}
