@extends('admin.layout')
@section('title', 'Kullanıcılar')

@section('content')
<div class="card">
    <h2 style="color:#c9a84c;margin-bottom:20px;">Kayıtlı Oyuncular ({{ $users->total() }})</h2>

    <form class="search-form" method="GET" action="/admin">
        <input type="text" name="search" placeholder="Kullanıcı adı veya e-posta ara..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary">Ara</button>
        @if(request('search'))
            <a href="/admin" class="btn btn-danger">Temizle</a>
        @endif
    </form>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kullanıcı Adı</th>
                <th>E-posta</th>
                <th>Karakter</th>
                <th>Son Giriş</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>
                    <a href="/admin/users/{{ $user->id }}" style="color:#c9a84c;">{{ $user->username ?? $user->name }}</a>
                    @if($user->is_admin) <span class="badge badge-admin">ADMIN</span> @endif
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->characters_count }}</td>
                <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Hiç' }}</td>
                <td>
                    @if($user->is_banned)
                        <span class="badge badge-banned">YASAK</span>
                    @else
                        <span class="badge badge-active">AKTİF</span>
                    @endif
                </td>
                <td>
                    <a href="/admin/users/{{ $user->id }}" class="btn btn-primary" style="font-size:0.8rem;padding:5px 10px;">Detay</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination">
        {{ $users->links('admin.pagination') }}
    </div>
</div>
@endsection
