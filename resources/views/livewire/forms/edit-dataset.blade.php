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

                <x-forms.dataset-info-upload :categories="$categories" :metadataTypes="$metadataTypes"/>
                {{-- Submit Button--}}
                <x-button
                    wire:click="updateDatasetInfo"
                    x-bind:disabled="isUploading"
                    x-bind:class="{ 'opacity-50 cursor-not-allowed': isUploading }"
                    text="Save">
                    <span x-show="!isUploading">Save</span>
                    <span x-show="isUploading">Saving...</span>
                </x-button>
            </div>
        </div>
    </x-modals.fixed-modal>
</div>
