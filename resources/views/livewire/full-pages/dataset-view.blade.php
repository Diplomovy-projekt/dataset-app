<div>
    <x-containers.grid-card-container size="large">
        {{-- Display images in a grid container --}}
        @foreach ($images as $image)
            <div class="relative grid-card" wire:key="{{$image['img_filename']}}">
                <img id="{{$image['img_filename']}}" src="{{ asset('storage/datasets/'.$this->dataset->unique_name. '/' . $image['img_filename']) }}" alt="Image"
                     class="relative w-full h-auto lazy block">
                <svg wire:ignore id="svg-{{$image['img_filename']}}" width="100%" height="100%" class="absolute top-0 left-0 w-full h-full pointer-events-none"></svg>
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
        let categories = $wire.$get('categories');
        $wire.on('images', ({ images }) => {
            console.log('Images received:', images);
            // Wait for the DOM to update before accessing image and svg elements
            setTimeout(() => {
                images.forEach(function(image) {
                    // Process the image for annotations
                    let { imageElement, svg } = getImageAndSvg(image);
                    if (imageElement && svg) {
                        processImages([image]); // Process annotations for this image
                    } else {
                        console.error('Could not find img or svg with id:', image.id);
                    }
                });
            }, 100); // Delay for DOM updates
        });


        function getImageAndSvg(image) {
            let imageElement = document.getElementById(image.img_filename);
            let svg = document.getElementById('svg-' + image.img_filename);
            if(imageElement === null ) {
                console.error('Image ornot found for ' + image.img_filename);
            }
            if(svg === null) {
                console.error('SVG not found for svg-' + image.img_filename);
            }
            return { imageElement, svg };
        }

        // Function to draw a polygon based on annotation points
        function drawPolygon(svg, annotation) {
            let points = JSON.parse(annotation.segmentation);
            let polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
            let pointsString = '';

            points.forEach(function(point, index) {
                pointsString += (index % 2 === 0 ? point * svg.clientWidth : point * svg.clientHeight) + ',';
            });

            pointsString = pointsString.slice(0, -1); // Removes the last character
            polygon.setAttribute('points', pointsString);

            let fillColor = annotation.category.color;
            polygon.setAttribute(
                'fill',
                fillColor.replace('rgb', 'rgba').replace(')', ', 0.5)')
            );
            polygon.setAttribute(
                'stroke',
                fillColor.replace('rgb', 'rgba').replace(')', ', 1)')
            );

            polygon.setAttribute('stroke-width', '1');
            polygon.setAttribute('closed', 'true');


            svg.appendChild(polygon);
        }
        function drawSquare(svg, annotation) {
            // Assuming annotation contains normalized values: x_center, y_center, width, height
            let { x_center, y_center, width, height } = annotation;

            // Calculate the top-left corner coordinates of the square
            let x = (x_center - width / 2) * svg.clientWidth; // x position (normalized to pixel scale)
            let y = (y_center - height / 2) * svg.clientHeight; // y position (normalized to pixel scale)

            // Create the square using the <rect> element
            let square = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            square.setAttribute('x', x); // x position
            square.setAttribute('y', y); // y position
            square.setAttribute('width', width * svg.clientWidth); // Width of square (scaled to SVG width)
            square.setAttribute('height', height * svg.clientHeight); // Height of square (scaled to SVG height)
            square.setAttribute('fill', 'rgba(90, 131, 0, 0.4)'); // Fill color
            square.setAttribute('stroke', 'rgb(0, 210, 255)'); // Stroke color
            square.setAttribute('stroke-width', '1'); // Stroke width

            // Append the square to the SVG container
            svg.appendChild(square);
        }

        // Main function to process all images and annotations
        function processImages(imagesData) {
            imagesData.forEach(function(image) {
                let { imageElement, svg } = getImageAndSvg(image);

                svg.setAttribute('viewBox', `0 0 ${imageElement.clientWidth} ${imageElement.clientHeight}`);

                // Draw annotations
                image.annotations.forEach(function(annotation) {
                    drawPolygon(svg, annotation);
                });
            });
        }
    </script>
@endscript
