@props([
    'table' => [],
    'items'
])
<x-tables.wrapper :table="$table">
    @foreach($this->{$items} as $request)
        <x-tables.table-row id="admin-dataset-accepted-request-{{ $request['id'] }}">
            {{-- Dataset --}}
            <x-tables.table-cell>
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500/10 p-2 rounded-lg">
                        <x-icon name="o-folder" class="w-5 h-5 text-blue-400" />
                    </div>
                    @if ($request['dataset'])
                        <a href="{{ route('dataset.show', ['uniqueName' => $request['dataset']['unique_name']])}}"
                           wire:navigate
                           class="text-gray-200">
                            {{ $request['dataset']['display_name'] }}
                        </a>
                    @else
                        <span class="text-gray-500">Dataset not available</span> <!-- Fallback text -->
                    @endif
                </div>
            </x-tables.table-cell>

            {{-- Type --}}
            <x-tables.table-cell>
                @php
                    $color = match($request['type'] ?? '') {
                        'new' => 'green',
                        'extend' => 'blue',
                        'reduce' => 'yellow',
                        'delete' => 'red',
                        'edit' => 'purple',
                    };
                @endphp

                <x-misc.tag text="{{ ucfirst($request['type']) }} Dataset" color="{{ $color }}" />
            </x-tables.table-cell>
            {{-- Status --}}
            <x-tables.table-cell class="w-1/6">
                @php
                    $color = match($request['status'] ?? '') {
                        'approved' => 'green',
                        'rejected' => 'red',
                        'pending' => 'yellow',
                    };
                @endphp

                <x-misc.tag text="{{ ucfirst($request['status']) }}" color="{{ $color }}" />
            </x-tables.table-cell>

            {{-- Reviewed By --}}
            <x-tables.table-cell>
                <span class="text-gray-200">
                    @if(isset($request['reviewed_by']))
                        {{ $request['reviewer']['email'] }}
                    @else
                        <span class="text-gray-400">Not reviewed yet</span>
                    @endif
                </span>
            </x-tables.table-cell>

            {{-- Comment --}}
            <x-tables.table-cell>
                @if(!empty($request['comment']))
                    <div class="relative" x-data="{
                            showFullComment: false,
                            isCommentTruncated: function() {
                                return this.$refs.commentText.scrollWidth > this.$refs.commentText.clientWidth;
                            }
                        }" x-init="$nextTick(() => isCommentTruncated = isCommentTruncated())">
                        <div class="flex items-center cursor-pointer" @click="showFullComment = !showFullComment">
                            <span x-ref="commentText" class="block truncate max-w-[200px] text-gray-200">{{ $request['comment'] }}</span>
                            <x-icon x-show="isCommentTruncated" name="o-ellipsis-horizontal" class="w-4 h-4 text-gray-400 ml-1 flex-shrink-0" />
                        </div>
                        <div x-show="showFullComment"
                             class="absolute z-10 bg-gray-800 border border-gray-700 p-3 rounded-md shadow-lg max-w-md"
                             @click.away="showFullComment = false">
                            <span class="text-gray-200 block whitespace-normal break-words mt-2">{{ $request['comment'] }}</span>
                            <button @click="showFullComment = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-200">
                                <x-icon name="o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @else
                    <span class="text-gray-400 italic">No comment</span>
                @endif
            </x-tables.table-cell>

            {{-- Requested At --}}
            <x-tables.table-cell>
                <span class="text-gray-200  break-keep whitespace-nowrap">{{ \Carbon\Carbon::parse($request['created_at'])->format('M d, Y, H:i') }}</span>
            </x-tables.table-cell>
        </x-tables.table-row>
    @endforeach
</x-tables.wrapper>
