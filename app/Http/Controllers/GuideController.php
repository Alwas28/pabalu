<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use League\CommonMark\CommonMarkConverter;

class GuideController extends Controller
{
    public function index(): View
    {
        $markdown = Setting::get('user_guide', '');
        $html     = '';

        if ($markdown) {
            $converter = new CommonMarkConverter([
                'html_input'         => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $html = $converter->convert($markdown)->getContent();
        }

        return view('guide.index', compact('html', 'markdown'));
    }

    public function edit(): View
    {
        $content = Setting::get('user_guide', '');
        return view('guide.edit', compact('content'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['content' => ['nullable', 'string']]);

        Setting::set('user_guide', $request->input('content', ''));
        Cache::forget('setting:user_guide');

        ActivityLog::record('update_guide', 'Panduan penggunaan diperbarui.');

        return redirect()->route('guide.index')->with('success', 'Panduan berhasil disimpan.');
    }
}
