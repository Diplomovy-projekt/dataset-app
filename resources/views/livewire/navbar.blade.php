<div class="bg-hcportal-primary drop-shadow-md opacity-100">
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
            <div class="font-bold hidden w-full md:block md:w-auto mr-5" id="navbar-default">
                <ul
                    class=" flex flex-col p-4 md:p-0   rounded-lg  md:flex-row md:space-x-8 md:mt-0 md:border-0   dark:border-gray-700">

                    <li>
                        <a href="{{route('builder')}}"
                           class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">BUILDER</a>
                    </li>
                    <li>
                        <a href="{{route('profile')}}"
                           class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">PROFILE</a>
                    </li>
                    @guest
                        <li>
                            <a href="{{route('welcome')}}"
                               class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">LOGIN</a>
                        </li>

                    @endguest
                    @auth
                        <li>
                            <a href="{{route('welcome')}}"
                               class="block py-2 pl-3 pr-4  rounded
                             md:border-0 md:hover:text-blue-700 md:p-0 text-white
                             md:dark:hover:text-blue-500
                              md:dark:hover:bg-transparent">Friends Showroom</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</div>
