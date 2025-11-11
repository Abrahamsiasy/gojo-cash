@props([
    'headers' => [],
    'rows' => [],
    'actions' => [
        'view' => false,
        'edit' => false,
        'delete' => false,
    ],
    'paginator' => null,
])

@php
    use Illuminate\Support\Arr;
    use Illuminate\Contracts\Pagination\Paginator;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;

    $resolvedActions = array_merge([
        'view' => false,
        'edit' => false,
        'delete' => false,
    ], $actions);

    $hasActions = collect($resolvedActions)->contains(true);

    $tableClasses = Arr::toCssClasses([
        'w-full text-sm text-left rtl:text-right text-gray-500',
        $attributes->get('class'),
    ]);
@endphp

<div {{ $attributes->except('class')->merge(['class' => 'relative overflow-x-auto shadow-md sm:rounded-lg text-left']) }}>
    <table class="{{ $tableClasses }}">
        @if (! empty($headers))
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="px-3 py-3 text-left">
                            {{ $header }}
                        </th>
                    @endforeach

                    @if ($hasActions)
                        <th scope="col" class="px-6 py-3 text-center">
                            {{ __('Actions') }}
                        </th>
                    @endif
                </tr>
            </thead>
        @endif

        <tbody>
            @forelse ($rows as $row)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    @foreach (Arr::get($row, 'cells', []) as $cell)
                        <td class="px-6 py-4">
                            {{ $cell }}
                        </td>
                    @endforeach

                    @if ($hasActions)
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if ($resolvedActions['view'] && Arr::get($row, 'actions.view.url'))
                                    <x-button tag="a" href="{{ Arr::get($row, 'actions.view.url') }}" class="px-3 py-2 text-xs">
                                        {{ Arr::get($row, 'actions.view.label', __('View')) }}
                                    </x-button>
                                @endif

                                @if ($resolvedActions['edit'] && Arr::get($row, 'actions.edit.url'))
                                    <x-button tag="a" href="{{ Arr::get($row, 'actions.edit.url') }}" class="px-3 py-2 text-xs">
                                        {{ Arr::get($row, 'actions.edit.label', __('Edit')) }}
                                    </x-button>
                                @endif

                                @if ($resolvedActions['delete'] && Arr::get($row, 'actions.delete.url'))
                                    <form
                                        method="POST"
                                        action="{{ Arr::get($row, 'actions.delete.url') }}"
                                        class="inline"
                                        x-data
                                        x-on:modal-confirm.window="if ($event.detail?.id === 'delete-{{ Arr::get($row, 'id') }}') { $el.submit() }"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <x-button
                                            type="danger"
                                            buttonType="button"
                                            class="px-3 py-2 text-xs"
                                            @click="$dispatch('open-modal', { id: 'delete-{{ Arr::get($row, 'id') }}' })"
                                        >
                                            {{ Arr::get($row, 'actions.delete.label', __('Delete')) }}
                                        </x-button>

                                        <x-modal
                                            id="delete-{{ Arr::get($row, 'id') }}"
                                            title="{{ Arr::get($row, 'actions.delete.title', __('Delete Company')) }}"
                                            confirmText="{{ Arr::get($row, 'actions.delete.confirmText', __('Delete')) }}"
                                            cancelText="{{ Arr::get($row, 'actions.delete.cancelText', __('Cancel')) }}"
                                            confirmColor="red"
                                        >
                                            {{ Arr::get($row, 'actions.delete.confirm', __('Are you sure you want to delete :name?', ['name' => Arr::get($row, 'name')])) }}
                                        </x-modal>

                                    </form>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + ($hasActions ? 1 : 0) }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                        {{ __('No records found.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($paginator instanceof Paginator && $paginator->hasPages())
        <div class="flex items-center justify-between mt-4 px-6 py-2">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Showing :from to :to of :total results', [
                    'from' => $paginator->firstItem() ?? 0,
                    'to' => $paginator->lastItem() ?? 0,
                    'total' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $paginator->count(),
                ]) }}
            </div>

            <div class="flex items-center gap-[5px] py-2 px-6">
                {{ $paginator->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    @endif
</div>