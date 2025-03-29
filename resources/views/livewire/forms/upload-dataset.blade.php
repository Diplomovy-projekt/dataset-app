<div x-data="chunkedUpload()">
    <x-modals.fixed-modal modalId="uploadDataset" class="w-11/12 sm:w-4/5 md:w-3/4 lg:w-2/3 xl:w-1/2">
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

                @if($errors)
                    <x-dataset.dataset-errors
                        :errorMessage="$this->errors['message']"
                        :errorData="$this->errors['data']">
                    </x-dataset.dataset-errors>
                @endif





                <div class="space-y-4">
                    {{-- Progress Indicators --}}
                    <template x-if="lock">
                        <div class="w-full bg-gray-100 rounded-full h-4 dark:bg-gray-700 overflow-hidden">
                            <div
                                class="bg-blue-600 h-4 rounded-full transition-all duration-300 ease-in-out"
                                :style="{ width: progress + '%' }"
                                :class="{
                    'bg-blue-600': !processing,
                    'bg-green-600': processing
                }"
                            ></div>
                        </div>
                    </template>


                    {{-- Status Text --}}
                    <div x-show="lock" class="flex items-center justify-between text-sm text-gray-400">
                        <span x-text="processing ? 'Processing Dataset...' : 'Uploading Dataset...'"></span>
                        <span x-text="progressFormatted"></span>
                    </div>

                    {{-- Uploaded Text and Checkmark --}}
                    <div x-show="processing && progress === 100" class="flex items-center text-sm text-green-600">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Uploaded</span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-4 items-center">
                        {{-- Upload Button --}}
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

                        {{-- Spinner --}}
                        <template x-if="lock">
                            <div class="flex items-center space-x-2">
                                <svg
                                    class="animate-spin h-5 w-5 text-gray-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0l3 3-3 3V4a6 6 0 00-6 6H4zm2 5a8 8 0 008 8v4l3-3-3-3v4a6 6 0 006-6h4a8 8 0 01-8 8v4l-3-3 3-3v-4a8 8 0 01-8-8H2z"
                                    ></path>
                                </svg>
                            </div>
                        </template>
                    </div>
                </div>



            </div>
        </div>

    </x-modals.fixed-modal>
</div>
