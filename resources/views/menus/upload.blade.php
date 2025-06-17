@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>月次給食献立PDFアップロード</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('menu.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_year" class="form-label">対象年</label>
                                <select class="form-control @error('target_year') is-invalid @enderror" 
                                        id="target_year" 
                                        name="target_year" 
                                        required>
                                    @for($year = now()->year - 1; $year <= now()->year + 1; $year++)
                                        <option value="{{ $year }}" 
                                                {{ old('target_year', now()->year) == $year ? 'selected' : '' }}>
                                            {{ $year }}年
                                        </option>
                                    @endfor
                                </select>
                                @error('target_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_month" class="form-label">対象月</label>
                                <select class="form-control @error('target_month') is-invalid @enderror" 
                                        id="target_month" 
                                        name="target_month" 
                                        required>
                                    @for($month = 1; $month <= 12; $month++)
                                        <option value="{{ $month }}" 
                                                {{ old('target_month', now()->month) == $month ? 'selected' : '' }}>
                                            {{ $month }}月
                                        </option>
                                    @endfor
                                </select>
                                @error('target_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pdf_file" class="form-label">月次献立PDF</label>
                        <input type="file" 
                               class="form-control @error('pdf_file') is-invalid @enderror" 
                               id="pdf_file" 
                               name="pdf_file" 
                               accept=".pdf" 
                               required>
                        <div class="form-text">
                            1ヶ月分の献立が記載されたPDFファイルのみアップロード可能です（最大20MB）。
                        </div>
                        @error('pdf_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('menu.index') }}" class="btn btn-secondary me-md-2">キャンセル</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            アップロード・解析実行
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4>使用方法</h4>
            </div>
            <div class="card-body">
                <ol>
                    <li>献立PDFの対象年月を選択してください</li>
                    <li>1ヶ月分の給食献立が記載されたPDFファイルを選択してください</li>
                    <li>「アップロード・解析実行」ボタンをクリックしてください</li>
                    <li>Claude APIがPDFを直接解析し、1ヶ月分の献立情報を自動抽出します</li>
                    <li>解析完了後、各日の献立がデータベースに保存されます</li>
                </ol>
                
                <div class="alert alert-info mt-3">
                    <h5 class="alert-heading">📋 解析について</h5>
                    <ul class="mb-0">
                        <li><strong>Claude APIの直接PDF解析:</strong> テキスト抽出不要で高精度な解析</li>
                        <li><strong>1ヶ月一括処理:</strong> 土日・祝日を除く平日の献立を自動識別</li>
                        <li><strong>構造化データ:</strong> 主菜・副菜・汁物などを自動分類</li>
                        <li><strong>処理時間:</strong> 1ヶ月分で約30秒〜1分程度</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <strong>⚠️ 注意事項:</strong>
                    <ul class="mb-0">
                        <li>同じ年月のPDFを再アップロードすると、既存データは上書きされます</li>
                        <li>PDF解析中はページを閉じないでください</li>
                        <li>Claude APIの解析が完璧でない場合があります。結果を確認してください</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function() {
    const button = this.querySelector('button[type="submit"]');
    const spinner = button.querySelector('.spinner-border');
    
    button.disabled = true;
    spinner.classList.remove('d-none');
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 解析中...';
});
</script>
@endsection