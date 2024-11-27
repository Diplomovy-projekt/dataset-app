<div>
    <x-modals.fixed-modal modalId="uploadDataset">
        {{-- Main Form Container using MaryUI's Form Component --}}
        <x-mary-form wire:submit.prevent="submitUploadDataset" class=" mx-auto">
            <div class="space-y-4 relative">
                {{-- Loading Spinner (Scoped to form) --}}
                <div wire:loading.flex wire:target="submitForm" class="absolute inset-0 bg-base-200/50 backdrop-blur-sm items-center justify-center rounded-lg z-50 hidden">
                    <x-mary-loading class="w-8 h-8" />
                </div>

                {{-- Header Section --}}
                <div class="text-center space-y-2">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-primary to-primary-focus bg-clip-text text-transparent">
                        Dataset Upload
                    </h2>
                </div>

                {{-- Dataset Upload Section --}}
                <div class="space-y-6 bg-base-200/50 p-6 rounded-xl shadow-sm">
                    {{-- File Upload with loading indicator --}}
                    <div class="relative">
                        <div wire:loading.flex wire:target="datasetFile" class="absolute inset-0 bg-base-200/50 backdrop-blur-sm items-center justify-center rounded-lg z-10 hidden">
                            <x-mary-loading class="w-6 h-6" />
                        </div>
                        <x-mary-file
                            wire:model.live="datasetFile"
                            label="Upload Dataset (ZIP)"
                            hint="Upload your dataset containing images and annotations"
                            accept=".zip, .rar"
                            class="border-2 border-dashed border-base-300 hover:border-primary transition-colors"
                        />
                    </div>

                    <x-mary-select
                        wire:model="selectedFormat"
                        label="Select Annotation Format"
                        hint="Choose the format used in your dataset"
                        :options="$annotationFormats"
                        option-value="name"
                        option-label="name"
                        placeholder="Select format"/>
                    @php
                        $users = [
                            ['key' => 'Polygon', 'value' => 'Polygon'],
                            ['key' => 'Bounding box', 'value' => 'Bounding box'],
                        ];
                    @endphp

                    <x-mary-radio
                        label="Select used annotation technique"
                        :options="$users"
                        option-value="key"
                        option-label="value"
                        wire:model="annotationTechnique" />

                </div>
                {{-- Metadata Section with Dynamic Accordions --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-base-content">Metadata</h3>

                    @foreach($propertyTypes  as $propertyType)
                        <div x-data="{ open: false }" class="rounded-lg border border-base-300">
                            <button type="button" @click="open = !open" class="w-full flex justify-between items-center p-4 hover:bg-base-200 transition-colors">
                                <span class="font-medium">{{ $propertyType->name }}</span>
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
                                    @foreach($propertyType->propertyValues as $propertyValue)
                                        <x-mary-checkbox
                                            wire:model="checkedProperties.{{ $propertyValue->id }}"
                                            value="{{ $propertyValue->id }}"
                                            label="{{ $propertyValue->value }}"
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
                <x-button text="Upload Dataset" wire:loading.attr="disabled" wire:target="submitForm"/>
            </div>
        </x-mary-form>
    </x-modals.fixed-modal>
</div>
