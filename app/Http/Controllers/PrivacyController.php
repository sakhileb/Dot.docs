<?php

namespace App\Http\Controllers;

use App\Services\UserDataExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PrivacyController extends Controller
{
    public function index(): View
    {
        return view('privacy.index');
    }

    public function rights(): View
    {
        $exportPath = session('gdpr_export_path');

        return view('privacy.rights', compact('exportPath'));
    }

    public function requestExport(UserDataExportService $exportService): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $relativePath = $exportService->exportUserData($user);

        session(['gdpr_export_path' => $relativePath]);

        return redirect()
            ->route('privacy.rights')
            ->with('status', 'Your data export has been generated and is ready to download.');
    }

    public function downloadExport(): Response|RedirectResponse
    {
        $relativePath = session('gdpr_export_path');

        if (!$relativePath || !Storage::disk(config('data-protection.disk', 'local'))->exists($relativePath)) {
            return redirect()
                ->route('privacy.rights')
                ->with('status', 'No export file found. Please request a new export.');
        }

        $disk = Storage::disk(config('data-protection.disk', 'local'));
        $fileName = basename($relativePath);

        return response(
            $disk->get($relativePath),
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]
        );
    }
}
