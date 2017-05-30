<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Word;
use App\Http\Requests;

class WordController extends Controller
{
    public function getPhrase()
    {
        $word = Word::inRandomOrder()->first();
        return response()->json($word->toArray());
    }
}
