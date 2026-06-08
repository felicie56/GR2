<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Hiển thị form đăng nhập.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        /*
         * Nếu là ADMIN thì luôn đưa thẳng về Admin Dashboard.
         * Không dùng redirect()->intended() cho admin,
         * vì intended có thể kéo admin quay lại /blog hoặc trang cũ trước đó.
         */
        if (
            $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('ADMIN')
            && Route::has('admin.dashboard')
        ) {
            return redirect()->route('admin.dashboard');
        }

        /*
         * Nếu là AUTHOR thì có thể đưa về trang bài của tôi.
         * Nếu bạn muốn author vẫn về trang chủ thì đổi route này thành home.
         */
        if (
            $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('AUTHOR')
            && Route::has('blog.my')
        ) {
            return redirect()->route('blog.my');
        }

        /*
         * USER thường về trang chủ.
         */
        return redirect()->route('home');
    }

    /**
     * Đăng xuất.
     */
    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}