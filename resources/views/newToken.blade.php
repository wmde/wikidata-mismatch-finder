<x-layout>
    <div>{{__('store-layout.token-successfully-generated')}}: {{ $newToken }}</div>
        <div role="group" class="button-group">
        <a class="button primary" href="{{ route('token') }}">{{__('store-layout.button:confirm')}}</a>
        {{-- TODO: fix this --}}
        {{-- @include('showToken') --}}
    </div>
</x-layout>