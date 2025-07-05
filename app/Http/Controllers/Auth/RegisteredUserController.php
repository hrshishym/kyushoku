<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
                'code' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
    [
                'name.required' => '名前は必須です。',
                'name.max' => '名前は255文字以内で入力してください。',
                'email.required' => 'メールアドレスは必須です。',
                'email.email' => 'メールアドレスの形式が正しくありません。',
                'email.max' => 'メールアドレスは255文字以内で入力してください。',
                'email.unique' => 'このメールアドレスはすでに使用されています。',
                'password.required' => 'パスワードは必須です。',
                'password.confirmed' => 'パスワードが一致しません。',
                'password.min' => 'パスワードは少なくとも8文字以上で入力してください。',
                'password.regex' => 'パスワードは英字と数字を含む必要があります。',
                'code.required' => '招待コードは必須です。',
                'code.max' => '招待コードは255文字以内で入力してください。',
            ]
        );

        // Check if the code is valid
        if ($request->code !== config('auth.invitation_code')) {
            return back()->withErrors(['code' => '無効な招待コードです。'])->withInput([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
