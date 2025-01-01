@props([
    'image',
])

<svg id="svg-{{$image['filename']}}" width="100%" height="100%" viewBox="0 0 {{$image['viewDims']['width']}} {{$image['viewDims']['height']}}"
     class="absolute top-0 left-0 w-full h-full pointer-events-none"
     preserveAspectRatio="xMidYMid slice">
    <defs>
        <mask id="annotation-mask-{{$image['filename']}}">
            <!-- Darken the background (everything will be darkened except the annotations) -->
            <rect width="100%" height="100%" fill="white" />

            @foreach($image['annotations'] as $annotation)
                @if(isset($annotation['polygonString']))
                    <!-- Keep the area inside annotations visible by making it transparent in the mask -->
                    <polygon wire:key="mask-poly-{{time()}}"
                             annotation-class="{{$annotation['class']['id']}}"
                             points="{{ $annotation['polygonString'] }}" fill="black" />
                @else
                    <rect wire:key="mask-rect-{{time()}}" x="{{ $annotation['bbox']['x'] }}"
                          annotation-class="{{$annotation['class']['id']}}"
                          y="{{ $annotation['bbox']['y'] }}"
                          width="{{ $annotation['bbox']['width'] }}"
                          height="{{ $annotation['bbox']['height'] }}"
                          fill="black" />
                @endif
            @endforeach
        </mask>
    </defs>

    <!-- Background black overlay with reduced opacity, with the mask applied to darken the outside of annotations -->
    <rect width="100%" height="100%" fill="black" fill-opacity="0.6" mask="url(#annotation-mask-{{$image['filename']}})" />

    <!-- Annotations with original fill colors and full opacity for the current image -->
    @foreach($image['annotations'] as $annotation)
        @if(isset($annotation['polygonString']))
            <polygon wire:key="annot-poly-{{time()}}"
                     annotation-class="{{$annotation['class']['id']}}"
                     points="{{ $annotation['polygonString'] }}"
                     fill="transparent"
                     stroke="{{ $annotation['class']['color']['stroke'] }}"
                     stroke-width="{{$image['strokeWidth']}}"
                     opacity="1"
                     closed="true"
            />
        @else
            <rect wire:key="annot-rect-{{time()}}"
                  annotation-class="{{$annotation['class']['id']}}"
                  x="{{ $annotation['bbox']['x'] }}"
                  y="{{ $annotation['bbox']['y'] }}"
                  width="{{ $annotation['bbox']['width'] }}"
                  height="{{ $annotation['bbox']['height'] }}"
                  fill="transparent"
                  stroke="{{ $annotation['class']['color']['stroke'] }}"
                  stroke-width="{{$image['strokeWidth']}}"
                  opacity="1"
            />
        @endif
    @endforeach
</svg>
{{--<div>
    <svg :id="'svg-' + imageData.filename" width="100%" height="100%"
         :viewBox="'0 0 ' + imageData.viewDims.width + ' ' + imageData.viewDims.height"
         class="absolute top-0 left-0 w-full h-full pointer-events-none"
         preserveAspectRatio="xMidYMid slice">
        <defs>
            <mask :id="'annotation-mask-' + imageData.filename">
                <!-- Darken the background -->
                <rect width="100%" height="100%" fill="white" />

                <template x-for="annotation in imageData.annotations" :key="annotation.id">
                    <template x-if="annotation.polygonString">
                        <polygon :points="annotation.polygonString" fill="black"></polygon>
                    </template>
                    <template x-if="!annotation.polygonString">
                        <rect :x="annotation.bbox.x"
                              :y="annotation.bbox.y"
                              :width="annotation.bbox.width"
                              :height="annotation.bbox.height"
                              fill="black"></rect>
                    </template>
                </template>
            </mask>
        </defs>

        <!-- Background overlay with reduced opacity -->
        <rect width="100%" height="100%" fill="black" fill-opacity="0.6"
              :mask="'url(#annotation-mask-' + imageData.filename + ')'" />

        <!-- Annotations with original colors and full opacity -->
        <template x-for="annotation in imageData.annotations" :key="annotation.id">
            <template x-if="annotation.polygonString">
                <polygon :points="annotation.polygonString"
                         fill="transparent"
                         :stroke="annotation.class.color.stroke"
                         :stroke-width="imageData.strokeWidth"
                         opacity="1"
                         closed="true"></polygon>
            </template>
            <template x-if="!annotation.polygonString">
                <rect :x="annotation.bbox.x"
                      :y="annotation.bbox.y"
                      :width="annotation.bbox.width"
                      :height="annotation.bbox.height"
                      fill="transparent"
                      :stroke="annotation.class.color.stroke"
                      :stroke-width="imageData.strokeWidth"
                      opacity="1"></rect>
            </template>
        </template>
    </svg>
</div>--}}
