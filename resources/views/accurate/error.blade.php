{{-- resources/views/accurate/error.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <title>Accurate Error</title>
</head>

<body>
    <h2>Error {{ $status }}</h2>
    <p>{{ $message }}</p>
    @if (!empty($body))
        <pre>{{ $body }}</pre>
    @endif
</body>

</html>
