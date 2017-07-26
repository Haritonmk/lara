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
        //htmlspecialchars_decode($str, ENT_QUOTES);
        //Families break up when people take hints you don&rsquo;t intend and miss hints you do intend.
        $tempArray = $word->toArray();
        $trans = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
        $trans = array_flip($trans);
        $tempArray['qeng'] = strtr($tempArray['qeng'], $trans);
        //$tempArray['trans'] = $trans;
        return response()->json($tempArray);
    }
}
