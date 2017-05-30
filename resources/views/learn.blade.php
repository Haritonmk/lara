@extends('layouts.app')

@section('content')
<div class="container">
    <a href="{{ url("/learn/list-category") }}" class="btn btn-default btn-lg">List</a>
    <a href="{{ url("/learn/phrase") }}" class="btn btn-default btn-lg">Phrase</a>
</div>
@endsection