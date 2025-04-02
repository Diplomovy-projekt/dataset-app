<div x-show="activeTab === 'pascalvoc'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="rounded-xl p-6 border border-slate-700">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-blue-500 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold">Pascal VOC Format Structure</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{--Left: Description--}}
            <div>
                <p class="mb-4">The Pascal VOC (Visual Object Classes) format requires a specific structure in your ZIP file:</p>

                <ul class="space-y-2 mb-6 list-disc list-inside text-slate-300">
                    <li>Images and their corresponding XML files must be at the root level</li>
                    <li>Each image must have a matching XML file with the same name</li>
                    <li>XML files contain annotation data for each image</li>
                    <li>Supported image formats: JPG, PNG, JPEG</li>
                </ul>

                <div class="bg-slate-700 p-4 rounded-lg">
                    <p class="text-slate-300 text-sm mb-2">Make sure your Pascal VOC XML files follow the official format with:</p>
                    <ul class="list-disc list-inside text-slate-400 text-sm space-y-1">
                        <li>Image filename, size (width, height, depth), and source information</li>
                        <li>Object definitions with name, pose, truncated, difficult flags</li>
                        <li>Bounding box coordinates (xmin, ymin, xmax, ymax)</li>
                        <li>Optional segmentation information when available</li>
                    </ul>
                </div>
            </div>

            {{--Right: Visualization--}}
            <div class="bg-slate-900/50 p-6 rounded-lg border border-slate-700">
                <h3 class="text-lg font-medium mb-4 text-slate-300">Expected Structure:</h3>

                <div class="font-mono text-sm space-y-1">
                    <p class="text-slate-300">root_folder/</p>
                    <p class="text-slate-400 ml-4">├── <span class="text-blue-400">image1.jpg</span></p>
                    <p class="text-slate-400 ml-4">├── <span class="text-blue-400">image2.jpg</span></p>
                    <p class="text-slate-400 ml-4">├── <span class="text-green-400">image1.xml</span></p>
                    <p class="text-slate-400 ml-4">└── <span class="text-green-400">image2.xml</span></p>
                </div>

                <div class="mt-8 p-4 bg-slate-800 rounded-lg border border-slate-600">
                    <h4 class="text-sm font-semibold mb-2 text-slate-300">Example Pascal VOC XML Structure:</h4>
                    <pre class="text-xs text-slate-300 overflow-x-auto"><code>&lt;annotation&gt;
  &lt;folder&gt;VOC2012&lt;/folder&gt;
  &lt;filename&gt;image1.jpg&lt;/filename&gt;
  &lt;source&gt;
    &lt;database&gt;The VOC Database&lt;/database&gt;
    &lt;annotation&gt;PASCAL VOC&lt;/annotation&gt;
  &lt;/source&gt;
  &lt;size&gt;
    &lt;width&gt;640&lt;/width&gt;
    &lt;height&gt;480&lt;/height&gt;
    &lt;depth&gt;3&lt;/depth&gt;
  &lt;/size&gt;
  &lt;segmented&gt;0&lt;/segmented&gt;
  &lt;object&gt;
    &lt;name&gt;dog&lt;/name&gt;
    &lt;pose&gt;Unspecified&lt;/pose&gt;
    &lt;truncated&gt;0&lt;/truncated&gt;
    &lt;difficult&gt;0&lt;/difficult&gt;
    &lt;bndbox&gt;
      &lt;xmin&gt;100&lt;/xmin&gt;
      &lt;ymin&gt;100&lt;/ymin&gt;
      &lt;xmax&gt;300&lt;/xmax&gt;
      &lt;ymax&gt;300&lt;/ymax&gt;
    &lt;/bndbox&gt;
  &lt;/object&gt;
  &lt;object&gt;
    &lt;name&gt;cat&lt;/name&gt;
    &lt;pose&gt;Unspecified&lt;/pose&gt;
    &lt;truncated&gt;0&lt;/truncated&gt;
    &lt;difficult&gt;0&lt;/difficult&gt;
    &lt;bndbox&gt;
      &lt;xmin&gt;150&lt;/xmin&gt;
      &lt;ymin&gt;150&lt;/ymin&gt;
      &lt;xmax&gt;400&lt;/xmax&gt;
      &lt;ymax&gt;400&lt;/ymax&gt;
    &lt;/bndbox&gt;
    &lt;polygon&gt;
      &lt;x1&gt;3765&lt;/x1&gt;
      &lt;y1&gt;1175&lt;/y1&gt;
      &lt;x2&gt;4095&lt;/x2&gt;
      &lt;y2&gt;1210&lt;/y2&gt;
      &lt;x3&gt;4335&lt;/x3&gt;
      &lt;y3&gt;1220&lt;/y3&gt;
      &lt;x4&gt;4715&lt;/x4&gt;
      &lt;y4&gt;1265&lt;/y4&gt;
      &lt;x5&gt;4830&lt;/x5&gt;
      &lt;y5&gt;1130&lt;/y5&gt;
    &lt;/polygon&gt;
  &lt;/object&gt;
&lt;/annotation&gt;</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
