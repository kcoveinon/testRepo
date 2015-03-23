<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta charset="utf-8" />
        <link type="text/css" href="{{{ asset('css/src/semantic.min.css')  }}}" rel="stylesheet"  media="screen"/>

        <meta name="_token" content="{{ csrf_token() }}" />
        <title> @yield('title')  </title>
    </head>

    <body>
        <div class="container">

            <header class="row">
                @yield('page_header')
            </header>

            <div id="main" class="row">
                @yield('content')
            </div>

        </div>

    </body>


    {{ HTML::script('js/src/jquery-2.0.0.min.js') }}
    {{ HTML::script('js/src/semantic.min.js') }}
     @yield('page_js')

</html>
