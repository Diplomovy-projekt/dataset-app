@props(
    [
        'annotationFormats' => [],
        'techniques' => [],
        'selectedFormat' => '',
        'selectedTechnique' => '',
    ]
)
<div>
{{--
    <div class="space-y-6 bg-base-200/50 p-6 rounded-xl shadow-sm">
--}}
    <div class="space-y-6 bg-slate-900/50 p-6 rounded-xl shadow-sm">
    {{-- Upload FILE --}}
        <div class="max-w-md">
            <label class="text-gray-400 text-sm font-medium mb-1.5 block">
                Upload Dataset (ZIP)
            </label>
            <p class="text-gray-500 text-xs mb-2">
                The dataset display name will be derived from the file name.
            </p>
            <div class="flex">
                <label for="dataset-upload" class="p-1 bg-blue-500 hover:bg-blue-600 text-gray-100 px-4 py-2 rounded-l-md cursor-pointer text-sm font-medium transition-colors">
                    CHOOSE FILE
                </label>
                <div class=" bg-[#1F2937] text-[#6B7280] px-4 py-2 rounded-r-md flex-grow text-sm" id="file-name">
                    No file chosen
                </div>
            </div>

            <input
                type="file"
                id="dataset-upload"
                name="myFile"
                accept=".zip"
                class="hidden"
                x-data
                @change="document.getElementById('file-name').textContent = $event.target.files[0] ? $event.target.files[0].name : 'No file chosen'"
            >

            <p class="text-gray-500 text-xs mt-1.5">
                @if($this->mode == 'new')
                    Upload your dataset containing images and annotations
                @elseif($this->mode == 'extend')
                    Upload additional images corresponding to your annotation technique
                    <br>
                    These will be added to the existing dataset
                @endif
            </p>
            <p x-show="fileError" x-text="fileError" class="text-red-500 text-sm mt-1"></p>

            <a target="_blank" href="{{ route('zip.format.info') }}" class="text-xs text-indigo-500 hover:underline">
                See format guidelines
            </a>

        </div>
            <!-- Select Dropdown -->
            <div class="space-y-2">
                <label class="inline-flex text-gray-400 font-medium text-sm">Select Annotation Format</label>
                <div class="relative">
                    <select
                        wire:model="selectedFormat"
                        class="text-sm w-full bg-gray-800 border border-blue-500 rounded-lg py-2 px-3 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-100 pr-8"
                    >
                        <option value="" selected>Select format</option>
                        <!-- Using $annotationFormats from your component -->
                        @foreach($annotationFormats as $format)
                            <option value="{{ $format['name'] }}">{{ $format['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-400">Choose the format used in this dataset</p>
                @error('selectedFormat')
                <span class="w-full mx-auto text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Radio Group -->
            <div class="space-y-2">
                <label class="block text-gray-400 font-medium text-sm">
                    @if($this->mode == 'new')
                        Select used annotation technique
                    @else
                        Annotation technique has to be same as the existing dataset
                    @endif
                </label>
                <div class="divide-x divide-gray-600 rounded-lg overflow-hidden border border-gray-700 bg-gray-800 inline-flex">
                    <!-- Using $this->techniques from your component -->
                    @foreach($this->techniques as $technique)
                        <label class="relative">
                            <input
                                type="radio"
                                wire:model="selectedTechnique"
                                name="technique"
                                value="{{ $technique['key'] }}"
                                class="peer sr-only"
                                @if($this->mode != 'new') disabled @endif
                            >
                            <div class="px-4 py-2 cursor-pointer flex items-center justify-center peer-checked:bg-blue-500 peer-checked:text-gray-100 text-gray-300 hover:bg-gray-600">
                                {{ $technique['value'] }}
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('selectedTechnique')
                <span class="w-full mx-auto text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>
</div>

@script
<script>
    Alpine.data('chunkedUpload', () => ({
        progress: 0,
        lock: $wire.entangle('lockUpload'),
        processing: false,
        processingCompleted: false,
        fileError: '',
        get progressFormatted() {
            return this.progress.toFixed(2) + '%';
        },
        init() {
            this.$watch('lock', (value) => {
                if (value === false) {
                    this.progress = 0;
                    this.processing = false;
                    this.processingCompleted = false;
                }
            });
        },
        uploadChunks() {
            if (this.lock) {
                return;
            }

            const fileInput = document.querySelector('input[name="myFile"]');
            if (fileInput.files[0]) {
                this.fileError = '';
                const file = fileInput.files[0];

                this.progress = 0;
                this.processing = false;
                this.processingCompleted = false;

                const uuid = this.generateUUIDv7();
                const extension = file.name.split('.').pop();
                const uniqueName = uuid + '.' + extension;

                $wire.$set('fileSize', file.size);
                $wire.$set('displayName', file.name);
                $wire.$set('uniqueName', uniqueName);
                $wire.$set('lockUpload', true);
                this.lock = true;

                this.livewireUploadChunk(file, 0).catch((err) => {
                    console.error("Upload failed with error:", err);
                    this.lock = false;
                    this.progress = 0;
                });
            } else {
                this.fileError = 'Please select a file before uploading.';
            }
        },

        async livewireUploadChunk(file, start) {
            const chunkSize = $wire.$get('chunkSize');
            const chunkEnd = Math.min(start + chunkSize, file.size);
            const chunk = file.slice(start, chunkEnd, file.type);
            const chunkFile = new File([chunk], file.name, { type: file.type });


            try {
                await new Promise((resolve, reject) => {
                    $wire.$upload(
                        'fileChunk',
                        chunkFile,
                        (resolve) => {
                        },
                        (error) => {
                            console.error("Error during chunk upload:", error);
                            $wire.$set('lockUpload', false);
                            this.processing = false;
                            this.progress = 0;
                            this.lock = false;
                            reject(error);
                        },
                        (event) => {
                            if (!this.lock) {
                                console.warn("Upload is not locked anymore, aborting progress update.");
                                return;
                            }

                            this.progress = ((start + (event.detail.progress / 100) * chunk.size) / file.size) * 100;

                            if (event.detail.progress === 100) {
                                const nextStart = chunkEnd;
                                if (nextStart < file.size) {
                                    this.livewireUploadChunk(file, nextStart);
                                } else {
                                    this.processing = true;
                                    this.progress = 100;
                                }
                            }
                        }
                    );
                });

                if (start >= file.size) {
                    this.processing = true;
                }
            } catch (error) {
                this.lock = false;
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
