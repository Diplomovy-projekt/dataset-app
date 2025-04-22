<footer class="border-t border-gray-600 text-white py-4">
    <div class="container mx-auto">
        <div class="flex justify-between items-center">
            <div>
                <p>&copy; 2025 Dataset Builder</p>
            </div>
            <div>
                <a wire:navigate href="{{ route('terms') }}" class="text-white hover:text-gray-200">Terms of Service</a>
                <span class="mx-2">|</span>
                <a wire:navigate href="{{ route('contact') }}" class="text-white hover:text-gray-200">Contact us</a>
                <span class="mx-2">|</span>
                <a wire:navigate href="{{ route('zip.format.info') }}" class="text-white hover:text-gray-200">Dataset Format Guide</a>
                @if(auth()->check() && Auth::user()->isAdmin())
                    <span class="mx-2">|</span>
                    <a wire:navigate href="{{ route('new.annotation.format') }}" class="text-white hover:text-gray-200">New annotation format</a>
                @endif
            </div>
        </div>
    </div>
</footer>
