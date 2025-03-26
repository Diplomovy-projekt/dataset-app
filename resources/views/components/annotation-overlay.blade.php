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
            @foreach($image['annotations'] as $index => $group)
                <path
                    id="path-{{$loop->parent->index ?? 0}}-{{$index}}"
                    d="{{$group['maskPathData']}}"
                />
            @endforeach

            <mask id="mask-{{$loop->index ?? 0}}">
                <rect width="100%" height="100%" fill="white" />

                @foreach($image['annotations'] as $index => $group)
                    <use
                        xlink:href="#path-{{$loop->parent->index ?? 0}}-{{$index}}"
                        annotation-class="{{$group['classId']}}"
                        fill="black"
                    />
                @endforeach
            </mask>
        </defs>

        <rect
            width="100%"
            height="100%"
            fill="black"
            fill-opacity="0.6"
            mask="url(#mask-{{$loop->index ?? 0}})"
        />

        @foreach($image['annotations'] as $index => $group)
            <use
                xlink:href="#path-{{$loop->parent->index ?? 0}}-{{$index}}"
                stroke="{{$group['rgb']}}"
                stroke-width="{{$image['strokeWidth']}}"
                fill="transparent"
                annotation-class="{{$group['classId']}}"
            />
        @endforeach
    </svg>
@endif
