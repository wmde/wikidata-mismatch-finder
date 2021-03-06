<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('store-layout.mismatch-finder')}}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="{{ Route::currentRouteName() }}">
    <main class="store">
        <header>
            <h1>{{__('store-layout.mismatch-finder')}}</h1>
            <div class="auth-widget">
                @auth
                    <a href='https://www.wikidata.org/wiki/User:{{ Auth::user()->username}}'><img src="{{ asset('/svg/user.svg') }}" class="button-icon" /><span class="username">{{ Auth::user()->username }}</span></a><a href="{{ route('logout') }}">Logout</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                @endauth
            </div>
        </header>
        <nav class="tabs" aria-label="{{__('store-layout.aria-labels:tabs')}}">
            <ul>
                <li tabindex="0" {{ ( url()->current() == route('store.api-settings') ) ? 'aria-current=page' : '' }} class="{{url()->current() == route('store.api-settings') ? 'selected' : ''}}"><a tabindex="-1" href="{{ route('store.api-settings') }}" >{{__('store-layout.tab:api-access-settings')}}</a></li>
                <li tabindex="0" {{ ( url()->current() == route('store.import-status') ) ? 'aria-current=page' : '' }} class="{{url()->current() == route('store.import-status') ? 'selected' : ''}}"><a tabindex="-1"  href="{{ route('store.import-status') }}">{{__('store-layout.tab:import-status')}}</a></li>
            </ul>
        </nav>
        {{ $slot }}
    </main>
    <script> </script>
</body>
</html>
