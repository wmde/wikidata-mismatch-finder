<x-layout>
    @guest
    <p>{!! __('store-layout.login-request', [ 'login-url' => route('login') ]) !!}</p>
    @endguest
    @auth
        @if(!$tokens || sizeof($tokens) == 0)
        <div class="no-tokens card border">

            <div>{{__('store-layout.no-token-found')}}</div>
            <div role="group" class="button-group">
                <a class="button primary progressive" href="{{ route('api.create_token') }}">{{__('store-layout.button:create')}}</a>
            </div>
        </div>
        @else
            @if (session('flashToken'))
            <div class="token">
                    <p>{{__('store-layout.token-successfully-generated')}}: {{ session('flashToken') }}</p>
                    <div role="group" class="button-group">
                        <a class="button primary" href="{{ route('store.api-settings') }}">{{__('store-layout.button:confirm')}}</a>
                    </div>
            </div>
            @endif
            @foreach ($tokens as $token)
            <div class="token-container card border">
                <p>{{__('store-layout.upload-permission')}}: {{ $upload_permission ? __('store-layout.yes') : __('store-layout.no') }}</p>
                <p>{{__('store-layout.created-at')}}: {{ $token->created_at }}</p>
                <p>{{__('store-layout.last-used-at')}}: {{ $token->last_used_at ?? __('store-layout.token-not-used') }}</p>

                <div role="group" class="button-group right">
                    <a class="button primary progressive" href="{{ route('api.create_token') }}">{{__('store-layout.button:regenerate')}}</a>
                    <a class="button primary destructive" href="{{ route('api.revoke_token', [ 'id' => $token->id ] ) }}">{{__('store-layout.button:delete')}}</a>
                </div>
            </div>
            @endforeach
        @endif
    @endauth
</x-layout>
