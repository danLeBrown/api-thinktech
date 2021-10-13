<?php

namespace App\Models;

use App\Traits\TimeagoTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory, TimeagoTrait;

    protected $fillable = [
        'user_id',
        'title',
        'body',
    ];

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function createArticleData($article)
    {
        $article->relative_at = $this->timeago($article->created_at);
        $article->body = json_decode($article->body, true);
        $article->meta = [
            'title_link' => strtolower(str_replace(' ', '-', $article->title)),
            'author_link' => strtolower(str_replace(' ', '-', $article->author->name))
        ];
        return $article;
    }
}
