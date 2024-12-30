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
            label="{{$modalStyle == 'new-upload' ? 'Select used annotation technique' : 'Annotation technique can`t be changed during edit'}}"
            :options="$this->techniques"
            option-value="key"
            option-label="value"
            wire:model="selectedTechnique"
            :disabled="$modalStyle != 'new-upload'"
        />
    </div>
</div>
