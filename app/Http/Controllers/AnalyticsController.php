<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Request;
use App\Http\Resources\DataResource;
use App\Models\UserVisit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AnalyticsController extends Controller
{
    public function updateViews(Request $request){
        $request->validate([
            "article_id"=> "required|integer"
        ]);
        $article_id = $request->article_id;
        $views = count(ArticleView::where('article_id', $article_id)->get());
        if(Auth::user()){
            $user_id = auth('sanctum')->user()->id;
            $author_id = Article::find($article_id)->user_id;
            if($user_id == $author_id){
                return new DataResource(["article_views" => $views]);
            }
        }
        $sessionArticleViews = Session::has('ArticleViews') ? Session::get('ArticleViews') : null;
        if ($sessionArticleViews == null) {
            ArticleView::create([
                "user_id"=> Auth::user() ? $user_id : null,
                "article_id"=> $article_id
            ]);
            $views = count(ArticleView::where('article_id', $article_id)->get());

            $articleToAdd = array(
                'article_id' => $article_id
            );
            Session::put('ArticleViews', []);
            Session::push('ArticleViews', $articleToAdd);
            return new DataResource(["article_views" => $views]);
        }
        $array_article_id = array_column($sessionArticleViews, "article_id");
        if (in_array($article_id, $array_article_id)) {
            $views = count(ArticleView::where('article_id', $article_id)->get());
            return new DataResource(["article_views" => $views]);
        }
        $articleToAdd = array(
            'article_id' => $article_id
        );
        ArticleView::create([
            "user_id"=> Auth::user() ? $user_id : null,
            "article_id"=> $article_id
        ]);
        $views = count(ArticleView::where('article_id', $article_id)->get());
        Session::push('ArticleViews', $articleToAdd);
        return new DataResource(["article_views"=> $views]);
    }

    public function updateVisits(Request $request){
        $request->validate([
            "article_id"=> "required|integer"
        ]);
        $article_id = $request->article_id;
        $visits = count(UserVisit::where('article_id', $article_id)->get());
        if(Auth::user()){
            $user_id = auth('sanctum')->user()->id;
            $author_id = Article::find($article_id)->user_id;
            if($user_id == $author_id){
                return new DataResource(["author_visits" => $visits]);
            }
        }
        $sessionUserVisits = Session::has('UserVisits') ? Session::get('UserVisits') : null;
        if ($sessionUserVisits == null) {
            UserVisit::create([
                "user_id"=> Auth::user() ? $user_id : null,
                "article_id"=> $article_id
            ]);
            $visits = count(UserVisit::where('article_id', $article_id)->get());

            $articleToAdd = array(
                'article_id' => $article_id
            );
            Session::put('UserVisits', []);
            Session::push('UserVisits', $articleToAdd);
            return new DataResource(["author_visits" => $visits]);
        }
        $array_article_id = array_column($sessionUserVisits, "article_id");
        if (in_array($article_id, $array_article_id)) {
            $visits = count(UserVisit::where('article_id', $article_id)->get());
            return new DataResource(["author_visits" => $visits]);
        }
        $articleToAdd = array(
            'article_id' => $article_id
        );
        UserVisit::create([
            "user_id"=> Auth::user() ? $user_id : null,
            "article_id"=> $article_id
        ]);
        $visits = count(UserVisit::where('article_id', $article_id)->get());
        Session::push('ArticleViews', $articleToAdd);
        return new DataResource(["author_visits"=> $visits]);
    }
    
}
