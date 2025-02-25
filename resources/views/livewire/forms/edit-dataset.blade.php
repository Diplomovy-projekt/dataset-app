<div>
    <x-modals.fixed-modal modalId="edit-dataset" class="w-1/2">
        {{--Main Form Container using MaryUI's Form Component--}}
        <div  class=" mx-auto">
            <div class="space-y-4 relative">

                {{-- Header Section--}}
                <div class="text-center space-y-2">
                    <h2 class="text-4xl font-bold bg-gradient-to-r from-primary to-primary-focus bg-clip-text text-transparent">
                        Edit Dataset Info
                    </h2>
                </div>

                {{-- Format Select--}}
                <div>
                    <input type="text" wire:model="displayName" class="mb-2 h-14 w-full border border-slate-900 bg-slate-800 rounded-md p-2" placeholder="Dataset Name">
                </div>
                <x-forms.dataset-info-upload :categories="$categories" :metadataTypes="$metadataTypes"/>
                {{-- Submit Button--}}
                <x-misc.button
                    type="submit"
                    variant="primary"
                    size="lg"
                    wire:click="updateDatasetInfo">
                    Save Dataset
                </x-misc.button>
            </div>
        </div>
    </x-modals.fixed-modal>
</div>
