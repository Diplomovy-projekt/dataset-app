<div x-data="annotationSelector()" class="p-4  rounded-lg">
    @php
        $dataset['stats'] = [
            'numImages' => 24,
            'numAnnotations' => 156,
            'numClasses' => 12,
        ];
        $totalDatasetCount = 6;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @foreach (App\Configs\AppConfig::ANNOTATION_TECHNIQUES as $key => $label)
            <div class="bg-slate-800 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                 :class="selected === '{{ $label }}' ? 'border-blue-500' : 'border-slate-700'"
                 @click="selected = '{{ $label }}'">
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-200">{{ $label }}</h3>
                        @if($label == App\Configs\AppConfig::ANNOTATION_TECHNIQUES['POLYGON'])
                            <p class="text-sm text-gray-400 mb-1">{{ $this->polygonDatasetsStats['numDatasets'] }} datasets</p>
                            <x-dataset.dataset-stats :stats="$this->polygonDatasetsStats" class="text-base" svgSize="w-5 h-5"/>
                        @else
                            <p class="text-sm text-gray-400 mb-1">{{ $this->allDatasetsStats['numDatasets'] }} datasets</p>
                            <x-dataset.dataset-stats :stats="$this->allDatasetsStats" class="text-base" svgSize="w-5 h-5"/>
                        @endif
                    </div>
                    <div class="relative inline-flex items-center">
                        <input type="radio" class="sr-only peer" name="annotation_type" id="{{ $label }}" value="{{ $label }}"  x-model="selected">
                        <div class="w-6 h-6 bg-slate-700 rounded-full flex items-center justify-center peer-checked:bg-blue-600">
                            <div class="w-3 h-3 rounded-full bg-white" x-show="selected === '{{ $label }}'"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="w-fit mt-4 p-3 py-2 bg-slate-800 text-sm text-gray-300 rounded-lg border border-slate-700">
        <p class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Bounding Box technique includes all datasets since polygon annotations can be converted into bounding boxes.
        </p>
    </div>
</div>

@script
<script>
    Alpine.data('annotationSelector', () => ({
        selected: null,
        init() {
            this.selected = this.$wire.get('selectedAnnotationTechnique');

            this.$watch('selected', value => {
                this.$wire.set('selectedAnnotationTechnique', value);
            });
        }
    }));
</script>
@endscript
