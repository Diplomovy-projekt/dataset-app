<div x-data="chunkedUpload"
>
    <x-modals.fixed-modal modalId="uploadDataset" class="w-1/2">
        {{-- Main Form Container using MaryUI's Form Component --}}
        <div  class=" mx-auto">
            <div class="space-y-4 relative">

                {{-- Header Section --}}
                <div class="text-center space-y-2">
                    <h2 class="text-4xl font-bold bg-gradient-to-r from-primary to-primary-focus bg-clip-text text-transparent">
                        Dataset Upload
                    </h2>
                </div>

                {{-- Dataset Upload Section --}}
                <div class="space-y-6 bg-base-200/50 p-6 rounded-xl shadow-sm">
                    <x-mary-file
                        name="myFile"
                        label="Upload Dataset (ZIP)"
                        hint="Upload your dataset containing images and annotations"
                        accept=".zip"
                        class="border-2 border-dashed border-base-300 hover:border-primary transition-colors"
                    />

                    <x-mary-select
                        wire:model="selectedFormat"
                        label="Select Annotation Format"
                        hint="Choose the format used in your dataset"
                        :options="$annotationFormats"
                        option-value="name"
                        option-label="name"
                        placeholder="Select format"/>
                    <x-mary-radio
                        label="Select used annotation technique"
                        :options="$this->techniques"
                        option-value="key"
                        option-label="value"
                        wire:model="selectedTechnique" />

                </div>
                {{-- Categories --}}
                <div class="space-y-6">
                    <div x-data="{ open: false }" class="rounded-lg border border-base-300">
                        <button type="button" @click="open = !open" class="w-full flex justify-between items-center p-4 hover:bg-base-200 transition-colors">
                            <span class="font-medium">Categories</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5 transform transition-transform"
                                 :class="{ 'rotate-180': open }"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open"
                             x-collapse
                             class="border-t border-base-300">
                            <div class="p-4 space-y-3">
                                @foreach($categories as $category)
                                    <x-mary-checkbox
                                        wire:model="selectedCategories"
                                        value="{{ $category->id }}"
                                        label="{{ $category->name }}"
                                    />
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Metadata Section with Dynamic Accordions --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-base-content">Metadata</h3>

                    @foreach($metadataTypes  as $metadataType)
                        <div x-data="{ open: false }" class="rounded-lg border border-base-300">
                            <button type="button" @click="open = !open" class="w-full flex justify-between items-center p-4 hover:bg-base-200 transition-colors">
                                <span class="font-medium">{{ $metadataType->name }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5 transform transition-transform"
                                     :class="{ 'rotate-180': open }"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-collapse
                                 class="border-t border-base-300">
                                <div class="p-4 space-y-3">
                                    @foreach($metadataType->metadataValues as $metadataValue)
                                        <x-mary-checkbox
                                            wire:model="selectedMetadata.{{ $metadataValue->id }}"
                                            value="{{ $metadataValue->id }}"
                                            label="{{ $metadataValue->value }}"
                                        />
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <x-mary-textarea
                    label="Description"
                    wire:model="description"
                    rows="1"
                    inline />

                {{-- Submit Button --}}
                <x-button
                    @click="uploadChunks"
                    x-bind:disabled="isUploading"
                    x-bind:class="{ 'opacity-50 cursor-not-allowed': isUploading }"
                    text="Upload Dataset">
                    <span x-show="!isUploading">Upload Dataset</span>
                    <span x-show="isUploading">Uploading...</span>
                </x-button>
                <div class="flex items-center space-x-2 mt-4">
                    <!-- MaryUI Progress Bar -->
                    <x-mary-progress name="progressBar" x-bind:value="progress" max="100" class="progress-warning h-3 flex-1" />

                    <!-- Percentage Text -->
                    <p class="text-sm font-medium text-yellow-600" x-text="progressFormatted" :style="{ width: progressFormatted.length > 5 ? '50px' : '35px' }"></p>
                </div>
            </div>
        </div>
    </x-modals.fixed-modal>
</div>

@script
    <script>
        Alpine.data('chunkedUpload', () => ({
            progress: 0,
            isUploading: false, // Add upload state tracking

            get progressFormatted() {
                return this.progress.toFixed(2) + '%';
            },

            uploadChunks() {
                // Prevent multiple uploads
                if (this.isUploading) {
                    console.log('Upload already in progress');
                    return;
                }

                const fileInput = document.querySelector('input[name="myFile"]');
                if (fileInput.files[0]) {
                    const file = fileInput.files[0];
                    this.progress = 0;
                    this.isUploading = true; // Set upload state to true

                    $wire.$set('fileSize', file.size, true);
                    $wire.$set('displayName', file.name, true);
                    $wire.$set('uniqueName', this.generateUUIDv7() + '.' + file.name.split('.').pop(), true);

                    this.livewireUploadChunk(file, 0).catch(() => {
                        // Handle any errors that occur during upload
                        this.isUploading = false;
                        this.progress = 0;
                    });
                }
            },

            async livewireUploadChunk(file, start) {
                console.log('Uploading chunk', start);
                const chunkSize = $wire.$get('chunkSize');
                const chunkEnd = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, chunkEnd, file.type);
                const chunkFile = new File([chunk], file.name, { type: file.type });

                try {
                    await new Promise((resolve, reject) => {
                        $wire.$upload(
                            'fileChunk',
                            chunkFile,
                            () => resolve(), // finish
                            (error) => {
                                console.error('Upload error:', error);
                                this.isUploading = false; // Reset on error
                                reject(error);
                            },
                            (event) => {
                                console.log(event.detail.progress);
                                this.progress = ((start + event.detail.progress) / file.size) * 100;

                                if (event.detail.progress == 100) {
                                    start = chunkEnd;

                                    if (start < file.size) {
                                        this.livewireUploadChunk(file, start);
                                    } else {
                                        // Upload is complete
                                        this.isUploading = false;
                                    }
                                }
                            }
                        );
                    });
                } catch (error) {
                    this.isUploading = false;
                    throw error;
                }
            },

            generateUUIDv7() {
                const timestamp = Date.now();
                const randomBytes = crypto.getRandomValues(new Uint8Array(10));

                let uuid = timestamp.toString(16).padStart(12, '0');
                uuid += '-';
                uuid += (Math.floor(Math.random() * 0x1000)).toString(16).padStart(4, '0');
                uuid += '-7';
                uuid += (Math.floor(Math.random() * 0x1000)).toString(16).padStart(3, '0');
                uuid += '-';
                uuid += (Math.floor(Math.random() * 0x4000) + 0x8000).toString(16).padStart(4, '0');
                uuid += '-';

                for (let i = 0; i < randomBytes.length; i++) {
                    uuid += randomBytes[i].toString(16).padStart(2, '0');
                }

                return uuid;
            }
        }));
    </script>
@endscript
