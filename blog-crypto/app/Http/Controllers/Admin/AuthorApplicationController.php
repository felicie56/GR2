<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorApplication;
use App\Models\Role;
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
            ->paginate(10);

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
        if (! $application->isPending()) {
            return redirect()
                ->route('admin.author-applications.show', $application)
                ->with('status', 'application-already-reviewed');
        }

        DB::transaction(function () use ($request, $application) {
            $authorRole = Role::where('name', 'AUTHOR')->firstOrFail();

            $application->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $application->user
                ->roles()
                ->syncWithoutDetaching([$authorRole->id]);
        });

        return redirect()
            ->route('admin.author-applications.index')
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
        ]);

        $application->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('admin.author-applications.index')
            ->with('status', 'author-application-rejected');
    }
}