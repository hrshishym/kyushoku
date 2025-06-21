<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>給食献立管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('menu.index') }}">給食献立管理</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="{{ route('menu.index') }}">ホーム</a>
                    @auth
                    <a class="nav-link" href="{{ route('menu.upload.form') }}">月次PDFアップロード</a>
                    <a class="nav-link" href="{{ route('menu.monthly') }}">月別表示</a>
                    @endauth
                </div>
                <div class="navbar-nav">
                    @auth
                        <span class="navbar-text me-3">
                            こんにちは、{{ Auth::user()->name }}さん
                        </span>
                        <a class="nav-link" href="{{ route('profile.edit') }}">プロフィール</a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link px-lg-3 py-2 border-0" style="color: rgba(255,255,255,.55);">
                                ログアウト
                            </button>
                        </form>
                    @else
                        <a class="nav-link" href="{{ route('login') }}">ログイン</a>
                        <a class="nav-link" href="{{ route('register') }}">新規登録</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif

        @yield('content')
        {{ $slot ?? '' }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
