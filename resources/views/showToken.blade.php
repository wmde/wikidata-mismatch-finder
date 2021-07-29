<x-layout>
@if(!$tokens || sizeof($tokens) == 0)

No token found, do you want to <a href="{{ route('token.create') }}">create</a> one?

@else

    @foreach ($tokens as $token)
    <div class="card">
        <p>Upload Permission: {{ $permission == 'Upload' ? 'Yes' : 'No' }}</p>
        <p>Created at: {{ $token->created_at }}</p>
        <p>Last used at: {{ $token->last_used_at }}</p>
        
        <div role="group" class="button-group">
            <a class="button primary progressive" href="{{ route('token.create') }}">Regenerate</a>
            <a class="button primary destructive" href="{{ route('token.revoke', [ 'id' => $token->id ] ) }}">Delete</a>
        </div>
    </div>
    @endforeach
@endif
</x-layout>
