<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Accesso amministrazione | Local Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-page">
    <main class="login-card">
        <div class="admin-brand">LOCAL UP <span>ADMIN</span></div>
        <p class="eyebrow">Area riservata</p>
        <h1>Accedi ai contatti</h1>
        <p class="intro">Inserisci le credenziali del tuo account amministratore.</p>

        @if($errors->any())
            <div class="alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('admin.login') }}" method="post">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button type="submit">Accedi <span aria-hidden="true">↗</span></button>
        </form>
    </main>
</body>
</html>
