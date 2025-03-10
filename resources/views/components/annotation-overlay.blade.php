@props([
    'image',
])
@if(!empty($image))
    <svg id="svg-{{$image['filename']}}" width="100%" height="100%" viewBox="0 0 {{$image['width']}} {{$image['height']}}"
         class="absolute top-0 left-0 w-full h-full pointer-events-none"
         preserveAspectRatio="xMidYMid slice">
        <defs>
            <mask id="annotation-mask-{{$image['filename']}}">
                <!-- Darken the background -->
                <rect width="100%" height="100%" fill="white" />

                <!-- Group all mask polygons/rects together -->
                <g>
                    @foreach($image['annotations'] as $annotation)
                        @if(isset($annotation['polygonString']))
                            <polygon annotation-class="{{$annotation['class']['id']}}"
                                     points="{{ $annotation['polygonString'] }}" fill="black" />
                        @else
                            <rect x="{{ $annotation['bbox']['x'] }}"
                                  y="{{ $annotation['bbox']['y'] }}"
                                  width="{{ $annotation['bbox']['width'] }}"
                                  height="{{ $annotation['bbox']['height'] }}"
                                  annotation-class="{{$annotation['class']['id']}}"
                                  fill="black" />
                        @endif
                    @endforeach
                </g>
            </mask>
        </defs>

        <!-- Background black overlay with mask applied -->
        <rect width="100%" height="100%" fill="black" fill-opacity="0.6" mask="url(#annotation-mask-{{$image['filename']}})" />

        <!-- Optimize by grouping annotations by class -->
        @php
            $annotationsByClass = collect($image['annotations'])->groupBy(function ($annotation) {
                return $annotation['class']['id'] . '-' . ($annotation['class']['rgb'] ?? 'default');
            });
        @endphp

        @foreach($annotationsByClass as $classKey => $classAnnotations)
            @php
                // Extract the class RGB from the first annotation in the group
                $firstAnnotation = $classAnnotations->first();
                $strokeColor = $firstAnnotation['class']['rgb'];
                $classId = $firstAnnotation['class']['id'];
            @endphp

                <!-- Group element for each class to reduce DOM nodes -->
            <g stroke="{{ $strokeColor }}"
               stroke-width="{{$image['strokeWidth']}}"
               fill="transparent"
               opacity="1"
               annotation-class="{{$classId}}">

                @foreach($classAnnotations as $annotation)
                    @if(isset($annotation['polygonString']))
                        <polygon points="{{ $annotation['polygonString'] }}"
                                 closed="true" />
                    @else
                        <rect x="{{ $annotation['bbox']['x'] }}"
                              y="{{ $annotation['bbox']['y'] }}"
                              width="{{ $annotation['bbox']['width'] }}"
                              height="{{ $annotation['bbox']['height'] }}" />
                    @endif
                @endforeach
            </g>
        @endforeach
    </svg>
@endif
