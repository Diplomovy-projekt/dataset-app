<div x-data="chunkedUpload(@this)">
    <x-modals.fixed-modal modalId="uploadDataset" class="w-1/2">
         {{--Main Form Container using MaryUI's Form Component--}}
        <div  class=" mx-auto">
            <div class="space-y-4 relative">

                {{-- Header Section--}}
                <div class="text-center space-y-2">
                    <h2 class="text-4xl font-bold bg-gradient-to-r from-primary to-primary-focus bg-clip-text text-transparent">
                        Dataset upload
                    </h2>
                </div>

                {{-- Upload File Section--}}
                <x-forms.dataset-file-upload :annotationFormats="$annotationFormats" modalStyle="new-upload" />

                {{-- Format Select--}}

                <x-forms.dataset-info-upload :categories="$categories" :metadataTypes="$metadataTypes"/>
                {{-- Submit Button--}}
                <div class="flex items-center space-x-2 mt-4">
                    <!-- MaryUI Progress Bar -->
                    <x-mary-progress name="progressBar" x-bind:value="progress" max="100" class="progress-warning h-3 flex-1" />

                    <!-- Percentage Text -->
                    <p class="text-sm font-medium text-gray-600" x-text="progressFormatted" :style="{ width: progressFormatted.length > 5 ? '50px' : '35px' }"></p>
                </div>
                @if($errors)
                    <x-dataset-errors></x-dataset-errors>
                @endif
                <x-button
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    @click="uploadChunks"
                    x-bind:disabled="isUploading"
                    x-bind:class="{ 'opacity-50 cursor-not-allowed': isUploading }"
                    text="Save">
                    <span x-show="!isUploading">Upload Dataset</span>
                    <span x-show="isUploading">Uploading...</span>
                </x-button>
            </div>
        </div>
    </x-modals.fixed-modal>
</div>

@push('scripts')
    @vite('resources/js/chunkedUpload.js')
@endpush
