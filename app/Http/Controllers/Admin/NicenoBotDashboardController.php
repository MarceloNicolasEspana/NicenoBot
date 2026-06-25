<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NicenoBotContentStatus;
use App\Http\Controllers\Controller;
use App\Models\NicenoBotContent;
use Illuminate\View\View;

class NicenoBotDashboardController extends Controller
{
    public function index(): View
    {
        $now = NicenoBotContent::now();

        $activeWeekly = NicenoBotContent::query()->activeWeekly($now)->first();

        $nextWeekly = NicenoBotContent::query()
            ->weekly()
            ->published()
            ->where('starts_at', '>', $now)
            ->orderBy('starts_at')
            ->first();

        $fixedPublished = NicenoBotContent::query()->fixed()->published()->count();

        $drafts = NicenoBotContent::query()
            ->where('status', NicenoBotContentStatus::Draft)
            ->count();

        return view('admin.nicenito.dashboard', compact(
            'activeWeekly',
            'nextWeekly',
            'fixedPublished',
            'drafts',
        ));
    }
}
