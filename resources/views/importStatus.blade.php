<x-layout>
    @foreach ($imports as $import)
        <dl class="import-meta import-meta-{{ $import->status }}">
            <dt>{{ __('import-status.item:status') }}</dt>
            <dd>{{ __('import-status.item:status.' . $import->status) }}</dd>

            <dt>{{__('import-status.item:uploader')}}</dt>
            <dd>{{ $import->user->username }}</dd>

            <dt>{{__('import-status.item:description')}}</dt>
            <dd>{{ $import->description }}</dd>

            <dt>{{__('import-status.item:upload_date')}}</dt>
            <dd>{{ $import->created_at->format(__('import-status.date_format')) }}</dd>

            <dt>{{__('import-status.item:expiring_date')}}</dt>
            <dd>{{ $import->expires->format(__('import-status.date_format')) }}</dd>
        </dl>
    @endforeach
</x-layout>
