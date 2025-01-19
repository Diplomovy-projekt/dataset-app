<div>
    {{--@forelse($this->datasets as $dataset)
        <div wire:key="{{$dataset['id']}}"
        x-data="{open: false}">
            <livewire:components.classes-sample :uniqueNames="$dataset['unique_name']" :selectable="true"/>
            <button @click.prevent="open = 'display-classes'">Open classes</button>
        </div>
    @empty
        <p class="text-gray-400">No datasets selected</p>
    @endforelse--}}
</div>
