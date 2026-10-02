<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramClient;
use App\Support\AiAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/** /admin → Chatbot training: edit the assistant's instructions, and a test chat. */
class BotTrainingController extends Controller
{
    public function instructions()
    {
        return view('admin.bot.instructions', [
            'content' => (string) file_get_contents(AiAssistant::instructionsPath()),
            'overridden' => AiAssistant::instructionsOverridden(),
        ]);
    }

    /** Saved under storage/ so code deploys never overwrite what admins taught. */
    public function saveInstructions(Request $request)
    {
        $data = $request->validate(['content' => ['required', 'string', 'max:60000']]);

        $path = storage_path(AiAssistant::INSTRUCTIONS_OVERRIDE);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, str_replace("\r\n", "\n", $data['content']));

        return back()->with('status', __('admin.common.saved'));
    }

    /** Back to the default file shipped with the code. */
    public function resetInstructions()
    {
        File::delete(storage_path(AiAssistant::INSTRUCTIONS_OVERRIDE));

        return back()->with('status', __('admin.bot.reset_done'));
    }

    public function playground()
    {
        return view('admin.bot.playground', ['question' => null, 'reply' => null, 'configured' => AiAssistant::configured()]);
    }

    /** Ask the assistant exactly as a fresh Telegram client would (nothing is saved). */
    public function ask(Request $request, AiAssistant $ai)
    {
        $data = $request->validate(['question' => ['required', 'string', 'max:2000']]);

        $reply = $ai->answer(new TelegramClient(['first_name' => 'Admin test']), $data['question']);

        return view('admin.bot.playground', [
            'question' => $data['question'],
            'reply' => $reply,
            'failed' => $reply === null,
            'locale' => AiAssistant::guessLocale($data['question']),
            'configured' => AiAssistant::configured(),
        ]);
    }
}
