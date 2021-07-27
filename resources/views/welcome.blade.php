<x-layout>
    <p>Coming soon, to a screen near you...</p>
-----------------------
    @auth
        <p>Hello, {{ Auth::user()->username }}! <a href="{{ route('logout') }}">Logout</a></p>

        Check out our <a href="{{ route('token') }}">shiny new API tokens</a>!
    @else
        <p>Hello, Guest! <a href="{{ route('login') }}">Login</a></p>
    @endauth
</x-layout>
