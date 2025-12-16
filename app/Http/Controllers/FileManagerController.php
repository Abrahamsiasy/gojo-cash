<?php

namespace App\Http\Controllers;

use App\Policies\FileManagerPolicy;
use App\Services\FileManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FileManagerController extends Controller
{
    public function __construct(private FileManagerService $fileManagerService) {}

    /**
     * Display a listing of transactions with attachments.
     */
    public function index(Request $request): View
    {
        $policy = new FileManagerPolicy;
        $user = Auth::user();
        if (! $user || ! $policy->viewAny($user)) {
            abort(403);
        }
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.file-manager.index', $this->fileManagerService->getIndexData($searchValue));
    }
}
