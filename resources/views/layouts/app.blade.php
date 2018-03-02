<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <!-- Fonts -->
    <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    --><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

    <!-- Styles -->
    {{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">--}}
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}
    <link rel="stylesheet" href="<?=asset('css/app.0874df32067411f3a7adec84cd0d9789.css');?>">
    <link rel="stylesheet" href="<?=asset('css/source.02b8ab8bd624bfc9706e34354135a000.css');?>">

    <!-- Scripts -->

    <script src="<?=asset('js/vue.440e570c372631aa20b9c778ad9e7273.js');?>"></script>
    <script src="<?=asset('js/vue-resource.js');?>"></script>
</head>
<body id="app-layout" class="nav-md">
  <div class="container body">
    <div class="main_container">

      @section('header')
          @if (!Auth::guest())
            @include('sections.navigation')
          @endif
          @include('sections.header')
      @show

      <div class="right_col" role="main" @if (Auth::guest())style="margin-left: 0;"@endif>
          <div class="page-title">
              <div class="title_left">
                  <h1 class="h3">@yield('title')</h1>
              </div>
          </div>
          <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                @yield('content')
            </div>
          </div>
      </div>

      <footer @if (Auth::guest())style="margin-left: 0;"@endif>
        @include('sections.footer')
      </footer>
    </div>
  </div>
  <script src="<?=asset('js/app.cb4b4f57e6d542b31c258d38bf63ca54.js');?>"></script>
  <script src="<?=asset('js/source.9b6fd95e6c58f761c94d3e1a25d3cd3e.js');?>"></script>
    <!-- JavaScripts -->
    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  -->
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
</body>
</html>
