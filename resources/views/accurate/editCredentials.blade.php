<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Accurate Credentials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Edit Accurate Credentials</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('credentials.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="client_id" class="form-label">Client ID</label>
                <input type="text" class="form-control" id="client_id" name="client_id"
                    value="{{ $credential->client_id ?? '' }}" required>
            </div>

            <div class="mb-3">
                <label for="client_secret" class="form-label">Client Secret</label>
                <input type="text" class="form-control" id="client_secret" name="client_secret"
                    value="{{ $credential->client_secret ?? '' }}" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Simpan</button>
        </form>
    </div>
</body>

</html>
