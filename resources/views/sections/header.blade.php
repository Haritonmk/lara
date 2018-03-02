<div class="top_nav" @if (Auth::guest())style="margin-left: 0;"@endif>
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                @if (Auth::guest())
                  <a href="{{ url('/') }}">
                      <span>{{ config('app.name') }}</span>
                  </a>
                @else
                  <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                @endif
            </div>
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @if (Auth::guest())
                    <li><a href="{{ url('/login') }}">Login</a></li>
                    <li><a href="{{ url('/register') }}">Register</a></li>
                @else
                    <li class="dropdown">
                        <a href="#" class="user-profile dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            {{ Auth::user()->name }} <span class=" fa fa-angle-down"></span>
                        </a>

                        <ul class="dropdown-menu dropdown-usermenu" role="menu">
                            <li><a href="{{ url('/learn') }}"><i class="fa fa-btn"></i>Learn</a></li>
                            <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
          </nav>
      </div>
  </div>
