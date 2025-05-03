<x-app-layout>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow" style="width: 30rem;">
            <div class="card-body">
                <h3 class="text-center mb-4">Akun accurate</h3>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('dashboard.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client ID</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="client_id" name="client_id"
                                value="{{ $credential->client_id ?? '' }}" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password"
                                data-target="#client_id">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="client_secret" class="form-label">Client Secret</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="client_secret" name="client_secret"
                                value="{{ $credential->client_secret ?? '' }}" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password"
                                data-target="#client_secret">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="redirect_uri" class="form-label">Redirect URI</label>
                        <input type="url" class="form-control" id="redirect_uri" name="redirect_uri"
                            value="{{ $credential->redirect_uri ?? '' }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">Simpan</button>
                </form>

                <!-- Tombol untuk masuk ke Accurate login -->
                <a href="{{ route('accurate.login') }}" class="btn btn-secondary w-100">Masuk ke Accurate Login</a>
            </div>
        </div>
    </div>

    <!-- Script untuk toggle password visibility -->
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const target = document.querySelector(this.dataset.target);
                const icon = this.querySelector('i');
                if (target.type === 'password') {
                    target.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    target.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    </script>
</x-app-layout>
