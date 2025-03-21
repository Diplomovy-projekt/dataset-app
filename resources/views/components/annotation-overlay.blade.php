{{--
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
                        @if(isset($annotation['segmentation']))
                            <polygon annotation-class="{{$annotation['class']['id']}}"
                                     points="{{ $annotation['segmentation'] }}" fill="black" />
                        @else
                            <rect x="{{ $annotation['x'] }}"
                                  y="{{ $annotation['y'] }}"
                                  width="{{ $annotation['width'] }}"
                                  height="{{ $annotation['height'] }}"
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
                    @if(isset($annotation['segmentation']))
                        <polygon points="{{ $annotation['segmentation'] }}"
                                 closed="true" />
                    @else
                        <rect x="{{ $annotation['x'] }}"
                              y="{{ $annotation['y'] }}"
                              width="{{ $annotation['width'] }}"
                              height="{{ $annotation['height'] }}" />
                    @endif
                @endforeach
            </g>
        @endforeach
    </svg>
@endif
--}}
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

                <!-- Single path per class for mask instead of individual polygons -->
                @php
                    $maskPathsByClass = collect($image['annotations'])->groupBy(function ($annotation) {
                        return $annotation['class']['id'];
                    });
                @endphp

                @foreach($maskPathsByClass as $classId => $annotations)
                    <path annotation-class="{{$classId}}"
                          d="@foreach($annotations as $index => $annotation)
                               @if(isset($annotation['segmentation']))
                                   @php
                                       $points = explode(' ', $annotation['segmentation']);
                                       $pathData = 'M' . $points[0] . ',' . $points[1];
                                       for ($i = 2; $i < count($points); $i += 2) {
                                           if (isset($points[$i]) && isset($points[$i+1])) {
                                               $pathData .= ' L' . $points[$i] . ',' . $points[$i+1];
                                           }
                                       }
                                       $pathData .= ' Z';
                                       echo $pathData;
                                   @endphp
                               @else
                                   M{{ $annotation['x'] }},{{ $annotation['y'] }}
                                   L{{ $annotation['x'] + $annotation['width'] }},{{ $annotation['y'] }}
                                   L{{ $annotation['x'] + $annotation['width'] }},{{ $annotation['y'] + $annotation['height'] }}
                                   L{{ $annotation['x'] }},{{ $annotation['y'] + $annotation['height'] }} Z
                               @endif
                           @endforeach"
                          fill="black" />
                @endforeach
            </mask>
        </defs>

        <!-- Background black overlay with mask applied -->
        <rect width="100%" height="100%" fill="black" fill-opacity="0.6" mask="url(#annotation-mask-{{$image['filename']}})" />

        <!-- Optimize by using paths instead of polygons, grouped by class -->
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

                <!-- Single path element per class -->
            <path stroke="{{ $strokeColor }}"
                  stroke-width="{{$image['strokeWidth']}}"
                  fill="transparent"
                  opacity="1"
                  annotation-class="{{$classId}}"
                  d="@foreach($classAnnotations as $index => $annotation)
                       @if(isset($annotation['segmentation']))
                           @php
                               $points = explode(' ', $annotation['segmentation']);
                               $pathData = 'M' . $points[0] . ',' . $points[1];
                               for ($i = 2; $i < count($points); $i += 2) {
                                   if (isset($points[$i]) && isset($points[$i+1])) {
                                       $pathData .= ' L' . $points[$i] . ',' . $points[$i+1];
                                   }
                               }
                               $pathData .= ' Z';
                               echo $pathData;
                           @endphp
                       @else
                           M{{ $annotation['x'] }},{{ $annotation['y'] }}
                           L{{ $annotation['x'] + $annotation['width'] }},{{ $annotation['y'] }}
                           L{{ $annotation['x'] + $annotation['width'] }},{{ $annotation['y'] + $annotation['height'] }}
                           L{{ $annotation['x'] }},{{ $annotation['y'] + $annotation['height'] }} Z
                       @endif
                   @endforeach" />
        @endforeach
    </svg>
@endif
