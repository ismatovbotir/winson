<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramClient;
use Illuminate\Http\Request;

class TelegramClientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        return view('admin.telegram-clients.index', [
            'clients' => TelegramClient::withCount(['messages', 'leads'])
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
                ->orderByDesc('last_seen_at')->paginate(30)->withQueryString(),
            'q' => $q,
        ]);
    }

    public function show(TelegramClient $client)
    {
        $client->load(['conversations.messages', 'conversations.lead', 'leads.product']);

        return view('admin.telegram-clients.show', compact('client'));
    }

    /** Block / unblock: a blocked client gets no answers from the bot. */
    public function update(Request $request, TelegramClient $client)
    {
        $client->update(['is_blocked' => $request->boolean('is_blocked')]);

        return back()->with('status', __('admin.common.saved'));
    }
}
