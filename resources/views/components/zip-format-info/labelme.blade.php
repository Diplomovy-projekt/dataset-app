<div x-show="activeTab === 'labelme'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class=" rounded-xl p-6 border border-slate-700">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-blue-500 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold">LabelMe Format Structure</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{--Left: Description--}}
            <div>
                <p class="mb-4">The LabelMe format requires a specific organization in your ZIP file:</p>

                <ul class="space-y-2 mb-6 list-disc list-inside text-slate-300">
                    <li>All images must be in an <span class="text-blue-400 font-mono">images/</span> folder</li>
                    <li>Note that <span class="font-mono">"imagePath" attribute</span> can also reference just image file name </li>
                    <li>Annotations must be in a <span class="text-green-400 font-mono">labels/</span> folder</li>
                    <li>Each annotation file must correspond to an image file with the same name but .json extension</li>
                    <li>Supported image formats: JPG, PNG, JPEG</li>
                </ul>

                <div class="bg-slate-700 p-4 rounded-lg">
                    <p class="text-slate-300 text-sm mb-2">Make sure your LabelMe JSON files follow the standard LabelMe format with:</p>
                    <ul class="list-disc list-inside text-slate-400 text-sm space-y-1">
                        <li>Image path, width, and height information</li>
                        <li>Shapes array containing annotations with label, points, shape_type, and flags</li>
                        <li>Image path key can contain just filename of image</li>
                        <li>Valid polygon or rectangle</li>
                    </ul>
                </div>
            </div>

            {{--Right: Visualization--}}
            <div class="bg-slate-900/50 p-6 rounded-lg border border-slate-700">
                <h3 class="text-lg font-medium mb-4 text-slate-300">Expected Structure:</h3>

                <div class="font-mono text-sm space-y-1">
                    <p class="text-slate-300">root_folder/</p>
                    <p class="text-slate-300 ml-4">├── <span class="text-blue-400">images/</span></p>
                    <p class="text-slate-400 ml-8">├── image1.jpg</p>
                    <p class="text-slate-400 ml-8">└── image2.jpg</p>
                    <p class="text-slate-300 ml-4">└── <span class="text-green-400">labels/</span></p>
                    <p class="text-slate-400 ml-8">├── image1.json</p>
                    <p class="text-slate-400 ml-8">└── image2.json</p>
                </div>

                <div class="mt-8 p-4 bg-slate-800 rounded-lg border border-slate-600">
                    <h4 class="text-sm font-semibold mb-2 text-slate-300">Example LabelMe JSON Structure:</h4>
                    <pre class="text-xs text-slate-300 overflow-x-auto"><code>{
  "version": "4.5.6",
  "flags": {},
  "shapes": [
    {
      "label": "dog",
      "points": [
        [100, 100],
        [300, 100],
        [300, 300],
        [100, 300]
      ],
      "group_id": null,
      "shape_type": "polygon",
      "flags": {}
    }
  ],
  "imagePath": "../images/image1.jpg", // Or just file name (image1.jpg)
  "imageData": null,
  "imageHeight": 480,
  "imageWidth": 640
}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
