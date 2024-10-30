<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="max-w-md mx-auto p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" 
                class="bg-gray-50 border border-[#800020] text-gray-900 sm:text-sm rounded-lg focus:ring-[#800020] focus:border-[#800020] block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-[#800020] dark:focus:border-[#800020]" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" 
                class="bg-gray-50 border border-[#800020] text-gray-900 sm:text-sm rounded-lg focus:ring-[#800020] focus:border-[#800020] block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-[#800020] dark:focus:border-[#800020]" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mt-4">
            <input id="remember_me" type="checkbox" class="w-4 h-4 text-[#800020] bg-gray-100 rounded border-gray-300 focus:ring-[#800020] dark:focus:ring-[#800020] dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" name="remember">
            <label for="remember_me" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('Remember me') }}</label>
        </div>

        <div class="flex items-center justify-between mt-6">
 
            <button type="submit" class="px-5 py-3 text-sm font-medium text-center text-white bg-[#800020] rounded-lg hover:bg-[#cc0040] focus:ring-4 focus:outline-none focus:ring-[#cc0040] dark:bg-[#800020] dark:hover:bg-[#cc0040] dark:focus:ring-[#cc0040]">
                {{ __('Log in') }}
            </button>
        </div>
    </form>
</x-guest-layout>
