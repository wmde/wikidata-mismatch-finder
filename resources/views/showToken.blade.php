<x-layout>
@if(!$tokens || sizeof($tokens) == 0)

No token found, do you want to <a href="{{ route('token.create') }}">create</a> one?

@else

    @foreach ($tokens as $token)
        <div id="token">
            <div id="tokenId">ID: {{ $token->id }}</div>
            <div id="tokenName">name: {{ $token->name }}</div>
            <div id="tokenCreated">created at: {{ $token->created_at }}</div>
            <div id="tokenLastUsed">last used at: {{ $token->last_used_at }}</div>
            <div id="tokenRevoke"><a href="{{ route('token.revoke', [ 'id' => $token->id ] ) }}">Revoke</a></div>
        </div>
        <hr/>
    @endforeach
<div id="back"><a href="/">home</a></div>
@endif
</x-layout>
