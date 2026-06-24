<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NicenitoContentStatus;
use App\Http\Controllers\Controller;
use App\Models\NicenitoContent;
use Illuminate\View\View;

class NicenitoDashboardController extends Controller
{
    public function index(): View
    {
        $now = NicenitoContent::now();

        $activeWeekly = NicenitoContent::query()->activeWeekly($now)->first();

        $nextWeekly = NicenitoContent::query()
            ->weekly()
            ->published()
            ->where('starts_at', '>', $now)
            ->orderBy('starts_at')
            ->first();

        $fixedPublished = NicenitoContent::query()->fixed()->published()->count();

        $drafts = NicenitoContent::query()
            ->where('status', NicenitoContentStatus::Draft)
            ->count();

        return view('admin.nicenito.dashboard', compact(
            'activeWeekly',
            'nextWeekly',
            'fixedPublished',
            'drafts',
        ));
    }
}
