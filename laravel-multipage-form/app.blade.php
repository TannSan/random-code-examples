<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @if(Request::is('users', 'roles', 'permissions', 'users/*', 'roles/*', 'permissions/*'))
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
        @else
        <link href="{{ asset('css/user.css') }}" rel="stylesheet">
        @endif
        @if(Request::is('step/*', '*/step/*', 'records', 'report/*'))
        <link href="{{ asset('css/questions.css') }}" rel="stylesheet">
        @if(Request::is('report/*'))
        <link href="{{ asset('css/reports.css') }}" rel="stylesheet">
        @endif
        @endif
        <link href="{{ asset('css/print.css') }}" rel="stylesheet">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#da4e94">
        <meta name="msapplication-TileColor" content="#da4e94">
        <meta name="theme-color" content="#da4e94">
        <link rel="author" href="/humans.txt" />
    </head>
    <body>
        <header id="site-header" class="d-print-none{{ Request::is('login', 'register', 'password/*', 'welcome') ? ' pinky' : '' }}">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-4 col-md-5 offset-md-1" id="header-merck-logo-wrapper">
                        <img id="header-merck-logo" src="/img/merck-logo-153px.png" width="153" height="24" alt="Merck" title="Merck Logo" />
                    </div>
                    <div class="col-12 col-sm-8 col-md-6">
                        <nav class="nav justify-content-end" id="main-navigation">
                            @guest
                            @if(!Request::is('login'))
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                            @endif
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                            @else
                            @hasanyrole('Admin|User')
                            <a class="nav-link" href="/welcome">Menu</a>
                            @endhasanyrole
                            @role('Admin')
                            <a class="nav-link" href="/users">Admin</a>
                            @endrole
                            <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign Out</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                            </form>
                            @endguest
                            {{-- <a class="nav-link" href="#" id="btn-fullscreen"><img src="/img/icons/Fullscreen.png" width="30" height="30" alt="Full Screen" title="Toggle full screen mode" /></a> --}}
                        </nav>
                    </div>
                </div>
            </div>
        </header>
        @yield('content')
        <div class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col col-md-11 offset-md-1">
                        <p class="copyright my-5">&copy; Merck KGaA, Darmstadt, Germany<br /><a href="#" class="d-print-none">Legal disclaimer</a></p>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery.js') }}"></script>
        @if(Request::is('*/step/5'))<script src="{{ asset('js/step-5.js') }}"></script>
        @elseif(Request::is('*/step/6'))<script src="{{ asset('js/step-6.js') }}"></script>
        @elseif(Request::is('records'))
        <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
        <script src="{{ asset('js/print.js') }}"></script>
        <script src="{{ asset('js/records.js') }}"></script>
        @elseif(Request::is('report/*/*'))
        <script src="{{ asset('js/Chart.min.js') }}"></script>
        @if(Request::is('report/*/overview'))
        <script src="{{ asset('js/chartjs-plugin-datalabels.min.js') }}"></script>
        <script src="{{ asset('js/report-overview.js') }}"></script>
        @elseif(Request::is('report/*/cycle-focus'))
        <script src="{{ asset('js/chartjs-plugin-datalabels.min.js') }}"></script>
        <script src="{{ asset('js/report-cycle-focus.js') }}"></script>
        @elseif(Request::is('report/*/year-comparison'))<script src="{{ asset('js/report-year-comparison.js') }}"></script>
        @elseif(Request::is('report/*/referrals'))<script src="{{ asset('js/jquery-numerator.js') }}"></script>
        <script src="{{ asset('js/report-referrals.js') }}"></script>
        @elseif(Request::is('report/*/benchmark'))<script src="{{ asset('js/report-benchmark.js') }}"></script>
        @endif
        <script src="{{ asset('js/general.js') }}"></script>
        @endif
        {{-- <script src="{{ asset('js/screenfull.min.js') }}"></script> --}}
   </body>
</html>
