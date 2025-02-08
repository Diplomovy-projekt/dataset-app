<div class="bg-hcportal-primary drop-shadow-md opacity-100 relative z-50">
    <nav class="container border-gray-200 h-[105px] ">
        <div class=" flex flex-wrap items-center justify-between mx-auto mt-3">
            <a href="{{route('welcome')}}" class="flex items-center gap-5">
                <img src="{{asset('v1.png')}}"  class="w-[101px] h-[101px] p-2 rounded-3xl" type="image/x-icon"/>
                <span class="self-center font-bold text-2xl whitespace-nowrap text-white">DATASET BUILDER</span>
            </a>
            <button data-collapse-toggle="navbar-default" type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    aria-controls="navbar-default" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M1 1h15M1 7h15M1 13h15" />
                </svg>
            </button>
            <div class=" font-bold hidden w-full md:block md:w-auto mr-5" id="navbar-default">
                <ul
                    class="items-center flex flex-col p-4 md:p-0   rounded-lg  md:flex-row md:space-x-8 md:mt-0 md:border-0   dark:border-gray-700">

                    <li>
                        <a wire:navigate href="{{route('builder')}}"
                           class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">BUILDER</a>
                    </li>
                    <li>
                        <a wire:navigate href="{{route('dataset.index')}}"
                           class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">OUR DATASETS</a>
                    </li>
                    <li>
                        <a wire:navigate href="{{route('profile')}}"
                           class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">PROFILE</a>
                    </li>

                    <div x-data="{ open: false }" class="relative z-50">
                        <!-- Admin Button Trigger -->
                        <button @click="open = !open"
                                @click.outside="open = false"
                                class="flex items-center py-2 pl-3 pr-4 text-white rounded
                                   hover:text-blue-700 dark:hover:text-blue-500
                                        transition-colors duration-200">
                            <span>ADMIN</span>
                            <!-- Dropdown Arrow -->
                            <svg class="w-4 h-4 ml-1" :class="{'rotate-180': open}"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 py-2 bg-white rounded-md shadow-xl z-50
                dark:bg-gray-800">

                            <a wire:navigate
                               href="{{route('admin')}}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100
                  dark:text-gray-200 dark:hover:bg-gray-700
                  transition-colors duration-200">
                                Dashboard
                            </a>

                            <a wire:navigate
                               href="{{route('admin.users')}}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100
                  dark:text-gray-200 dark:hover:bg-gray-700
                  transition-colors duration-200">
                                User Management
                            </a>

                            <a wire:navigate
                               href="{{route('admin.datasets')}}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100
                  dark:text-gray-200 dark:hover:bg-gray-700
                  transition-colors duration-200">
                                Dataset Management
                            </a>
                        </div>
                    </div>


                    @guest
                        <li>
                            <a wire:navigate href="{{route('welcome')}}"
                               class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">LOGIN</a>
                        </li>

                    @endguest
                    @auth
                        <li>
                            <a href="{{route('profile')}}"
                               class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">PROFILE</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</div>
