<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
          <!-- Branding Image -->
          <a class="site_title" href="{{ url('/') }}">
              <span>{{ config('app.name') }}</span>
          </a>
        </div>

        <div class="clearfix"></div>

        <!-- menu profile quick info -->
        <div class="profile clearfix">

        </div>
        <!-- /menu profile quick info -->

        <br/>

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <h3>Learn</h3>
                <ul class="nav side-menu">
                    <li>
                        <a href="{{ url("/learn/list-category") }}">
                            <i class="fa fa-list-ul" aria-hidden="true"></i>
                            List
                        </a>
                    </li>
                    <li>
                        <a href="{{ url("/learn/phrase") }}">
                            <i class="fa fa-commenting" aria-hidden="true"></i>
                            Phrase
                        </a>
                    </li>
                    <li>
                        <a href="{{ url("/learn/articles") }}">
                            <i class="fa fa-file-word-o" aria-hidden="true"></i>
                            Articles
                        </a>
                    </li>
                </ul>
            </div>
            <div class="menu_section">
                <h3>Other</h3>
                <ul class="nav side-menu">
                    <li>
                        <a href="{{ url("/") }}">
                            <i class="fa fa-tasks" aria-hidden="true"></i>
                            Task List
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->
    </div>
</div>
