<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>motOnline Admin - Giriş</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #1a1a2e; color: #e0e0e0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { background: #16213e; border-radius: 8px; padding: 40px; width: 100%; max-width: 400px; border: 1px solid #0f3460; }
        h1 { color: #c9a84c; text-align: center; margin-bottom: 8px; }
        p { color: #888; text-align: center; margin-bottom: 30px; font-size: 0.9rem; }
        label { display: block; margin-bottom: 5px; color: #aaa; font-size: 0.9rem; }
        input { width: 100%; padding: 10px 12px; background: #0f3460; border: 1px solid #2980b9; color: #e0e0e0; border-radius: 4px; margin-bottom: 20px; font-size: 1rem; }
        input:focus { outline: none; border-color: #c9a84c; }
        button { width: 100%; padding: 12px; background: #c9a84c; color: #1a1a2e; border: none; border-radius: 4px; font-size: 1rem; font-weight: bold; cursor: pointer; }
        button:hover { background: #e0c16b; }
        .error { background: #4d1e1e; border-left: 4px solid #c0392b; padding: 10px 14px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>⚔ motOnline</h1>
        <p>Admin Paneli</p>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="/admin/login">
            @csrf
            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" required autofocus value="{{ old('email') }}">
            <label for="password">Şifre</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Giriş Yap</button>
        </form>
    </div>
</body>
</html>
