@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">今日・明日の給食献立</h1>
    </div>
</div>

<div class="row">
    <!-- 今日の献立 -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">今日の献立 ({{ now()->format('Y年m月d日') }})</h3>
            </div>
            <div class="card-body">
                @if($todayMenu)
                    <div class="menu-items">
                        @if($todayMenu->main_dish)
                            <div class="mb-2">
                                <strong>主菜:</strong> {{ $todayMenu->main_dish }}
                            </div>
                        @endif
                        
                        @if($todayMenu->side_dish)
                            <div class="mb-2">
                                <strong>副菜:</strong> {{ $todayMenu->side_dish }}
                            </div>
                        @endif
                        
                        @if($todayMenu->soup)
                            <div class="mb-2">
                                <strong>汁物:</strong> {{ $todayMenu->soup }}
                            </div>
                        @endif
                        
                        @if($todayMenu->rice)
                            <div class="mb-2">
                                <strong>ご飯:</strong> {{ $todayMenu->rice }}
                            </div>
                        @endif
                        
                        @if($todayMenu->drink)
                            <div class="mb-2">
                                <strong>飲み物:</strong> {{ $todayMenu->drink }}
                            </div>
                        @endif
                        
                        @if($todayMenu->dessert)
                            <div class="mb-2">
                                <strong>デザート:</strong> {{ $todayMenu->dessert }}
                            </div>
                        @endif
                        
                        @if($todayMenu->other)
                            <div class="mb-2">
                                <strong>その他:</strong> <small>{{ $todayMenu->other }}</small>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-muted">
                        <p>今日の献立は登録されていません。</p>
                        <a href="{{ route('menu.upload.form') }}" class="btn btn-primary">
                            月次献立をアップロード
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 明日の献立 -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">明日の献立 ({{ now()->addDay()->format('Y年m月d日') }})</h3>
            </div>
            <div class="card-body">
                @if($tomorrowMenu)
                    <div class="menu-items">
                        @if($tomorrowMenu->main_dish)
                            <div class="mb-2">
                                <strong>主菜:</strong> {{ $tomorrowMenu->main_dish }}
                            </div>
                        @endif
                        
                        @if($tomorrowMenu->side_dish)
                            <div class="mb-2">
                                <strong>副菜:</strong> {{ $tomorrowMenu->side_dish }}
                            </div>
                        @endif
                        
                        @if($tomorrowMenu->soup)
                            <div class="mb-2">
                                <strong>汁物:</strong> {{ $tomorrowMenu->soup }}
                            </div>
                        @endif
                        
                        @if($tomorrowMenu->rice)
                            <div class="mb-2">
                                <strong>ご飯:</strong> {{ $tomorrowMenu->rice }}
                            </div>
                        @endif
                        
                        @if($tomorrowMenu->drink)
                            <div class="mb-2">
                                <strong>飲み物:</strong> {{ $tomorrowMenu->drink }}
                            </div>
                        @endif
                        
                        @if($tomorrowMenu->dessert)
                            <div class="mb-2">
                                <strong>デザート:</strong> {{ $tomorrowMenu->dessert }}
                            </div>
                        @endif
                        
                        @if($tomorrowMenu->other)
                            <div class="mb-2">
                                <strong>その他:</strong> <small>{{ $tomorrowMenu->other }}</small>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-muted">
                        <p>明日の献立は登録されていません。</p>
                        <a href="{{ route('menu.upload.form') }}" class="btn btn-success">
                            月次献立をアップロード
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 最近アップロードされた月次PDF -->
@if($recentPdfs->count() > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>最近アップロードされた献立PDF</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>対象月</th>
                                <th>解析済み日数</th>
                                <th>アップロード日時</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPdfs as $pdf)
                            <tr>
                                <td>{{ $pdf->formatted_month }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $pdf->total_days_parsed }}日</span>
                                </td>
                                <td>{{ $pdf->created_at->format('Y/m/d H:i') }}</td>
                                <td>
                                    <a href="{{ route('menu.monthly', ['year' => $pdf->year, 'month' => $pdf->month]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        詳細表示
                                    </a>
                                    <a href="{{ route('menu.pdf', $pdf->id) }}" 
                                       class="btn btn-sm btn-outline-secondary" target="_blank">
                                        PDF表示
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection