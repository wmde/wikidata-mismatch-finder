<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mismatch Finder</title>
</head>
<body>
@if(!$tokens || sizeof($tokens) == 0)

No tokens found, do you want to <a href="/createToken">create</a> one? 

@else

    @foreach ($tokens as $token)
        <div id="token">
            <div id="tokenId">ID: {{ $token->id }}</div>
            <div id="tokenName">name: {{ $token->name }}</div>
            <div id="tokenAbilities">abilities: {{ implode(', ', $token->abilities) }}</div>
            <div id="tokenCreated">created at: {{ $token->created_at }}</div>
            <div id="tokenLastUsed">last used at: {{ $token->last_used_at }}</div>
            <div id="tokenRevoke"><a href="{{ route('token.revoke', [ 'id' => $token->id ] ) }}">Revoke</a></div>
        </div>
        <hr/>
    @endforeach

@endif
</body>
</html>
