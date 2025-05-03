<section>
    <header class="mb-4">
        <h2 class="h5 text-primary">
            {{ __('Update Password') }}
        </h2>
        <p class="text-muted">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="mb-3">
            <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
            <input type="password" id="current_password" name="current_password" class="form-control"
                autocomplete="current-password" required>
            @error('current_password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('New Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password"
                required>
            @error('password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                autocomplete="new-password" required>
            @error('password_confirmation')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Save Button -->
        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-success small mb-0" x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 2000)">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
