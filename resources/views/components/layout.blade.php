<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('store-layout.mismatch-finder')}}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header>
        <h1>{{__('store-layout.mismatch-finder')}}</h1>
        <div class="top-nav-right">
            @auth
                <a href='https://www.wikidata.org/wiki/User:{{ Auth::user()->username}}'><img src="{{ asset('/svg/user.svg') }}" class="icon-user" /><span class="username">{{ Auth::user()->username }}</span></a><a href="{{ route('logout') }}">Logout</a>
            @else
                <a href="{{ route('login') }}">Log in</a>
            @endauth
        </div>
    </header>
    <nav class="tabs" aria-label="{{__('store-layout.aria-labels:tabs')}}">
        <ul>
            <li tabindex="0" {{ ( url()->current() == route('token') ) ? 'aria-current=page' : '' }} class="{{url()->current() == route('token') ? 'selected' : ''}}"><a tabindex="-1" href="{{ route('token') }}" >{{__('store-layout.tab:authentication-token')}}</a></li>
            <li tabindex="0"><a tabindex="-1" href="#">{{__('store-layout.tab:import-status')}}</a></li>
        </ul>
    </nav>
    {{ $slot }}
</body>
</html>