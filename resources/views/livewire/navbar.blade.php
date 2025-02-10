<div x-data="{ navLinks: false }" class="bg-hcportal-primary drop-shadow-md opacity-100 relative z-20">
    <nav class="container border-gray-200 h-[105px]">
        <div class="flex flex-wrap items-center justify-between mx-auto mt-3">
            <a href="{{route('welcome')}}" class="flex items-center gap-5">
                <img src="{{asset('v1.png')}}" class="w-[101px] h-[101px] p-2 rounded-3xl" type="image/x-icon"/>
                <span class="hidden lg:block self-center font-bold text-2xl whitespace-nowrap text-white">
                    DATASET BUILDER
                </span>
            </a>

            <!-- Mobile menu button -->
            <button @click="navLinks = !navLinks"
                    type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>

            <!-- Navbar items -->
            <div x-show="navLinks || isDesktop" x-data="{ isDesktop: window.innerWidth >= 768 }"
                 x-init="window.matchMedia('(min-width: 768px)').addEventListener('change', e => isDesktop = e.matches)"
                 @click.away="navLinks = false"
                 :class="{'block': navLinks, 'hidden md:flex': !navLinks}"
                 class="border md:border-0 border-gray-800 rounded-md mx-4 -mt-4 md:m-0 w-fit md:flex md:w-auto absolute md:relative top-[105px] right-0 md:top-0 md:left-auto bg-hcportal-primary md:bg-transparent p-4 md:p-0">
                <ul class="items-start divide-y md:divide-y-0 flex flex-col md:flex-row md:space-x-8 md:items-center text-lg font-medium">

                    <li class="w-full"><a wire:navigate href="{{route('builder')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500">BUILDER</a></li>
                    <li class="w-full"><a wire:navigate href="{{route('dataset.index')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500">DATASETS</a></li>
                    <li class="w-full"><a wire:navigate href="{{route('profile')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500">PROFILE</a></li>

                    <!-- Admin dropdown -->
                    <div x-data="{ adminSubMenu: false }" class="relative w-full">
                        <button @click="adminSubMenu = !adminSubMenu" @click.away="adminSubMenu = false"
                                class="flex items-center py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500">
                            <span>ADMIN</span>
                            <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="{'rotate-180': adminSubMenu}"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="adminSubMenu"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:leave="transition ease-in duration-75"
                             class="absolute -left-20 mt-2 w-60 py-2 bg-white rounded-md shadow-xl dark:bg-gray-800">
                            <a wire:navigate href="{{route('admin.dashboard')}}" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                            <a wire:navigate href="{{route('admin.users')}}" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">User Management</a>
                            <a wire:navigate href="{{route('admin.datasets')}}" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Dataset Management</a>
                        </div>
                    </div>

                    @guest
                        <li class="w-full"><a wire:navigate href="{{route('welcome')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500">LOGIN</a></li>
                    @endguest
                    @auth
                        <li class="w-full"><a href="{{route('profile')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500">PROFILE</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</div>
