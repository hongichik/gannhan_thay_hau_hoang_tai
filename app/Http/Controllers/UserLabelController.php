<?php

namespace App\Http\Controllers;

use App\Models\Intrusion;
use App\Models\IntrusionLabel;
use App\Models\Subdomain;
use App\Models\SubdomainLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserLabelController extends Controller
{
    public function home(): View
    {
        return view('user.home');
    }

    public function showIntrusionLabelPage(Request $request): View
    {
        $userId = Auth::id();
        $relabelMode = $request->boolean('relabel');

        $editingIntrusionId = $request->integer('edit_intrusion_id');
        $editingLabel = null;

        if ($editingIntrusionId) {
            $editingLabel = IntrusionLabel::where('user_id', $userId)
                ->where('intrusion_id', $editingIntrusionId)
                ->first();
        }

        if ($editingLabel) {
            $intrusion = Intrusion::find($editingLabel->intrusion_id);
        } else {
            $intrusion = Intrusion::whereDoesntHave('intrusionLabels', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->orderBy('id')->first();
        }

        $total = Intrusion::count();
        $done = IntrusionLabel::where('user_id', $userId)->count();
        $currentLabel = $intrusion
            ? IntrusionLabel::where('user_id', $userId)->where('intrusion_id', $intrusion->id)->first()
            : null;

        return view('user.label-intrusions', compact('intrusion', 'total', 'done', 'editingIntrusionId', 'currentLabel', 'relabelMode'));
    }

    public function intrusionHistory(Request $request): View
    {
        $userId = Auth::id();
        $currentIntrusionId = $request->integer('edit_intrusion_id');
        $relabelMode = $request->boolean('relabel', true);

        $currentLabelQuery = IntrusionLabel::with('intrusion')->where('user_id', $userId);

        if ($currentIntrusionId) {
            $currentLabelQuery->where('intrusion_id', $currentIntrusionId);
        }

        $currentLabel = $currentLabelQuery->orderBy('intrusion_id')->first();

        if (!$currentLabel && $currentIntrusionId) {
            $currentLabel = IntrusionLabel::with('intrusion')
                ->where('user_id', $userId)
                ->orderBy('intrusion_id')
                ->first();
        }

        $total = IntrusionLabel::where('user_id', $userId)->count();
        $position = $currentLabel
            ? IntrusionLabel::where('user_id', $userId)->where('intrusion_id', '<=', $currentLabel->intrusion_id)->count()
            : 0;

        return view('user.intrusion-history', compact('currentLabel', 'total', 'position', 'relabelMode'));
    }

    public function storeIntrusionLabel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'intrusion_id' => ['required', 'exists:intrusions,id'],
            'outlier_id' => ['required', 'integer', 'between:1,6'],
            'relabel_mode' => ['nullable', 'boolean'],
        ]);

        IntrusionLabel::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'intrusion_id' => $data['intrusion_id'],
            ],
            [
                'outlier_id' => $data['outlier_id'],
            ]
        );

        if ((bool) ($data['relabel_mode'] ?? false)) {
            $nextLabel = IntrusionLabel::where('user_id', Auth::id())
                ->where('intrusion_id', '>', $data['intrusion_id'])
                ->orderBy('intrusion_id')
                ->first();

            if ($nextLabel) {
                return redirect()->route('user.label.intrusions.history', [
                    'edit_intrusion_id' => $nextLabel->intrusion_id,
                    'relabel' => 1,
                ])->with('success', 'Đã cập nhật nhãn cho intrusion.');
            }

            return redirect()->route('user.label.intrusions.history')->with('success', 'Đã gắn nhãn lại xong toàn bộ intrusions đã chọn trước đó.');
        }

        return redirect()->route('user.label.intrusions')->with('success', 'Đã lưu nhãn cho intrusion.');
    }

    public function showSubdomainLabelPage(Request $request): View
    {
        $userId = Auth::id();
        $relabelMode = $request->boolean('relabel');

        $editingSubdomainId = $request->integer('edit_subdomain_id');
        $editingLabel = null;

        if ($editingSubdomainId) {
            $editingLabel = SubdomainLabel::where('user_id', $userId)
                ->where('subdomain_id', $editingSubdomainId)
                ->first();
        }

        if ($editingLabel) {
            $subdomain = Subdomain::find($editingLabel->subdomain_id);
        } else {
            $subdomain = Subdomain::whereDoesntHave('subdomainLabels', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->orderBy('id')->first();
        }

        $total = Subdomain::count();
        $done = SubdomainLabel::where('user_id', $userId)->count();
        $currentLabel = $subdomain
            ? SubdomainLabel::where('user_id', $userId)->where('subdomain_id', $subdomain->id)->first()
            : null;

        return view('user.label-subdomains', compact('subdomain', 'total', 'done', 'editingSubdomainId', 'currentLabel', 'relabelMode'));
    }

    public function subdomainHistory(Request $request): View
    {
        $userId = Auth::id();
        $currentSubdomainId = $request->integer('edit_subdomain_id');
        $relabelMode = $request->boolean('relabel', true);

        $currentLabelQuery = SubdomainLabel::with('subdomain')->where('user_id', $userId);

        if ($currentSubdomainId) {
            $currentLabelQuery->where('subdomain_id', $currentSubdomainId);
        }

        $currentLabel = $currentLabelQuery->orderBy('subdomain_id')->first();

        if (!$currentLabel && $currentSubdomainId) {
            $currentLabel = SubdomainLabel::with('subdomain')
                ->where('user_id', $userId)
                ->orderBy('subdomain_id')
                ->first();
        }

        $total = SubdomainLabel::where('user_id', $userId)->count();
        $position = $currentLabel
            ? SubdomainLabel::where('user_id', $userId)->where('subdomain_id', '<=', $currentLabel->subdomain_id)->count()
            : 0;

        return view('user.subdomain-history', compact('currentLabel', 'total', 'position', 'relabelMode'));
    }

    public function storeSubdomainLabel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subdomain_id' => ['required', 'exists:subdomains,id'],
            'label' => ['required', 'in:yes,no'],
            'relabel_mode' => ['nullable', 'boolean'],
        ]);

        SubdomainLabel::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'subdomain_id' => $data['subdomain_id'],
            ],
            [
                'label' => $data['label'],
            ]
        );

        if ((bool) ($data['relabel_mode'] ?? false)) {
            $nextLabel = SubdomainLabel::where('user_id', Auth::id())
                ->where('subdomain_id', '>', $data['subdomain_id'])
                ->orderBy('subdomain_id')
                ->first();

            if ($nextLabel) {
                return redirect()->route('user.label.subdomains.history', [
                    'edit_subdomain_id' => $nextLabel->subdomain_id,
                    'relabel' => 1,
                ])->with('success', 'Đã cập nhật nhãn cho subdomain.');
            }

            return redirect()->route('user.label.subdomains.history')->with('success', 'Đã gắn nhãn lại xong toàn bộ subdomains đã chọn trước đó.');
        }

        return redirect()->route('user.label.subdomains')->with('success', 'Đã lưu nhãn cho subdomain.');
    }
}
