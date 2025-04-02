<div x-show="activeTab === 'yolo'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="rounded-xl p-6 border border-slate-700">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-blue-500 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold">YOLO Format Structure</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{--Left: Description--}}
            <div>
                <p class="mb-4">The YOLO (You Only Look Once) format requires specific structure in your ZIP file:</p>

                <ul class="space-y-2 mb-6 list-disc list-inside text-slate-300">
                    <li>Images must be in an <span class="text-blue-400 font-mono">images/</span> folder</li>
                    <li>Labels must be in a <span class="text-green-400 font-mono">labels/</span> folder</li>
                    <li>Each label file should correspond to an image file (same name, .txt extension)</li>
                    <li>A <span class="text-purple-400 font-mono">data.yaml</span> file must be at the root</li>
                    <li>Supported image formats: JPG, PNG, JPEG</li>
                </ul>

                <div class="bg-slate-700 p-4 rounded-lg">
                    <p class="text-slate-300 text-sm mb-2">Each YOLO annotation text file should:</p>
                    <ul class="list-disc list-inside text-slate-400 text-sm space-y-1">
                        <li>Contain one line per annotation</li>
                        <li>Bounding box: Each line should have the format: <span class="font-mono">class_id center_x center_y width height</span></li>
                        <li>Polygon: Each line should have the format: <span class="font-mono">class_id x1 y1 x2 y2...</span></li>
                        <li>All values after class_id are relative to the image width and height (0.0-1.0)</li>
                    </ul>
                </div>
            </div>

            {{--Right: Visualization--}}
            <div class="bg-slate-900/50 p-6 rounded-lg border border-slate-700">
                <h3 class="text-lg font-medium mb-4 text-slate-300">Expected Structure:</h3>

                <div class="font-mono text-sm space-y-1">
                    <p class="text-slate-300">root_folder/</p>
                    <p class="text-slate-300 ml-4">├── <span class="text-purple-400">data.yaml</span></p>
                    <p class="text-slate-300 ml-4">├── <span class="text-blue-400">images/</span></p>
                    <p class="text-slate-400 ml-8">├── image1.jpg</p>
                    <p class="text-slate-400 ml-8">└── image2.jpg</p>
                    <p class="text-slate-300 ml-4">└── <span class="text-green-400">labels/</span></p>
                    <p class="text-slate-400 ml-8">├── image1.txt</p>
                    <p class="text-slate-400 ml-8">└── image2.txt</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-8">
                    <div class="p-4 bg-slate-800 rounded-lg border border-slate-600">
                        <h4 class="text-sm font-semibold mb-2 text-slate-300">Example data.yaml:</h4>
                        <pre class="text-xs text-slate-300 overflow-x-auto"><code>
nc: 3
names:
  - person
  - car
  - dog</code></pre>
                    </div>

                    <div class="p-4 bg-slate-800 rounded-lg border border-slate-600">
                        <h4 class="text-sm font-semibold mb-2 text-slate-300">Example annotation (image1.txt):</h4>
                        <pre class="text-xs text-slate-300 overflow-x-auto"><code>0 0.5 0.5 0.2 0.3
1 0.7 0.7 0.1 0.1</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
