<x-layout>
    @foreach ($imports as $import)
        <dl class="import-meta import-meta-{{ $import->status }}">
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
