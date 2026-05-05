@extends('admin.layout')
@section('title', 'Yeni Görev')

@section('content')
<div class="card" style="max-width:600px;">
    <h2 style="color:#c9a84c;margin-bottom:20px;">+ Yeni Görev Oluştur</h2>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.quests.store') }}">
        @csrf
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">Görev Başlığı</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">Açıklama</label>
            <textarea name="description" rows="3"
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">{{ old('description') }}</textarea>
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">Hedef Düşman (Unreal'daki ID)</label>
            <input type="text" name="target_enemy" value="{{ old('target_enemy') }}" required placeholder="örn: vahsi_kurt"
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
            <div>
                <label style="display:block;margin-bottom:5px;color:#c9a84c;">Gereken Öldürme</label>
                <input type="number" name="required_kills" value="{{ old('required_kills', 1) }}" min="1" required
                    style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
            </div>
            <div>
                <label style="display:block;margin-bottom:5px;color:#c9a84c;">Min. Level</label>
                <input type="number" name="min_level" value="{{ old('min_level', 1) }}" min="1" max="100" required
                    style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
            </div>
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;color:#c9a84c;">EXP Ödülü</label>
            <input type="number" name="reward_exp" value="{{ old('reward_exp', 500) }}" min="1" required
                style="width:100%;padding:8px 12px;background:#0f3460;border:1px solid #2980b9;color:#e0e0e0;border-radius:4px;">
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span style="color:#c9a84c;">Aktif</span>
            </label>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-success">Kaydet</button>
            <a href="{{ route('admin.quests.index') }}" class="btn btn-primary">İptal</a>
        </div>
    </form>
</div>
@endsection
