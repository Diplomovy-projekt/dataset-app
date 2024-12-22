@props([
    'originData'
])
<div class="flex flex-wrap justify-around  p-4">
    {{-- Century Column --}}
    @foreach($originData as $data)
        <div>
            <h2 class="text-xl font-bold text-gray-200 mb-4">{{$data['type']['name']}}</h2>
            <div class="space-y-3">
                @forelse($data['values'] as $value)
                    <label class="block bg-gray-700 text-gray-200 rounded-lg p-3 flex items-center hover:bg-gray-600 transition duration-300 ease-in-out">
                        <input
                            type="checkbox"
                            name="century[]"
                            wire:model="selectedOriginData.{{$value['id']}}"
                            value="{{ $value['id'] }}"
                            class="form-checkbox h-5 w-5 text-indigo-600 mr-3 bg-gray-800 border-transparent focus:border-transparent focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500"
                        >
                        <span class="text-sm font-medium">{{ $value['value'] }}</span>
                    </label>
                @empty
                    <p class="text-gray-400">No centuries found</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
