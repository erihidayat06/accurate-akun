<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="p-4">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required
                autofocus autocomplete="name">
            @error('name')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}"
                required autocomplete="username">
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" required
                autocomplete="new-password">
            @error('password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required
                autocomplete="new-password">
            @error('password_confirmation')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Register Button -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('login') }}" class="text-decoration-none">
                {{ __('Already registered?') }}
            </a>
            <button type="submit" class="btn btn-primary">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>
