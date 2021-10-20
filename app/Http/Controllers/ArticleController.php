<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Request;
use App\Traits\ResourceTrait;
use App\Http\Resources\DataResource;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Cloudinary\Api\Upload\UploadApi;

class ArticleController extends Controller
{
    use ResourceTrait;

    public function __construct()
    {
        $this->middleware(["auth:sanctum", 'verify.author'])->only([
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
            if ($trend->view_count < 1) {
                unset($trending[$key]);
            }
        }
        krsort($trending);
        return $this->createResource($trending);
    }

    public function byAuthor(Request $request)
    {
        $filters = ['id', 'name'];
        $request->validate([
            "filter_by"=> "required|in:". implode(',', $filters)
        ]);
        if($request->input('filter_by') == 'id'){
            $request->validate([
                "q"=> "required|integer"
            ]);
            $articles_arr = [];
            Article::where('user_id', $request->input('q'))->orderBy('id', 'desc')->with('author')->chunk(50, function ($articles) use (&$articles_arr){
                foreach ($articles as $key => $article) {
                    $article->createArticleData($article);
                }
                $articles_arr = $articles;
            });
            return $this->createResource($articles_arr);
        }
        if($request->input('filter_by') == 'name'){
            $request->validate([
                "q"=> "required|string"
            ]);
            $user = User::where('id', $request->user()->id)->with('articles');
            $user->image_url =  $user->image_data !== null ? json_decode($user->image_data, true)['secure_url'] : null;
            $user->relative_at = $this->timeago($user->created_at);
            foreach ($user->articles as $key => $article) {
                $article->createArticleData($article);
            }
            return new DataResource(["user"=> $user]);
        }
        return $this->returnError([
            "field"=> "q",
            "message"=> "Invalid query parameter",
            "code"=> 400
        ]);
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
    
    public function uploadImage(Request $request){
        $request->validate([
            "image"=> "required|image"
        ]);
        
        // Get File name and extension
        // $FileNameWithExt = $request->file('image')->getClientOriginalName();
        // Get File name
        // $fileName = pathinfo($FileNameWithExt, PATHINFO_FILENAME);
        // Get File ext
        $fileExt = $request->file('image')->getClientOriginalExtension();
        // File name to store
        $fileNameToStore = time() . '.' . $fileExt;
        // Store Image
        $request->file('image')->storeAs('public/articles', $fileNameToStore);
        list($width, $height) = getimagesize(storage_path('app/public/articles/' . $fileNameToStore));
        //obtain ratio
        $imageratio = $width / $height;

        if ($imageratio >= 1) {
            $newwidth = 600;
            $newheight = 600 / $imageratio;
        } else {
            $newwidth = 400;
            $newheight = 400 / $imageratio;
        };
        Image::make(storage_path('app/public/articles/' . $fileNameToStore))->resize($newwidth, $newheight)->save(storage_path('app/public/articles/' . $fileNameToStore));
        $upload = (new UploadApi())->upload(storage_path('app/public/articles/' . $fileNameToStore), [
            "folder" => "think-tech/articles/", 
        ]
        );
        return json_encode([
            "success"=> 1,
            "file"=> [
                "url"=> $upload['secure_url']
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @re
     * turn \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "title"=> "required|string",
            "body"=> "required|array"
        ]);
        if(count($request->body["blocks"]) < 1){
            return $this->returnError(["code"=> 422, "message"=> "Article body is empty!", "field"=> "body"]);
        }
        $article = Article::create([
            "user_id"=> $request->user()->id,
            "title"=> $request->title,
            "body"=> json_encode($request->body)
        ]);
        return new DataResource([
            "article"=> $article,
            "message"=> "Article has been published successfully!"
        ]);
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
        $article = Article::where(['title' => $articleTitle])->first();
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
        $request->validate([
            "body"=> "required|array"
        ]);
        if(count($request->body["blocks"]) < 1){
            return $this->returnError(["code"=> 422, "message"=> "Article body is empty!", "field"=> "body"]);
        }
        $article = Article::where(['id'=> $id, 'user_id'=> $request->user()->id])->update([
            "body"=> json_encode($request->input('body'))
        ]);
        return new DataResource([
            "article"=> $article,
            "message"=> "Article has been updated successfully!"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $article = Article::find($id);
        // $blocks = json_decode($article->body, true);
        // $blocks = $blocks['blocks'];
        // $imgurl = [];
        // foreach ($blocks as $key => $block) {
        //     switch ($block['type']) {
        //         case 'image':
        //             array_push($imgurl, $block['data']['file']['url']);
        //             break;
        //         default:
        //             break;
        //     }
        // }
        // foreach ($imgurl as $key => $img) {
        //     $file = str_replace("https://thinktech.fuoye360.com/storage/articles/", '', $img);
        //     Storage::delete('public/articles/' . $file);
        // }
        $article->delete();
        return new DataResource(["message"=> "Article deleted successfully!"]);
    }
}
