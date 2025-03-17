@props([
    'name'
])

@switch($name)
    @case('dataset.review.new')
        <x-misc.highlight-card
            color="green"
            title="New Dataset Review"
            description="Review the new dataset and resolve the request."
        >
            <x-slot:icon>
                <x-myicon name="new-dataset" color="text-green-500"/>
            </x-slot:icon>
            <x-misc.button
                color="green"
                variant="primary"
                size="lg"
                @click="$dispatch('init-resolve-request',{requestId: '{{ $this->request['id'] }}'});
                         open = 'resolve-request'">
                Resolve
            </x-misc.button>
        </x-misc.highlight-card>
        @break
    @case('dataset.review.edit')
        <x-misc.highlight-card
            color="purple"
            title="Edit Dataset Info Review"
            description="Review changes about the dataset metadata and resolve the request."
        >
            <x-slot:icon>
                <x-myicon name="edit-dataset" color="text-purple-500"/>
            </x-slot:icon>
            <x-misc.button
                color="purple"
                variant="primary"
                size="lg"
                @click="$dispatch('init-resolve-request',{requestId: '{{ $this->request['id'] }}'});
                         open = 'resolve-request'">
                Resolve
            </x-misc.button>
        </x-misc.highlight-card>
        @break
    @case('dataset.review.extend')
        <x-misc.highlight-card
            color="blue"
            title="Extend Dataset Review"
            description="Review changes requested by the user to extend the dataset and resolve the request."
        >
            <x-slot:icon>
                <x-myicon name="extend-dataset" color="text-blue-500"/>
            </x-slot:icon>
            <x-misc.button
                color="blue"
                variant="primary"
                size="lg"
                @click="$dispatch('init-resolve-request',{requestId: '{{ $this->request['id'] }}'});
                         open = 'resolve-request'">
                Resolve
            </x-misc.button>
        </x-misc.highlight-card>
        @break
    @case('dataset.review.reduce')
        <x-misc.highlight-card
            color="yellow"
            title="Reduce Dataset Review"
            description="Review changes requested by the user to reduce the dataset and resolve the request."
        >
            <x-slot:icon>
                <x-myicon name="reduce-dataset" color="text-yellow-500"/>
            </x-slot:icon>
            <x-misc.button
                color="yellow"
                variant="primary"
                size="lg"
                @click="$dispatch('init-resolve-request',{requestId: '{{ $this->request['id'] }}'});
                         open = 'resolve-request'">
                Resolve
            </x-misc.button>
        </x-misc.highlight-card>
        @break
    @case('dataset.review.delete')
        <x-misc.highlight-card
            color="red"
            title="Delete Dataset Review"
            description="Review changes requested by the user to delete the dataset and resolve the request."
        >
            <x-slot:icon>
                <x-myicon name="delete-dataset" color="text-red-500"/>
            </x-slot:icon>
            <x-misc.button
                color="red"
                variant="primary"
                size="lg"
                @click="$dispatch('init-resolve-request',{requestId: '{{ $this->request['id'] }}'});
                         open = 'resolve-request'">
                Resolve
            </x-misc.button>
        </x-misc.highlight-card>
        @break
@endswitch
