@props([
    'headers' => [],
    'rows' => [],
    'actions' => [
        'view' => false,
        'edit' => false,
        'delete' => false,
    ],
    'paginator' => null,
    'model' => ''
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
                        {{-- <th scope="col" class="px-6 py-3 text-center"> --}}
                            {{-- {{ __('Actions') }} --}}
                        {{-- </th> --}}
                    @endif
                </tr>
            </thead>
        @endif

        <tbody>
            @forelse ($rows as $row)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    @foreach (Arr::get($row, 'cells', []) as $cell)
                        <td class="px-6 py-4">
                            @if (is_array($cell) && isset($cell['html']))
                                {!! $cell['html'] !!}
                            @elseif (is_array($cell) && isset($cell['raw']))
                                {!! $cell['raw'] !!}
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach

                    @if ($hasActions)
                        @php
                            $rowId = Arr::get($row, 'id');
                            $modelInstance = Arr::get($row, 'model');
                            $user = auth()->user();
                            
                            // Check permissions
                            $canView = false;
                            $canEdit = false;
                            $canDelete = false;
                            
                            if ($user) {
                                if ($resolvedActions['view'] && Arr::get($row, 'actions.view.url')) {
                                    try {
                                        $canView = $modelInstance ? $user->can('view', $modelInstance) : $user->can('view ' . strtolower($model));
                                    } catch (\Exception $e) {
                                        $canView = $user->can('view ' . strtolower($model));
                                    }
                                }
                                
                                if ($resolvedActions['edit'] && Arr::get($row, 'actions.edit.url')) {
                                    try {
                                        $canEdit = $modelInstance ? $user->can('update', $modelInstance) : $user->can('edit ' . strtolower($model));
                                    } catch (\Exception $e) {
                                        $canEdit = $user->can('edit ' . strtolower($model));
                                    }
                                }
                                
                                if ($resolvedActions['delete'] && Arr::get($row, 'actions.delete.url')) {
                                    try {
                                        $canDelete = $modelInstance ? $user->can('delete', $modelInstance) : $user->can('delete ' . strtolower($model));
                                    } catch (\Exception $e) {
                                        $canDelete = $user->can('delete ' . strtolower($model));
                                    }
                                }
                            }
                            
                            $hasAnyAction = $canView || $canEdit || $canDelete;
                        @endphp
                        
                        @if ($hasAnyAction)
                            <td class="px-4 py-3">
                                <div x-data="{ open: false }" class="relative flex items-center justify-end">
                                    <button 
                                        @click="open = !open" 
                                        @click.away="open = false"
                                        type="button"
                                        class="inline-flex items-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 p-1.5 text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                                    >
                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    </button>

                                    <div 
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-95"
                                        class="absolute right-0 z-10 w-44 mt-2 bg-white rounded-lg divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600"
                                        style="display: none;"
                                    >
                                        <ul class="py-1 text-sm" aria-labelledby="dropdown-button-{{ $rowId }}">
                                            @if ($canEdit)
                                                <li>
                                                    <a 
                                                        href="{{ Arr::get($row, 'actions.edit.url') }}"
                                                        class="flex w-full items-center py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-gray-700 dark:text-gray-200"
                                                    >
                                                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                                        </svg>
                                                        {{ Arr::get($row, 'actions.edit.label', __('Edit')) }}
                                                    </a>
                                                </li>
                                            @endif
                                            
                                            @if ($canView)
                                                <li>
                                                    <a 
                                                        href="{{ Arr::get($row, 'actions.view.url') }}"
                                                        class="flex w-full items-center py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-gray-700 dark:text-gray-200"
                                                    >
                                                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" />
                                                        </svg>
                                                        {{ Arr::get($row, 'actions.view.label', __('View')) }}
                                                    </a>
                                                </li>
                                            @endif
                                            
                                            @if ($canDelete)
                                                <li>
                                                    <form
                                                        method="POST"
                                                        action="{{ Arr::get($row, 'actions.delete.url') }}"
                                                        class="w-full"
                                                        x-data
                                                        x-on:modal-confirm.window="if ($event.detail?.id === 'delete-{{ $rowId }}') { $el.submit() }"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button 
                                                            type="button"
                                                            @click="$dispatch('open-modal', { id: 'delete-{{ $rowId }}' })"
                                                            class="flex w-full items-center py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 text-red-500 dark:hover:text-red-400"
                                                        >
                                                            <svg class="w-4 h-4 mr-2" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                                <path fill-rule="evenodd" clip-rule="evenodd" fill="currentColor" d="M6.09922 0.300781C5.93212 0.30087 5.76835 0.347476 5.62625 0.435378C5.48414 0.523281 5.36931 0.649009 5.29462 0.798481L4.64302 2.10078H1.59922C1.36052 2.10078 1.13161 2.1956 0.962823 2.36439C0.79404 2.53317 0.699219 2.76209 0.699219 3.00078C0.699219 3.23948 0.79404 3.46839 0.962823 3.63718C1.13161 3.80596 1.36052 3.90078 1.59922 3.90078V12.9008C1.59922 13.3782 1.78886 13.836 2.12643 14.1736C2.46399 14.5111 2.92183 14.7008 3.39922 14.7008H10.5992C11.0766 14.7008 11.5344 14.5111 11.872 14.1736C12.2096 13.836 12.3992 13.3782 12.3992 12.9008V3.90078C12.6379 3.90078 12.8668 3.80596 13.0356 3.63718C13.2044 3.46839 13.2992 3.23948 13.2992 3.00078C13.2992 2.76209 13.2044 2.53317 13.0356 2.36439C12.8668 2.1956 12.6379 2.10078 12.3992 2.10078H9.35542L8.70382 0.798481C8.62913 0.649009 8.5143 0.523281 8.37219 0.435378C8.23009 0.347476 8.06631 0.30087 7.89922 0.300781H6.09922ZM4.29922 5.70078C4.29922 5.46209 4.39404 5.23317 4.56282 5.06439C4.73161 4.8956 4.96052 4.80078 5.19922 4.80078C5.43791 4.80078 5.66683 4.8956 5.83561 5.06439C6.0044 5.23317 6.09922 5.46209 6.09922 5.70078V11.1008C6.09922 11.3395 6.0044 11.5684 5.83561 11.7372C5.66683 11.906 5.43791 12.0008 5.19922 12.0008C4.96052 12.0008 4.73161 11.906 4.56282 11.7372C4.39404 11.5684 4.29922 11.3395 4.29922 11.1008V5.70078ZM8.79922 4.80078C8.56052 4.80078 8.33161 4.8956 8.16282 5.06439C7.99404 5.23317 7.89922 5.46209 7.89922 5.70078V11.1008C7.89922 11.3395 7.99404 11.5684 8.16282 11.7372C8.33161 11.906 8.56052 12.0008 8.79922 12.0008C9.03791 12.0008 9.26683 11.906 9.43561 11.7372C9.6044 11.5684 9.69922 11.3395 9.69922 11.1008V5.70078C9.69922 5.46209 9.6044 5.23317 9.43561 5.06439C9.26683 4.8956 9.03791 4.80078 8.79922 4.80078Z" />
                                                            </svg>
                                                            {{ Arr::get($row, 'actions.delete.label', __('Delete')) }}
                                                        </button>

                                                        <x-modal
                                                            id="delete-{{ $rowId }}"
                                                            title="{{ Arr::get($row, 'actions.delete.title', __('Delete :model', ['model' => $model])) }}"
                                                            confirmText="{{ Arr::get($row, 'actions.delete.confirmText', __('Delete')) }}"
                                                            cancelText="{{ Arr::get($row, 'actions.delete.cancelText', __('Cancel')) }}"
                                                            confirmColor="red"
                                                        >
                                                            {{ Arr::get($row, 'actions.delete.confirm', __('Are you sure you want to delete :name?', ['name' => Arr::get($row, 'name')])) }}
                                                        </x-modal>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        @else
                            <td class="px-4 py-3"></td>
                        @endif
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
