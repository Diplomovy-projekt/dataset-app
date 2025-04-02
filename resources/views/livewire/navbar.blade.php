<div x-data="{ navLinks: false }" class="bg-hcportal-primary drop-shadow-md opacity-100 relative z-20">
    <nav class="container border-gray-200 h-[105px]">
        <div class="flex flex-wrap items-center justify-between mx-auto mt-0">
            <a wire:navigate href="{{route('welcome')}}" class="flex items-center gap-5">
                <img src="{{asset('v1.png')}}" class="w-[101px] h-[101px] p-2 rounded-3xl" type="image/x-icon"/>
                <span class="hidden lg:block self-center font-bold text-2xl whitespace-nowrap text-white">
                    DATASET BUILDER
                </span>
            </a>

            {{--Mobile menu button--}}
            <button @click="navLinks = !navLinks"
                    type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>

            {{--Navbar items--}}
            <div x-cloak x-show="navLinks || isDesktop" x-data="{ isDesktop: window.innerWidth >= 768 }"
                 x-init="window.matchMedia('(min-width: 768px)').addEventListener('change', e => isDesktop = e.matches)"
                 @click.away="navLinks = false"
                 :class="{'block': navLinks, 'hidden md:flex': !navLinks}"
                 class="border md:border-0 border-gray-800 rounded-md mx-4 -mt-4 md:m-0 w-fit md:flex md:w-auto absolute md:relative top-[105px] right-0 md:top-0 md:left-auto bg-hcportal-primary md:bg-transparent p-4 md:p-0">
                <ul class="items-start divide-y md:divide-y-0 flex flex-col md:flex-row md:space-x-8 md:items-center text-lg font-medium">

                    <li class="w-full"><a wire:navigate href="{{route('builder')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500 transition duration-200 ease-in-out ">BUILDER</a></li>
                    <li class="w-full"><a wire:navigate href="{{route('dataset.index')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500 transition duration-200 ease-in-out">DATASETS</a></li>

                    @guest
                        <li class="w-full"><a wire:navigate href="{{route('login')}}" class="block py-2 pl-3 pr-4 text-white rounded md:p-0 hover:text-blue-500 transition duration-200 ease-in-out">LOGIN</a></li>
                    @endguest
                    @auth
                        <div x-cloak x-data="{ userSubMenu: false }" class="relative">
                            {{--Profile Button--}}
                            <button @click="userSubMenu = !userSubMenu" @click.away="userSubMenu = false"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors hover:bg-slate-700 text-gray-200">
                                <div class="bg-blue-500 p-1.5 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M12 2a5 5 0 100 10 5 5 0 000-10zM4 20a8 8 0 1116 0H4z"
                                              clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': userSubMenu}"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd" />
                                </svg>
                            </button>

                            {{--Dropdown Menu--}}
                            <div x-show="userSubMenu"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-xl bg-slate-800 border border-slate-700 shadow-xl divide-y divide-slate-700">

                                {{--User Section--}}
                                <div class="px-4 py-3">
                                    <p class="text-sm text-gray-200">Signed in as</p>
                                    <p class="text-sm font-medium text-gray-200 truncate">{{ Auth::user()->email }}</p>
                                </div>

                                {{--Main Options--}}
                                <div class="py-1">
                                    <a wire:navigate href="{{route('profile')}}"
                                       class="group flex items-center gap-3 px-4 py-2 text-sm text-gray-200 hover:bg-slate-700">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        My Datasets
                                    </a>
                                    <a wire:navigate href="{{route('my.requests')}}"
                                       class="group flex items-center gap-3 px-4 py-2 text-sm text-gray-200 hover:bg-slate-700">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        My Requests
                                    </a>
                                    <a wire:navigate href="{{route('profile.settings')}}"
                                       class="group flex items-center gap-3 px-4 py-2 text-sm text-gray-200 hover:bg-slate-700">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Settings
                                    </a>
                                </div>

                                {{--Admin Section--}}
                                @can('admin')
                                    <div class="py-1">
                                        <div class="px-4 py-2">
                                            <span class="text-xs font-semibold text-slate-500">Admin</span>
                                        </div>
                                        <a wire:navigate href="{{route('admin.dashboard')}}"
                                           class="group flex items-center gap-3 px-4 py-2 text-sm text-gray-200 hover:bg-slate-700">
                                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Dashboard
                                        </a>
                                        <a wire:navigate href="{{route('admin.users')}}"
                                           class="group flex items-center gap-3 px-4 py-2 text-sm text-gray-200 hover:bg-slate-700">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            User Management
                                        </a>
                                        <a wire:navigate href="{{route('admin.datasets')}}"
                                           class="group flex items-center gap-3 px-4 py-2 text-sm text-gray-200 hover:bg-slate-700">
                                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                            Dataset Management
                                        </a>
                                    </div>
                                @endcan

                                {{--Logout--}}
                                <div class="py-1">
                                    <button wire:click="logout"
                                            class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-400 hover:bg-slate-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</div>
