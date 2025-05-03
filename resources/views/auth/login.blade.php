<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="p-4">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required
                autofocus>
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" required>
            @error('password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-3">
            <input type="checkbox" id="remember_me" name="remember" class="form-check-input">
            <label for="remember_me" class="form-check-label">{{ __('Remember me') }}</label>
        </div>

        <!-- Forgot Password and Submit -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none">
                    {{ __('Forgot your password?') }}
                </a>
            @endif --}}

            <button type="submit" class="btn btn-primary">
                {{ __('Log in') }}
            </button>
        </div>
    </form>

    <!-- Register Link -->
    <div class="text-center mt-4">
        <span class="text-muted">{{ __('Don\'t have an account?') }}</span>
        <a href="{{ route('register') }}" class="text-decoration-none text-primary">
            {{ __('Register') }}
        </a>
    </div>
</x-guest-layout>
