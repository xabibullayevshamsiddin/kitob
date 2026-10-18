<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiChatRedirectController extends Controller
{
    /**
     * 301 Permanent Redirect from legacy /ai-chat to catalog with a toast notice.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->route('books.catalog', [], 301)
            ->with('info', 'AI endi har bir kitob ichida');
    }
}
