<?php

namespace App\Support;

use App\Models\BotKnowledge;
use Illuminate\Support\Collection;

/** An assistant answer: plain text + setting guides to deliver verbatim after it. */
class AiReply
{
    /** @param  Collection<int, BotKnowledge>  $guides */
    public function __construct(public string $text, public Collection $guides) {}
}
