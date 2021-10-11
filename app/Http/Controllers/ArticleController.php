<?php

namespace App\Http\Controllers;

use App\Jobs\RetrieveArticle;
use App\Services\ArticleCache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index($articlePath = '')
    {
        return view('show_article', [
            'isIndex' => $articlePath == ''
        ]);
    }

    public function articleJson(Request $request)
    {
        logger()->debug('-------------new request!------------');
        $article = RetrieveArticle::dispatchSync($request->input('articlePath') ?? '');

        return [
            'title' => $article->name,
            'content' => $article->getParsed(),
            'path' => $request->input('articlePath') ?? '',
            'links' => ArticleCache::populate()['links']
        ];
    }

    public function noSuchArticle(Request $request)
    {
        $article = $request->get('articlePath');

        return "Article '$article' does not exist";
    }

    public function tag(Request $request)
    {
        return view('tag_search', [
            'tagSearch' => $request->input('tag'),
            'results' => ArticleCache::allWithTag($request->input('tag'))
        ]);
    }

    public function linkData()
    {
        return ArticleCache::populate();
    }

    public function search()
    {
        return ArticleCache::populate()['articles']->filter(function($article){
            return Str::contains(strtolower($article['title']), strtolower(request()->input('searchTerm')));
        })->values();
    }
}
