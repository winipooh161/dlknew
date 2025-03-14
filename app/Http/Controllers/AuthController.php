<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Deal;

class AuthController extends Controller
{
    public function showLoginFormByPassword()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        $title_site = "Страница входа по паролю в Личный кабинет Экспресс-дизайн";
        return view('auth.login-password', compact('title_site'));
    }
    public function loginByPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required|string|min:6',
        ]);
        $user = User::where('phone', $request->phone)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            return redirect()->route('home');
        }
        return redirect()->back()->withErrors(['phone' => 'Неверный номер телефона или пароль.']);
    }
    public function showLoginFormByCode()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        $title_site = "Страница входа по коду в Личный кабинет Экспресс-дизайн";
        return view('auth.login-code', compact('title_site'));
    }
    public function loginByCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required|string|size:4',
        ]);
        $user = User::where('phone', $request->phone)->first();
        if ($user && $this->checkVerificationCode($request->code, $user)) {
            Auth::login($user);
            return redirect()->route('home');
        }
        return redirect()->back()->withErrors(['code' => 'Неверный код.']);
    }
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['error' => 'Пользователь с таким номером не найден.'], 400);
        }
        $this->sendVerificationCode($user);
        return response()->json(['success' => true]);
    }
    private function sendVerificationCode($user)
    {
        $code = rand(1000, 9999);
        $user->verification_code = $code;
        $user->verification_code_expires_at = now()->addMinutes(10);
        $user->save();
        $this->sendSms($user->phone, $code);
    }
    private function checkVerificationCode($code, $user)
    {
        return $code === $user->verification_code && now()->lessThanOrEqualTo($user->verification_code_expires_at);
    }
    private function sendSms($phone, $code)
    {
        $apiKey = '6CDCE0B0-6091-278C-5145-360657FF0F9B';
        $phone = preg_replace('/\D/', '', $phone);
        Http::get("https://sms.ru/sms/send", [
            'api_id' => $apiKey,
            'to' => $phone,
            'msg' => "Ваш код для входа: $code",
        ]);
    }
    public function showRegistrationForm()
    {
        $title_site = "Страница Регистрации в Личный кабинет Экспресс-дизайн";
        return view('auth.register', compact('title_site'));
    }
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'avatar_url' => '/storage/icon/profile.svg',
            'status' => 'user',
            'password' => Hash::make($validated['password']),
        ]);
        Auth::login($user);
        return redirect('home');
    }
    public function logout()
    {
        Auth::logout();
        Session::flush();
        Session::regenerateToken();
        return redirect('/');
    }
    public function registerByDealLink($token)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        $deal = Deal::where('registration_token', $token)
            ->where('registration_token_expiry', '>', now())
            ->first();

        if (!$deal) {
            return redirect()->route('login.password')->with('error', 'Ссылка на регистрацию устарела или неверна.');
        }

        $title_site = "Регистрация для сделки";

        return view('auth.register_by_deal', compact('deal', 'title_site'));
    }
    public function completeRegistrationByDeal(Request $request, $token)
    {
        $deal = Deal::where('registration_token', $token)
            ->where('registration_token_expiry', '>', now())
            ->first();
    
        if (!$deal) {
            return redirect()->route('login.password')->with('error', 'Ссылка на регистрацию устарела или неверна.');
        }
    
        $phone = preg_replace('/\D/', '', $request->input('phone'));
        $normalizedDealPhone = preg_replace('/\D/', '', $deal->client_phone);
    
        if ($normalizedDealPhone !== $phone) {
            return redirect()->route('login.password')->with('error', 'Регистрация возможна только для клиента сделки.');
        }
    
        if (User::where('phone', $phone)->exists()) {
            return redirect()->route('login.password')->with('error', 'Пользователь с таким номером телефона уже зарегистрирован.');
        }
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
    
        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'avatar_url' => '/storage/icon/profile.svg',
            'status' => 'user',
            'password' => Hash::make($validated['password']),
        ]);
    
        $deal->user_id = $user->id;
        $deal->status = 'Регистрация';
        $deal->registration_token = null;
        $deal->registration_token_expiry = null;
        $deal->save();
    
        $deal->users()->attach($user->id, ['role' => 'client']);
    
        $creator = $deal->creator;
        if ($creator && $creator->phone) {
            $rawPhone = preg_replace('/\D/', '', $creator->phone);
            $apiKey = '6CDCE0B0-6091-278C-5145-360657FF0F9B';
    
            $response = Http::get("https://sms.ru/sms/send", [
                'api_id' => $apiKey,
                'to' => $rawPhone,
                'msg' => "Клиент {$user->name} успешно зарегистрировался по сделке: {$deal->name}.",
                'partner_id' => 1,
            ]);
    
            if ($response->failed()) {
                \Log::error("Ошибка при отправке SMS создателю сделки", [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'phone' => $rawPhone,
                    'deal' => $deal->id,
                ]);
            }
        }
    
        Auth::login($user);
    
        return redirect()->route('home')->with('success', 'Вы успешно зарегистрированы и привязаны к сделке.');
    }
}
