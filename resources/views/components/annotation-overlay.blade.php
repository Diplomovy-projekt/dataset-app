@props(['image'])
@if(!empty($image))
    <svg
        id="svg-{{$image['filename']}}"
        width="100%"
        height="100%"
        viewBox="0 0 {{$image['width']}} {{$image['height']}}"
        class="absolute top-0 left-0 w-full h-full pointer-events-none rounded-lg"
        preserveAspectRatio="xMidYMid slice"
    >
        <defs>
            <mask id="annotation-mask-{{$image['filename']}}">
                <rect width="100%" height="100%" fill="white" />

                @php
                    // Process annotations once and store the results
                    $processedAnnotations = collect($image['annotations'])->map(function($annotation) {
                        $pathData = isset($annotation['segmentation'])
                            ? trim($annotation['segmentation'])
                            : "M{$annotation['x']},{$annotation['y']}L" .
                              ($annotation['x'] + $annotation['width']) . ",{$annotation['y']}L" .
                              ($annotation['x'] + $annotation['width']) . "," . ($annotation['y'] + $annotation['height']) . "L" .
                              "{$annotation['x']}," . ($annotation['y'] + $annotation['height']) . "Z";

                        return [
                            'classId' => $annotation['class']['id'],
                            'rgb' => $annotation['class']['rgb'] ?? 'default',
                            'pathData' => $pathData
                        ];
                    });

                    // Group by class for the mask
                    $annotationsByClass = $processedAnnotations->groupBy('classId');
                @endphp

                @foreach($annotationsByClass as $classId => $items)
                    <path
                        annotation-class="{{$classId}}"
                        d="@foreach($items as $item){{$item['pathData']}}@endforeach"
                        fill="black"
                    />
                @endforeach
            </mask>
        </defs>

        <!-- Background black overlay with mask applied -->
        <rect
            width="100%"
            height="100%"
            fill="black"
            fill-opacity="0.6"
            mask="url(#annotation-mask-{{$image['filename']}})"
        />

        @php
            // Group by both classId and color for the strokes
            $annotationsByClassAndColor = $processedAnnotations->groupBy(function($item) {
                return $item['classId'] . '-' . $item['rgb'];
            });
        @endphp

        @foreach($annotationsByClassAndColor as $key => $items)
            @php
                list($classId, $color) = explode('-', $key, 2);
            @endphp

            <path
                stroke="{{ $color }}"
                stroke-width="{{$image['strokeWidth']}}"
                fill="transparent"
                annotation-class="{{$classId}}"
                d="@foreach($items as $item){{$item['pathData']}}@endforeach"
            />
        @endforeach
    </svg>
@endif
