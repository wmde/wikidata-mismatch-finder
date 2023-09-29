<!DOCTYPE html>
<html lang="{{ App::currentLocale() }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <script src="{{ mix('/js/app.js') }}" defer></script>

    <noscript>
        @include('noscript')
    </noscript>
  </head>
  <body class="app-container" dir="auto">
    @inertia
  </body>
</html>
