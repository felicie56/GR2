<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthorApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $applications = AuthorApplication::with(['user', 'reviewer'])
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.author_applications.index', [
            'applications' => $applications,
            'status' => $status,
        ]);
    }

    public function show(AuthorApplication $application): View
    {
        $application->load(['user', 'reviewer']);

        return view('admin.author_applications.show', [
            'application' => $application,
        ]);
    }

    public function approve(Request $request, AuthorApplication $application): RedirectResponse
    {
        $application->load('user');

        if (! $application->isPending()) {
            return redirect()
                ->route('admin.author-applications.show', $application)
                ->with('status', 'application-already-reviewed');
        }

        if (! $application->user) {
            return redirect()
                ->route('admin.author-applications.index')
                ->with('error', 'Không tìm thấy người dùng của đơn đăng ký này.');
        }

        DB::transaction(function () use ($request, $application) {
            $application->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
                'user_seen_at' => null,
            ]);

            /*
             * Database hiện tại của project đang dùng cột users.role.
             * Không dùng role_id hoặc bảng pivot roles ở đây để tránh lỗi 404/500.
             */
            $application->user->forceFill([
                'role' => 'AUTHOR',
            ])->save();
        });

        return redirect()
            ->route('admin.author-applications.index', ['status' => 'pending'])
            ->with('status', 'author-application-approved');
    }

    public function reject(Request $request, AuthorApplication $application): RedirectResponse
    {
        if (! $application->isPending()) {
            return redirect()
                ->route('admin.author-applications.show', $application)
                ->with('status', 'application-already-reviewed');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'rejection_reason.required' => 'Vui lòng nhập lý do từ chối.',
            'rejection_reason.min' => 'Lý do từ chối nên có ít nhất 10 ký tự.',
            'rejection_reason.max' => 'Lý do từ chối không được vượt quá 2000 ký tự.',
        ]);

        $application->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'user_seen_at' => null,
        ]);

        return redirect()
            ->route('admin.author-applications.index', ['status' => 'pending'])
            ->with('status', 'author-application-rejected');
    }
}