@extends('admin.layout')
@section('title', $user->username ?? $user->name)

@section('content')
<a href="/admin" style="color:#c9a84c;display:inline-block;margin-bottom:20px;">← Geri Dön</a>

<div class="card">
    <h2 style="color:#c9a84c;margin-bottom:20px;">
        {{ $user->username ?? $user->name }}
        @if($user->is_admin) <span class="badge badge-admin">ADMIN</span> @endif
        @if($user->is_banned) <span class="badge badge-banned">YASAK</span> @else <span class="badge badge-active">AKTİF</span> @endif
    </h2>

    <table style="max-width:600px;">
        <tr><td><strong>ID:</strong></td><td>{{ $user->id }}</td></tr>
        <tr><td><strong>Kullanıcı Adı:</strong></td><td>{{ $user->username }}</td></tr>
        <tr><td><strong>E-posta:</strong></td><td>{{ $user->email }}</td></tr>
        <tr><td><strong>Kayıt Tarihi:</strong></td><td>{{ $user->created_at->format('d.m.Y H:i') }}</td></tr>
        <tr><td><strong>Son Giriş:</strong></td><td>{{ $user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : 'Hiç' }}</td></tr>
        <tr><td><strong>Son Giriş IP:</strong></td><td>{{ $user->last_login_ip ?? '-' }}</td></tr>
        <tr><td><strong>Karakter Sayısı:</strong></td><td>{{ $user->characters_count }}</td></tr>
        @if($user->is_banned)
        <tr><td><strong>Yasak Sebebi:</strong></td><td style="color:#e74c3c;">{{ $user->ban_reason }}</td></tr>
        @endif
    </table>
</div>

@if(!$user->is_admin)
<div class="card">
    @if($user->is_banned)
        <h3 style="color:#27ae60;margin-bottom:15px;">Yasağı Kaldır</h3>
        <form method="POST" action="/admin/users/{{ $user->id }}/unban">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success" onclick="return confirm('Yasak kaldırılsın mı?')">Yasağı Kaldır</button>
        </form>
    @else
        <h3 style="color:#e74c3c;margin-bottom:15px;">Kullanıcıyı Yasakla</h3>
        <form method="POST" action="/admin/users/{{ $user->id }}/ban">
            @csrf
            @method('PATCH')
            <div style="margin-bottom:10px;">
                <textarea name="ban_reason" placeholder="Yasak sebebi (isteğe bağlı)..." style="width:100%;max-width:500px;padding:8px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;resize:vertical;min-height:80px;"></textarea>
            </div>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Bu kullanıcı yasaklanacak. Emin misiniz?')">Yasakla</button>
        </form>
    @endif
</div>
@endif

@if($user->characters->count() > 0)
<div class="card">
    <h3 style="color:#c9a84c;margin-bottom:15px;">Karakterler</h3>
    <table>
        <thead>
            <tr>
                <th>Ad</th>
                <th>Sınıf</th>
                <th>Seviye</th>
                <th>Altın</th>
                <th>Harita</th>
                <th>Son Oynama</th>
            </tr>
        </thead>
        <tbody>
            @foreach($user->characters as $char)
            <tr>
                <td>{{ $char->name }}</td>
                <td>{{ ucfirst($char->class) }}</td>
                <td>{{ $char->level }}</td>
                <td>{{ $char->gold }}</td>
                <td>{{ $char->current_map }}</td>
                <td>{{ $char->last_played_at ? $char->last_played_at->diffForHumans() : 'Hiç' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
