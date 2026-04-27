<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>motOnline Admin - @yield('title')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #1a1a2e; color: #e0e0e0; }
        .navbar { background: #16213e; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #c9a84c; }
        .navbar h1 { color: #c9a84c; font-size: 1.4rem; }
        .navbar a { color: #e0e0e0; text-decoration: none; margin-left: 20px; }
        .navbar a:hover { color: #c9a84c; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card { background: #16213e; border-radius: 8px; padding: 20px; margin-bottom: 20px; border: 1px solid #0f3460; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; text-decoration: none; display: inline-block; }
        .btn-danger { background: #c0392b; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-primary { background: #2980b9; color: white; }
        .btn:hover { opacity: 0.85; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #0f3460; }
        th { background: #0f3460; color: #c9a84c; }
        tr:hover { background: rgba(201,168,76,0.05); }
        .badge { padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; }
        .badge-banned { background: #c0392b; color: white; }
        .badge-active { background: #27ae60; color: white; }
        .badge-admin { background: #8e44ad; color: white; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #1e4d1e; border-left: 4px solid #27ae60; }
        .alert-error { background: #4d1e1e; border-left: 4px solid #c0392b; }
        .search-form { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-form input { flex: 1; padding: 8px 12px; background: #0f3460; border: 1px solid #2980b9; color: #e0e0e0; border-radius: 4px; }
        .pagination { display: flex; gap: 5px; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; background: #16213e; border: 1px solid #0f3460; border-radius: 4px; color: #e0e0e0; text-decoration: none; }
        .pagination .active { background: #c9a84c; color: #1a1a2e; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>⚔ motOnline Admin Paneli</h1>
        <div>
            <a href="/admin">Kullanıcılar</a>
            <form method="POST" action="/admin/logout" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;color:#e0e0e0;cursor:pointer;font-size:1rem;margin-left:20px;">Çıkış</button>
            </form>
        </div>
    </div>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</body>
</html>
