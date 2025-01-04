<div class="p-6">
    <x-modals.fixed-modal modalId="display-classes" class="sm:max-w-11/12">

        <!-- Modal Title -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-200">Class Sample Preview</h1>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent mx-6"></div>
        </div>

        @foreach($this->datasets as $dataset)
            <div class="mb-8">
                <!-- Dataset Header -->
                <div class="flex items-center justify-between bg-gradient-to-r from-slate-800 to-slate-900 p-4 rounded-t-xl border-b border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-500 p-2 rounded-lg">
                            <x-icon name="o-folder" class="w-5 h-5 text-gray-200" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-200">{{$dataset['display_name']}}</h2>
                            <p class="text-slate-400 text-sm">Total Classes: {{count($dataset['classes'])}}</p>
                        </div>
                    </div>

                    <!-- Sorting Controls -->
                    <div class="flex items-center gap-3">
                        <span class="text-slate-400 text-sm">Sort by:</span>
                        <select class="bg-slate-700 text-gray-200 rounded-md px-3 py-1.5 text-sm border border-slate-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option>Class Name</option>
                            <option>Annotation Count</option>
                            <option>Image Count</option>
                        </select>
                    </div>
                </div>

                <!-- Classes Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4 bg-slate-900/50 rounded-b-xl">
                    @foreach($dataset['classes'] as $class)
                        <div class="bg-slate-800 rounded-lg hover:bg-slate-750 transition-all duration-200 group">
                            <!-- Class Header -->
                            <div class="p-4 border-b border-slate-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-200 group-hover:text-blue-400 transition-colors">
                                        {{$class['name']}}
                                    </h3>
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-2 bg-slate-700 px-3 py-1 rounded-full">
                                            <x-icon name="o-photo" class="w-4 h-4 text-slate-400" />
                                            <span class="text-sm text-gray-200">156</span>
                                        </div>
                                        <div class="flex items-center gap-2 bg-slate-700 px-3 py-1 rounded-full">
                                            <x-icon name="o-tag" class="w-4 h-4 text-slate-400" />
                                            <span class="text-sm text-gray-200">2,453</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Preview Grid -->
                            <div class="p-4">
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($class['images'] as $image)
                                        <div class="relative group/image">
                                            <img src="{{asset($image)}}"
                                                 class="w-10 rounded-md border border-slate-700 group-hover/image:border-blue-500 transition-all"
                                                 loading="lazy"
                                                 @click="const imgSrc = $event.target.src;
                                                        console.log(imgSrc);
                                                        $dispatch('open-full-screen-image', { src: imgSrc, overlayId: 'asdd' })">
                                            <!-- Hover Overlay -->
                                            <div class="pointer-events-none absolute inset-0 bg-slate-900/60 opacity-0 group-hover/image:opacity-100 transition-opacity rounded-md flex items-center justify-center">
                                                <x-icon name="o-magnifying-glass" class="w-4 h-4 text-gray-200" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </x-modals.fixed-modal>
</div>
