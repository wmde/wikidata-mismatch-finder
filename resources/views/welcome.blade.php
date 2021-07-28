<x-layout>
    @auth
     DONT know how to add the redirect here!
    @else
        <p>Please <a href="{{ route('login') }}">log in</a> to access the API token.</p>
    @endauth
</x-layout>
