<div x-data="chunkedUpload()">
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
                    <x-dataset.dataset-errors
                        :errorMessage="$this->errors['message']"
                        :errorData="$this->errors['data']">
                    </x-dataset.dataset-errors>
                @endif

                {{-- Progress bar--}}
                <div x-show="lock"
                     class="flex items-center space-x-2 mt-4">
                    <x-mary-progress name="progressBar" x-bind:value="progress" max="100" class="progress-warning h-3 flex-1" />
                    <p class="text-sm font-medium text-gray-600" x-text="progressFormatted" :style="{ width: progressFormatted.length > 5 ? '50px' : '35px' }"></p>
                </div>

                {{-- Submit Button--}}
                <div class="flex gap-3">
                    <x-misc.button
                        type="submit"
                        variant="primary"
                        size="lg"
                        x-bind:disabled="lock"
                        x-bind:class="lock ? 'opacity-50 cursor-not-allowed' : ''"
                        @click="uploadChunks">
                        <x-slot:icon>
                            <x-eva-upload class="w-5 h-5"></x-eva-upload>
                        </x-slot:icon>
                        Upload Dataset
                    </x-misc.button>
                    <span class="text-gray-300 text-base flex items-center">
                        <svg x-show="lock" class="animate-spin h-5 w-5 mr-1 text-gray-300" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3-3-3h4z"></path>
                        </svg>
                        <span x-text="lock ? 'Uploading...' : ''"></span>
                    </span>
                </div>
            </div>
        </div>
    </x-modals.fixed-modal>
</div>

