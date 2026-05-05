@extends('admin.layout')
@section('title', 'Görevler')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:#c9a84c;">⚔ Görevler</h2>
        <a href="{{ route('admin.quests.create') }}" class="btn btn-success">+ Yeni Görev</a>
    </div>

    @if($quests->isEmpty())
        <p style="color:#aaa;">Henüz görev yok.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Başlık</th>
                <th>Hedef Düşman</th>
                <th>Gereken Öldürme</th>
                <th>Min. Level</th>
                <th>EXP Ödülü</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quests as $quest)
            <tr>
                <td>{{ $quest->id }}</td>
                <td>{{ $quest->title }}</td>
                <td>{{ $quest->target_enemy }}</td>
                <td>{{ $quest->required_kills }}</td>
                <td>{{ $quest->min_level }}</td>
                <td>{{ number_format($quest->reward_exp) }}</td>
                <td>
                    <span class="badge {{ $quest->is_active ? 'badge-active' : 'badge-banned' }}">
                        {{ $quest->is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.quests.edit', $quest->id) }}" class="btn btn-primary" style="font-size:0.8rem;padding:5px 10px;">Düzenle</a>
                    <form method="POST" action="{{ route('admin.quests.destroy', $quest->id) }}" style="display:inline;" onsubmit="return confirm('Görevi silmek istediğinize emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size:0.8rem;padding:5px 10px;">Sil</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">
        {{ $quests->links('admin.pagination') }}
    </div>
    @endif
</div>
@endsection
