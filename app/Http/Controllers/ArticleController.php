<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use App\Http\Requests;

class ArticleController extends Controller
{
    public function index()
    {
      $articles = Article::all();
      $articles = $articles->each(function ($item, $key) {
        //delete control symbols
        $item->body_eng = preg_replace('/\p{Cc}+/u', '', $item->body_eng);
        //todo...
        $item->body_eng = preg_replace('/<p>/im', '', $item->body_eng);
        $item->body_eng = preg_replace('/<\/p>/im', '', $item->body_eng);
      });
      return response()->json($articles->toArray());
    }
}
