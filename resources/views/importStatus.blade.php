<x-layout>
    <div role="group" class="button-group right">
        <a class="button primary progressive" href="{{ route('store.imports-overview') }}"><img src="{{ asset('/images/download-stats.svg') }}" class="button-icon svg-white-fill" />{{__('store-layout.button:download-stats')}}</a>
    </div>
    @foreach ($imports as $import)
        <dl class="import-meta import-meta-{{ $import->status }}">
            @if($import->status == 'completed')
                <dt class="download-csv"><a class="button primary neutral" href="{{ route('store.import-results', [ $import->id ]) }}"><img src="{{ asset('/images/download-stats.svg') }}" class="button-icon svg-black-fill" />{{__('store-layout.button:download-import-csv') }}</a></dt>
            @endif
            <dt>{{ __('import-status.item:status') }}</dt>
            <dd>{{ __('import-status.item:status.' . $import->status) }}</dd>

            @if($import->error)
                <dt>{{ __('import-status.item:error') }}</dt>
                <dd>{{ $import->error->message }}</dd>
            @endif

            <dt>{{__('import-status.item:uploader')}}</dt>
            <dd>{{ $import->user->username }}</dd>

            @if($import->description)
                <dt>{{__('import-status.item:description')}}</dt>
                <dd>{{ $import->description }}</dd>
            @endif

            <dt>{{__('import-status.item:external_source')}}</dt>
            <dd>
                @if($import->external_source_url)
                    <a href="{{ $import->external_source_url }}" target="_blank">
                        {{ $import->external_source }}
                    </a>
                @else
                    {{ $import->external_source }}
                @endif
            </dd>

            <dt>{{__('import-status.item:upload_date')}}</dt>
            <dd>{{ $import->created_at->format(__('import-status.date_format')) }}</dd>

            <dt>{{__('import-status.item:expiring_date')}}</dt>
            <dd>{{ $import->expires->format(__('import-status.date_format')) }}</dd>
        </dl>
    @endforeach
</x-layout>
