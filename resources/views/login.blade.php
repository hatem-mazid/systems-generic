<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ __('welcome to') }} {{ __('Vectorian Palace Cafe') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>

        <div class="bg-surface-50 dark:bg-surface-950 px-6 py-20 md:px-12 lg:px-20 h-svh">
            <div class="bg-surface-0 dark:bg-surface-900 p-6 shadow rounded-border w-full lg:w-6/12 mx-auto">

                {{-- Header --}}
                <div class="text-center mb-8">
                    <svg class="mb-4 mx-auto fill-surface-600 dark:fill-surface-200 h-16" viewBox="0 0 30 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M20.7207 6.18211L14.9944 3.11148L3.46855 9.28678L0.579749 7.73444L14.9944 0L23.6242 4.62977L20.7207 6.18211ZM14.9996 12.3574L26.5182 6.1821L29.4216 7.73443L14.9996 15.4621L6.37724 10.8391L9.27337 9.28677L14.9996 12.3574ZM2.89613 16.572L0 15.0196V24.2656L14.4147 32V28.8953L2.89613 22.7132V16.572ZM11.5185 18.09L0 11.9147V8.81001L14.4147 16.5376V25.7904L11.5185 24.2312V18.09ZM24.2086 15.0194V11.9147L15.5788 16.5377V31.9998L18.475 30.4474V18.09L24.2086 15.0194ZM27.0969 22.7129V10.3623L30.0004 8.81V24.2653L21.3706 28.895V25.7904L27.0969 22.7129Z"
                        />
                    </svg>

                    <h1 class="text-2xl font-bold mb-2 text-balance text-surface-900 dark:text-surface-0">
                        {{ __('welcome to') }}
                        <span class="text-primary">{{ __('Vectorian Palace Cafe') }}</span>
                    </h1>
                    <span class="text-surface-600 dark:text-surface-200 font-medium leading-normal">
                        {{ __('please enter your credentials. contact admin for access') }}
                    </span>
                </div>

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    {{-- Phone Number --}}
                    <div class="mb-4">
                        <label for="email" class="text-surface-900 dark:text-surface-0 font-medium mb-2 block">
                            {{ __('email') }}
                        </label>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="{{ __('email') }}"
                            class="w-full mb-1 px-3 py-2 border rounded-md bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-0
                                {{ $errors->has('email') ? 'border-red-500' : 'border-surface-300 dark:border-surface-600' }}
                                focus:outline-none focus:ring-2 focus:ring-primary"
                            autocomplete="username"
                        />
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <label for="password" class="text-surface-900 dark:text-surface-0 font-medium mb-2 block">
                            {{ __('password') }}
                        </label>
                        <div class="relative mb-1">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="{{ __('password') }}"
                                class="w-full px-3 py-2 pr-10 border rounded-md bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-0
                                    {{ $errors->has('password') ? 'border-red-500' : 'border-surface-300 dark:border-surface-600' }}
                                    focus:outline-none focus:ring-2 focus:ring-primary"
                                autocomplete="current-password"
                            />
                            {{-- Toggle password visibility --}}
                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute inset-y-0 end-0 flex items-center pe-3 text-surface-500 hover:text-surface-700 dark:hover:text-surface-200"
                                aria-label="Toggle password visibility"
                            >
                                <i id="toggle-icon" class="pi pi-eye text-sm"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between mb-12">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="remember"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                                class="me-2 rounded border-surface-300 dark:border-surface-600 text-primary focus:ring-primary"
                            />
                            <label class="mb-0 text-surface-900 dark:text-surface-0" for="remember">
                                {{ __('remember me') }}
                            </label>
                        </div>
                        {{-- <a href="{{ route('password.request') }}" class="font-medium no-underline ml-2 text-primary text-right cursor-pointer">Forgot password?</a> --}}
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white font-medium rounded-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors"
                    >
                        <i class="pi pi-user"></i>
                        {{ __('sign In') }}
                    </button>

                </form>
            </div>
        </div>

        <script>
            function togglePassword() {
                const input = document.getElementById('password');
                const icon = document.getElementById('toggle-icon');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('pi-eye', 'pi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('pi-eye-slash', 'pi-eye');
                }
            }
        </script>

    </body>
</html>
