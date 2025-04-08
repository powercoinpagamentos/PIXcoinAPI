<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\Cliente;
use App\Models\Pessoa;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $user = Cliente::where('email', $request->email)->first()
            ?? Pessoa::where('email', $request->email)->first();

        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'E-mail não cadastrado'
            ], 404);
        }

        $token = Str::random(60);

        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $resetLink = url("/reset-password/{$token}");
        Mail::to($request->email)->send(new PasswordResetMail($resetLink, $user->nome));

        return new JsonResponse([
            'error' => false,
            'message' => 'Link de recuperação enviado para seu e-mail. Verifique seu e-mail e caixa de SPAM'
        ]);
    }

    public function showResetForm($token): View|Factory|Application
    {
        $reset = DB::table('password_resets')
            ->where('token', $token)
            ->first();

        if (!$reset) {
            abort(404, 'Token inválido ou expirado');
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): Application|Redirector|RedirectResponse
    {
        $reset = DB::table('password_resets')
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['token' => 'Token inválido ou expirado']);
        }

        $user = Cliente::where('email', $reset->email)->first()
            ?? Pessoa::where('email', $reset->email)->first();

        $user->senha = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')
            ->where('token', $request->token)
            ->delete();

        return redirect()->away('https://app.pixcoinapi.com');
    }
}
