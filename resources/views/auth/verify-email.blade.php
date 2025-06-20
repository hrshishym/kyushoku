@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">メールアドレス認証</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5>認証が必要です</h5>
                    <p class="mb-0">
                        ご登録ありがとうございます！アプリケーションをご利用いただく前に、
                        登録時にご入力いただいたメールアドレスに送信された認証リンクをクリックして、
                        メールアドレスの認証を完了してください。
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success">
                        <strong>認証メールを再送信しました！</strong>
                        ご登録時にご入力いただいたメールアドレスに新しい認証リンクを送信いたしました。
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            認証メールを再送信
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            ログアウト
                        </button>
                    </form>
                </div>

                <hr class="my-4">
                <div class="text-muted">
                    <small>
                        <strong>メールが届かない場合：</strong><br>
                        • 迷惑メールフォルダをご確認ください<br>
                        • しばらく待ってから「認証メールを再送信」ボタンをクリックしてください
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
