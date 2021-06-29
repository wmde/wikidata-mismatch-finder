<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mismatch Finder</title>
</head>
<body>
    @auth
        <p>Hello, {{ Auth::user()->username }}! <a href="{{ route('logout') }}">Logout</a></p>
    @else
        <p>Hello, Guest! <a href="{{ route('login') }}">Login</a></p>
    @endauth

    <h1>Wikidata Mismatch Finder</h1>
    <p>Coming soon, to a screen near you...</p>
</body>
</html>
