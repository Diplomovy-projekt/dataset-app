@props(
    [
        'annotationFormats' => [],
        'techniques' => [],
        'selectedFormat' => '',
        'selectedTechnique' => '',
        'modalStyle' => ''
    ]
)
<div>
    <div class="space-y-6 bg-base-200/50 p-6 rounded-xl shadow-sm">
        {{-- Upload FILE --}}
        <div class="max-w-md">
            <label class="text-[#9BA3AF] text-sm mb-1.5 block">
                Upload Dataset (ZIP)
            </label>

            <div class="flex">
                <label for="dataset-upload" class="bg-indigo-500 hover:bg-indigo-600 text-slate-800 px-4 py-2 rounded-l-md cursor-pointer text-sm font-medium transition-colors">
                    CHOOSE FILE
                </label>
                <div class="bg-[#1F2937] text-[#6B7280] px-4 py-2 rounded-r-md flex-grow text-sm" id="file-name">
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

            <p class="text-[#6B7280] text-xs mt-1.5">
                @if($modalStyle == 'new-upload')
                    Upload your dataset containing images and annotations
                @elseif($modalStyle == 'extend-dataset')
                    Upload additional images corresponding to your annotation technique
                    <br>
                    These will be added to the existing dataset
                @endif
            </p>
            <a target="_blank" href="{{ route('zip.format.info') }}" class="text-xs text-indigo-500 hover:underline">
                See format guidelines
            </a>

        </div>
        {{-- FORMAT SELECT --}}
        <x-mary-select
            wire:model="selectedFormat"
            label="Select Annotation Format"
            hint="Choose the format used in this dataset"
            :options="$annotationFormats"
            option-value="name"
            option-label="name"
            placeholder="Select format"/>
        {{-- TECHNIQUE SELECT --}}
        <x-mary-radio
            label="{{$modalStyle == 'new-upload' ? 'Select used annotation technique' : 'Annotation technique has to be same as the existing dataset'}}"
            :options="$this->techniques"
            option-value="key"
            option-label="value"
            wire:model="selectedTechnique"
            :disabled="$modalStyle != 'new-upload'"
        />
    </div>
</div>

@script
<script>
    Alpine.data('chunkedUpload', () => ({
        progress: 0,
        lock: $wire.entangle('lockUpload'),
        get progressFormatted() {
            return this.progress.toFixed(2) + '%';
        },

        uploadChunks() {
            // Prevent multiple uploads
            if (this.lock) {
                return;
            }

            const fileInput = document.querySelector('input[name="myFile"]');
            if (fileInput.files[0]) {
                const file = fileInput.files[0];
                this.progress = 0;

                $wire.$set('fileSize', file.size);
                $wire.$set('displayName', file.name);
                $wire.$set('uniqueName', this.generateUUIDv7() + '.' + file.name.split('.').pop());
                $wire.$set('lockUpload', true);

                this.livewireUploadChunk(file, 0).catch(() => {
                    // Handle any errors that occur during upload
                    this.lock = false;
                    this.progress = 0;
                });
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
                            $wire.$set('lockUpload', false, true);
                        },
                        (error) => {
                            $wire.$set('lockUpload', false);
                            reject(error);
                        },
                        (event) => {
                            this.progress = ((start + event.detail.progress) / file.size) * 100;

                            if (event.detail.progress == 100) {
                                start = chunkEnd;

                                if (start < file.size) {
                                    this.livewireUploadChunk(file, start);
                                }
                            }
                        }
                    );
                });
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
