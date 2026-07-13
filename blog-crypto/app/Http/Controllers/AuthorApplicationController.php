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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash'],

            'headline' => ['required', 'string', 'max:160'],
            'bio' => ['required', 'string', 'min:30', 'max:2000'],

            'occupation' => ['nullable', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],

            'website_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],

            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'expertise_areas' => ['required', 'array', 'min:1'],
            'expertise_areas.*' => ['string', 'max:80'],

            'experience_summary' => ['required', 'string', 'min:50', 'max:3000'],
            'motivation' => ['required', 'string', 'min:30', 'max:2000'],

            'sample_article_title' => ['required', 'string', 'max:255'],
            'sample_article_content' => ['required', 'string', 'min:200'],

            'truthful_information_confirmed' => ['accepted'],
            'content_policy_confirmed' => ['accepted'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
                'username' => $validated['username'] ?? $user->username,
                'bio' => $validated['bio'],
                'headline' => $validated['headline'],
                'occupation' => $validated['occupation'] ?? null,
                'organization' => $validated['organization'] ?? null,
                'location' => $validated['location'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'x_url' => $validated['x_url'] ?? null,
                'experience_years' => $validated['experience_years'],
                'expertise_areas' => $validated['expertise_areas'],
                'profile_completed_at' => now(),
            ]);

            AuthorApplication::create([
                'user_id' => $user->id,
                'status' => 'pending',

                'full_name' => $validated['name'],
                'public_name' => $validated['username'] ?: $validated['name'],
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
            ]);
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

    if (in_array($application->status, ['approved', 'rejected']) && is_null($application->user_seen_at)) {
        $application->update([
            'user_seen_at' => now(),
        ]);
    }

    return back();
}
}