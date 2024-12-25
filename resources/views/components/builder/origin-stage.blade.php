@props([
    'originData'
])
<div class="flex flex-wrap justify-around p-4">
    @foreach($originData as $data)
        <div class="w-80" x-data="{ skip: false }">
            <div class="bg-gray-800 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-200">{{$data['type']['name']}}</h2>
                    <label class="flex items-center space-x-3 text-gray-400 hover:text-gray-300 cursor-pointer">
                        <span class="text-sm">Skip</span>
                        <div class="relative inline-flex items-center">
                            <input
                                type="checkbox"
                                class="sr-only peer"
                                x-model="skip"
                                value="{{$data['type']['id']}}"
                                wire:model="skipTypes"
                            >
                            <div class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="space-y-3" x-show="!skip" x-transition>

            </div>@forelse($data['values'] as $value)
                <label class="block bg-gray-700 text-gray-200 rounded-lg p-3 flex items-center hover:bg-gray-600 transition duration-300 ease-in-out">
                    <input
                        type="checkbox"
                        name="century[]"
                        wire:model="selectedOriginData"
                        value="{{ json_encode(['value' => $value['id'], 'type' => $data['type']['id']]) }}"
                        class="form-checkbox h-5 w-5 text-indigo-600 mr-3 bg-gray-800 border-transparent focus:border-transparent focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500"
                    >
                    <span class="text-sm font-medium">{{ $value['value'] }}</span>
                </label>
            @empty
                <p class="text-gray-400">No data found for {{$data['type']['name']}}</p>
            @endforelse
        </div>
    @endforeach
</div>
