<?php

namespace Froala\NovaFroalaField\Handlers;

use Froala\NovaFroalaField\Models\PendingAttachment;
use Illuminate\Http\Request;

class DiscardPendingAttachments
{
    public function __invoke(Request $request): void
    {
        PendingAttachment::where('draft_id', $request->draftId)
                    ->get()
                    ->each
                    ->purge();
    }
}
