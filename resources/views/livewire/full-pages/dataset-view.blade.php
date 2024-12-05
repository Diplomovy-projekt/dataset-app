<div>
    <x-containers.grid-card-container size="large">
        {{-- Display images in a grid container --}}
        @foreach ($images as $image)
            <div class="relative grid-card" wire:key="{{$image['img_filename']}}">
                <img id="{{$image['img_filename']}}" src="{{ asset('storage/datasets/'.$this->dataset->unique_name. '/' . $image['img_filename']) }}" alt="Image"
                     class="relative w-full h-auto lazy block">
                {{--<div id="konva-{{ $image['img_filename'] }}"
                     class="absolute inset-0">
                    <!-- Konva canvas for this image -->
                </div>--}}
                <svg id="svg-{{$image['img_filename']}}" width="100%" height="100%" class="absolute top-0 left-0 w-full h-full pointer-events-none"></svg>
            </div>
        @endforeach
    </x-containers.grid-card-container>

    {{-- Load More Button --}}
    @if ($imagesLoaded < $this->dataset->images()->count())
        <div class="text-center mt-4">
            <button wire:click="loadMore" class="btn btn-primary">Load More</button>
        </div>
    @endif
</div>

@script
    <script>
        console.log('Images loaded: ');
        let imagesData = $wire.$get('images');
        imagesData.forEach(function(image){
            let imageElement = document.getElementById(image.img_filename);
            let svg = document.getElementById('svg-' + image.img_filename);
            let annotations = image.annotations;

            svg.setAttribute('viewBox', `0 0 ${imageElement.clientWidth} ${imageElement.clientHeight}`);

            annotations.forEach(function(annotation){
                let points = JSON.parse(annotation.segmentation);
                let polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                let pointsString = '';
                points.forEach(function(point, index){
                    pointsString += (index % 2 === 0 ? point * svg.clientWidth : point * svg.clientHeight) + ',';
                });
                pointsString = pointsString.slice(0, -1); // Removes the last character

                polygon.setAttribute('points', pointsString);
                polygon.setAttribute('fill', 'rgba(90, 131, 0, 0.4)');
                polygon.setAttribute('stroke', 'rgb(0, 210, 255)');
                polygon.setAttribute('stroke-width', '1');
                polygon.setAttribute('closed', 'true');
                svg.appendChild(polygon);
            });
        })
        /*let stages = [];
        imagesData.forEach(function(image) {
            //var imageElement = $('#' + image.img_filename);
            var imageElement = document.getElementById(image.img_filename);
            console.log("CLIENT: ",imageElement.clientWidth,);

            if (imageElement) {
                var containerId = 'konva-' + image.img_filename;
                var stage = new Konva.Stage({
                    container: containerId,
                    width: imageElement.clientWidth,
                    height: imageElement.clientHeight
                });

                var layer = new Konva.Layer();
                // Loop through the annotations for the current image and draw shapes
                image.annotations.forEach(function(annotation) {
                    let points = JSON.parse(annotation.segmentation);

                    var pixelPoints = points.map(function(value, index) {
                        return index % 2 === 0 ? value * imageElement.clientWidth : value * imageElement.clientHeight;
                    });
                    var shape = new Konva.Line({
                        points: pixelPoints, // Annotation points
                        fill: 'rgba(90, 131, 0, 0.4)', // Fill color (default if not set)
                        stroke: 'rgb(0, 210, 255)',
                        strokeWidth: 1,
                        closed: true,
                    });

                    layer.add(shape);
                });

                // Add the layer to the stage
                stage.add(layer);
                stages.push(stage);
            }
        });
        function fitStageIntoParentContainer() {
            stages.forEach(function(stage) {
                var container = document.getElementById(stage.attrs.container.id);

                var containerWidth = container.offsetWidth;

                var scale = containerWidth / stage.attrs.width;

                stage.width(stage.attrs.width * scale);
                stage.height(stage.attrs.height * scale);
                stage.scale({ x: scale, y: scale });

                stage.find('Shape').forEach(function(shape) {
                    shape.scale({ x: scale, y: scale }); // Scale the shape
                    shape.position({
                        x: shape.x() * scale,
                        y: shape.y() * scale,
                    }); // Adjust the position of the shape
                });

                stage.draw();
            })
        }

        fitStageIntoParentContainer();
        window.addEventListener('resize', fitStageIntoParentContainer);*/
    </script>
@endscript
