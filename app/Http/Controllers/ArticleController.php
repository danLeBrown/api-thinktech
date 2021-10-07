<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Request;
use App\Traits\ResourceTrait;
use App\Http\Resources\DataResource;

class ArticleController extends Controller
{
    use ResourceTrait;

    public function __construct()
    {
        $this->middleware('verify.author')->only([
            'store',
            'update',
            'delete'
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles_arr = [];
        Article::orderBy('id', 'desc')->with('author')->chunk(50, function ($articles) use (&$articles_arr){
            foreach ($articles as $key => $article) {
                $article->createArticleData($article);
            }
            $articles_arr = $articles;
        });
        return $this->createResource($articles_arr);
    }

    public function trending()
    {
        $trendingArticles = ArticleView::with('article')->get();
        $trending = [];
        $checker = [];
        foreach ($trendingArticles as $key => $trendingArticle) {
            if (!array_key_exists($trendingArticle->article_id, $checker)) {
                $checker[$trendingArticle->article_id]['viewCount'] = count(ArticleView::where('article_id', $trendingArticle->article_id)->get());
                if($trendingArticle->article != null){
                    $trending[$checker[$trendingArticle->article_id]['viewCount']] = $trendingArticle->article;
                    $trending[$checker[$trendingArticle->article_id]['viewCount']]->view_count = $checker[$trendingArticle->article_id]['viewCount'];
                }
            }
        }
        foreach ($trending as $key => $trend) {
            $trend->createArticleData($trend);
            if ($trend->view_count < 5) {
                unset($trending[$key]);
            }
        }
        krsort($trending);
        return $this->createResource($trending);
    }

    public function author($user_id)
    {
        $articles_arr = [];
        Article::where('user_id', $user_id)->orderBy('id', 'desc')->with('author')->chunk(50, function ($articles) use (&$articles_arr){
            foreach ($articles as $key => $article) {
                $article->createArticleData($article);
            }
            $articles_arr = $articles;
        });
        return $this->createResource($articles_arr);
    }

    public function tag($tag)
    {
        $articles_arr = [];
        Article::where('tag', 'LIKE', "%{$tag}%")->orderBy('id', 'desc')->with('author')->chunk(50, function ($articles) use (&$articles_arr){
            foreach ($articles as $key => $article) {
                $article->createArticleData($article);
            }
            $articles_arr = $articles;
        });
        return $this->createResource($articles_arr);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "title"=> "required|string",
            "body"=> "required|string"
        ]);
        $article = Article::create([
            "user_id"=> $request->user()->id,
            "title"=> $request->title,
            "body"=> $request->body
        ]);
        return new DataResource($article);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $articleTitle = str_replace('-', ' ', $id);
        $article = Article::where(['title' => $articleTitle])->get()[0];
        $article->createArticleData($article);        
        $response = [
            'article' => $article,
        ];
        return new DataResource($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
