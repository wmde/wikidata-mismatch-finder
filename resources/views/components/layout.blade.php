<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mismatch Finder</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header>
        <h1>Mismatch Finder</h1>
        <div class="username">
            @auth
                <a href='https://www.wikidata.org/wiki/User:{{ Auth::user()->username}}'><img src="/svg/user.svg" class="icon-user" />{{ Auth::user()->username }}</a><a href="{{ route('logout') }}">Logout</a>
            @else
                <a href="{{ route('login') }}">Log in</a>
            @endauth
        </div>
    </header>
    <nav class="tabs">
        <ul>
            <li class="{{url()->current() == route('token') ? 'active' : ''}}"><a href="{{ route('token') }}">{{__('store-layout.tab:authentication-token')}}</a></li>
            <li><a href="#">{{__('store-layout.tab:import-status')}}</a></li>
        </ul>
    </nav>
    {{ $slot }}
</body>
</html>