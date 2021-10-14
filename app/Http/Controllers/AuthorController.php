<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\UserVisit;
use App\Models\ArticleView;
use Illuminate\Http\Request;
use App\Traits\UserProfileTrait;
use App\Http\Resources\DataResource;

class AuthorController extends Controller
{
    use UserProfileTrait;

    public function analyticStats(Request $request)
    {
        $views_count = 0;
        $visits_count = 0;
        $articles = Article::where('user_id', $request->user()->id)->get();
        $articles_count = count($articles);
        foreach($articles as $key => $article){
            $views = ArticleView::where('article_id', $article->id)->get();
            $views_count += count($views);
            $visits = UserVisit::where('article_id', $article->id)->get();
            $visits_count += count($visits);
        }

        return new DataResource([
            "articles_written"=> $articles_count,
            "profile_visits"=> $visits_count,
            "articles_views"=> $views_count
        ]);        
    }
}
