@extends('admin.layout')
@section('title', 'Görev Düzenle')

@section('content')
<div class="card" style="max-width:600px;">
    <h2 style="color:#c9a84c;margin-bottom:20px;">✏ Görevi Düzenle</h2>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.quests.update', $quest->id) }}">
        @csrf
        @method('PUT')
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">Görev Başlığı</label>
            <input type="text" name="title" value="{{ old('title', $quest->title) }}" required
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">Açıklama</label>
            <textarea name="description" rows="3"
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">{{ old('description', $quest->description) }}</textarea>
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">Hedef Düşman (Unreal'daki ID)</label>
            <input type="text" name="target_enemy" value="{{ old('target_enemy', $quest->target_enemy) }}" required
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
            <div>
                <label style="display:block;margin-bottom:5px;color:#c9a84c;">Gereken Öldürme</label>
                <input type="number" name="required_kills" value="{{ old('required_kills', $quest->required_kills) }}" min="1" required
                    style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
            </div>
            <div>
                <label style="display:block;margin-bottom:5px;color:#c9a84c;">Min. Level</label>
                <input type="number" name="min_level" value="{{ old('min_level', $quest->min_level) }}" min="1" max="100" required
                    style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
            </div>
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">EXP Ödülü</label>
            <input type="number" name="reward_exp" value="{{ old('reward_exp', $quest->reward_exp) }}" min="1" required
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $quest->is_active) ? 'checked' : '' }}>
                <span style="color:#c9a84c;">Aktif</span>
            </label>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-success">Güncelle</button>
            <a href="{{ route('admin.quests.index') }}" class="btn btn-primary">İptal</a>
        </div>
    </form>
</div>
@endsection
