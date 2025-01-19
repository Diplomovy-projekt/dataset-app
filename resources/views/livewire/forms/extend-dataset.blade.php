<div x-data="chunkedUpload(@this)">
    <x-modals.fixed-modal modalId="extend-dataset" class="w-1/2">
        {{--Main Form Container using MaryUI's Form Component--}}
        <div  class=" mx-auto">
            <div class="space-y-4 relative">

                {{-- Header Section--}}
                <div class="text-center space-y-2">
                    <h2 class="text-4xl font-bold bg-gradient-to-r from-primary to-primary-focus bg-clip-text text-transparent">
                        Extend Dataset
                    </h2>
                </div>

                {{-- Upload File Section--}}
                <x-forms.dataset-file-upload :annotationFormats="$annotationFormats" modalStyle="extend-dataset" />

                {{-- Erorrs--}}
                @if($errors)
                    <x-dataset-errors></x-dataset-errors>
                @endif

                {{-- Progress bar--}}
                <div class="flex items-center space-x-2 mt-4">
                    <x-mary-progress name="progressBar" x-bind:value="progress" max="100" class="progress-warning h-3 flex-1" />
                    <p class="text-sm font-medium text-gray-600" x-text="progressFormatted" :style="{ width: progressFormatted.length > 5 ? '50px' : '35px' }"></p>
                </div>

                {{-- Submit Button--}}
                <span x-text="lock ? 'Uploading...' : ''"></span>
                <x-button
                    type="submit"
                    x-bind:disabled="lock"
                    class="{{$this->lockUpload ? 'opacity-50 cursor-not-allowed' : ''}}"
                    @click="uploadChunks"
                    text="Save">
                </x-button>
            </div>
        </div>
    </x-modals.fixed-modal>
</div>

@push('scripts')
    @vite('resources/js/chunkedUpload.js')
@endpush
