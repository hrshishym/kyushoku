@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>{{ $year }}年{{ $month }}月の献立表</h1>
            <div>
                <form method="GET" action="{{ route('menu.monthly') }}" class="d-flex gap-2">
                    <select name="year" class="form-select form-select-sm">
                        @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}年
                            </option>
                        @endfor
                    </select>
                    <select name="month" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ $m }}月
                            </option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-outline-primary btn-sm">表示</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($monthlyPdf)
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📄 PDF情報</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>ファイル名:</strong><br>
                        <small>{{ $monthlyPdf->original_filename }}</small>
                    </div>
                    <div class="col-md-2">
                        <strong>解析済み日数:</strong><br>
                        <span class="badge bg-success fs-6">{{ $monthlyPdf->total_days_parsed }}日</span>
                    </div>
                    <div class="col-md-3">
                        <strong>アップロード日時:</strong><br>
                        <small>{{ $monthlyPdf->created_at->format('Y/m/d H:i:s') }}</small>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('menu.pdf', $monthlyPdf->id) }}" 
                           class="btn btn-outline-primary btn-sm" target="_blank">
                            📄 元のPDFを表示
                        </a>
                        <a href="{{ route('menu.upload.form') }}" 
                           class="btn btn-outline-warning btn-sm">
                            🔄 再アップロード
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($menus->count() > 0)
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 10%;">日付</th>
                        <th style="width: 15%;">主菜</th>
                        <th style="width: 15%;">副菜</th>
                        <th style="width: 12%;">汁物</th>
                        <th style="width: 10%;">ご飯</th>
                        <th style="width: 8%;">飲み物</th>
                        <th style="width: 10%;">デザート</th>
                        <th style="width: 20%;">その他</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menus as $menu)
                    <tr class="{{ $menu->date->isToday() ? 'table-warning' : ($menu->date->isTomorrow() ? 'table-info' : '') }}">
                        <td>
                            <strong>{{ $menu->date->format('m/d') }}</strong><br>
                            <small class="text-muted">{{ $menu->date->isoFormat('(ddd)') }}</small>
                            @if($menu->date->isToday())
                                <span class="badge bg-warning text-dark">今日</span>
                            @elseif($menu->date->isTomorrow())
                                <span class="badge bg-info">明日</span>
                            @endif
                        </td>
                        <td>{{ $menu->main_dish ?: '-' }}</td>
                        <td>{{ $menu->side_dish ?: '-' }}</td>
                        <td>{{ $menu->soup ?: '-' }}</td>
                        <td>{{ $menu->rice ?: '-' }}</td>
                        <td>{{ $menu->drink ?: '-' }}</td>
                        <td>{{ $menu->dessert ?: '-' }}</td>
                        <td>
                            @if($menu->other)
                                <small>{{ Str::limit($menu->other, 100) }}</small>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning text-center">
            <h4>📅 {{ $year }}年{{ $month }}月の献立データがありません</h4>
            <p class="mb-3">この月の献立PDFがまだアップロードされていません。</p>
            <a href="{{ route('menu.upload.form') }}" class="btn btn-primary">
                📄 月次献立PDFをアップロード
            </a>
        </div>
    </div>
</div>
@endif

@if($menus->count() > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>📊 統計情報</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-primary">{{ $menus->count() }}</h3>
                            <small>登録日数</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success">{{ $menus->whereNotNull('main_dish')->count() }}</h3>
                            <small>主菜あり</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-info">{{ $menus->whereNotNull('dessert')->count() }}</h3>
                            <small>デザートあり</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-warning">{{ $menus->whereNotNull('other')->count() }}</h3>
                            <small>特記事項あり</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection