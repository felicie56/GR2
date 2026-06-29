<?php

namespace App\Http\Controllers;

use App\Models\AuthorApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthorApplicationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $pendingApplication = $user->authorApplications()
            ->where('status', 'pending')
            ->latest()
            ->first();

        $latestApplication = $user->authorApplications()
            ->latest()
            ->first();

        return view('author_applications.create', [
            'user' => $user,
            'pendingApplication' => $pendingApplication,
            'latestApplication' => $latestApplication,
        ]);
    }

    public function store(Request $request): RedirectResponse
{
    $user = $request->user();

    if ($user->hasRole('AUTHOR')) {
        return redirect()
            ->route('home')
            ->with('success', 'Bạn đã là tác giả.');
    }

    $hasPendingApplication = $user->authorApplications()
        ->where('status', 'pending')
        ->exists();

    if ($hasPendingApplication) {
        return redirect()
            ->route('author.apply.create')
            ->with('warning', 'Bạn đã có một đơn đang chờ duyệt.');
    }

    $validated = $request->validate([
        'full_name' => ['required', 'string', 'max:255'],
        'public_name' => ['nullable', 'string', 'max:255'],

        'headline' => ['required', 'string', 'max:255'],

        'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
        'expertise_areas' => ['required', 'array', 'min:1'],
        'expertise_areas.*' => ['string', 'max:100'],

        'experience_summary' => ['required', 'string', 'min:30', 'max:3000'],
        'motivation' => ['required', 'string', 'min:30', 'max:2000'],

        'sample_article_title' => ['required', 'string', 'max:255'],
        'sample_article_content' => ['required', 'string', 'min:100'],

        'website_url' => ['nullable', 'url', 'max:255'],
        'linkedin_url' => ['nullable', 'url', 'max:255'],
        'x_url' => ['nullable', 'url', 'max:255'],

        'truthful_information_confirmed' => ['accepted'],
        'content_policy_confirmed' => ['accepted'],
    ], [
        'full_name.required' => 'Vui lòng nhập họ tên thật.',
        'full_name.max' => 'Họ tên thật không được vượt quá 255 ký tự.',

        'public_name.max' => 'Tên công khai / bút danh không được vượt quá 255 ký tự.',

        'headline.required' => 'Vui lòng nhập headline chuyên môn.',
        'headline.max' => 'Headline chuyên môn không được vượt quá 255 ký tự.',

        'experience_years.required' => 'Vui lòng nhập số năm kinh nghiệm.',
        'experience_years.integer' => 'Số năm kinh nghiệm phải là số nguyên.',
        'experience_years.min' => 'Số năm kinh nghiệm không hợp lệ.',
        'experience_years.max' => 'Số năm kinh nghiệm không hợp lệ.',

        'expertise_areas.required' => 'Vui lòng chọn ít nhất một lĩnh vực chuyên môn.',
        'expertise_areas.array' => 'Lĩnh vực chuyên môn không hợp lệ.',
        'expertise_areas.min' => 'Vui lòng chọn ít nhất một lĩnh vực chuyên môn.',

        'experience_summary.required' => 'Vui lòng nhập phần mô tả kinh nghiệm.',
        'experience_summary.min' => 'Phần mô tả kinh nghiệm nên có ít nhất 30 ký tự.',
        'experience_summary.max' => 'Phần mô tả kinh nghiệm không được vượt quá 3000 ký tự.',

        'motivation.required' => 'Vui lòng nhập lý do muốn trở thành tác giả.',
        'motivation.min' => 'Phần lý do nên có ít nhất 30 ký tự.',
        'motivation.max' => 'Phần lý do không được vượt quá 2000 ký tự.',

        'sample_article_title.required' => 'Vui lòng nhập tiêu đề bài viết mẫu.',
        'sample_article_title.max' => 'Tiêu đề bài viết mẫu không được vượt quá 255 ký tự.',

        'sample_article_content.required' => 'Vui lòng nhập nội dung bài viết mẫu.',
        'sample_article_content.min' => 'Bài viết mẫu nên có ít nhất 100 ký tự để admin có thể đánh giá.',

        'website_url.url' => 'Website cá nhân phải là một đường link hợp lệ.',
        'linkedin_url.url' => 'LinkedIn phải là một đường link hợp lệ.',
        'x_url.url' => 'Link X/Twitter phải là một đường link hợp lệ.',

        'truthful_information_confirmed.accepted' => 'Bạn cần xác nhận các thông tin cung cấp là đúng sự thật.',
        'content_policy_confirmed.accepted' => 'Bạn cần xác nhận sẽ tuân thủ chính sách nội dung của website.',
    ]);

    DB::transaction(function () use ($user, $validated) {
        $user->forceFill([
            'name' => $validated['full_name'],
            'username' => $validated['public_name'] ?? $user->username,
            'headline' => $validated['headline'],
            'bio' => $validated['experience_summary'],
            'website_url' => $validated['website_url'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'x_url' => $validated['x_url'] ?? null,
            'experience_years' => $validated['experience_years'],
            'expertise_areas' => $validated['expertise_areas'],
            'profile_completed_at' => now(),
        ])->save();

        $application = new AuthorApplication();

        $application->forceFill([
            'user_id' => $user->id,
            'status' => 'pending',

            'full_name' => $validated['full_name'],
            'public_name' => $validated['public_name'] ?? $validated['full_name'],
            'headline' => $validated['headline'],

            'experience_years' => $validated['experience_years'],
            'expertise_areas' => $validated['expertise_areas'],

            'experience_summary' => $validated['experience_summary'],
            'motivation' => $validated['motivation'],

            'sample_article_title' => $validated['sample_article_title'],
            'sample_article_content' => $validated['sample_article_content'],

            'website_url' => $validated['website_url'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'x_url' => $validated['x_url'] ?? null,

            'truthful_information_confirmed' => true,
            'content_policy_confirmed' => true,
        ])->save();
    });

    return redirect()
        ->route('author.apply.create')
        ->with('success', 'Đơn đăng ký tác giả của bạn đã được gửi thành công. Vui lòng chờ admin duyệt.');
}

    public function markSeen(AuthorApplication $application): RedirectResponse
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($application->status, ['approved', 'rejected'], true) && is_null($application->user_seen_at)) {
            $application->update([
                'user_seen_at' => now(),
            ]);
        }

        return back();
    }
}