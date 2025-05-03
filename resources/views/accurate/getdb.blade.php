<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow" style="width: 30rem;">
            <div class="card-body">
                <h5 class="card-title text-center">Pilih Database</h5>
                <form action="{{ route('get.item') }}" method="GET">
                    @csrf
                    <div class="mb-3">
                        <label for="database" class="form-label">Database</label>
                        <select class="form-select" id="database" name="dbId" required>
                            <option value="" disabled selected>Pilih database</option>
                            @foreach ($databases as $database)
                                <option value="{{ $database['id'] }}">{{ $database['alias'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Gunakan Database</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
