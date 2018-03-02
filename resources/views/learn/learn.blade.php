@extends('layouts.app')

@section('content')
<div class="x_panel">
    <a href="{{ url("/learn/list-category") }}" class="btn btn-default btn-lg">List</a>
    <a href="{{ url("/learn/phrase") }}" class="btn btn-default btn-lg">Phrase</a>
    <a href="{{ url("/learn/articles") }}" class="btn btn-default btn-lg">Articles</a>
</div>
@endsection
