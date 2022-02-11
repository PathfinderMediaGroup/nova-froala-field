<?php

namespace Froala\NovaFroalaField\Jobs;

use Froala\NovaFroalaField\Models\PendingAttachment;

class PruneStaleAttachments
{
    public function __invoke(): void
    {
        PendingAttachment::query()
            ->where('created_at', '<=', now()->subDays(1))
            ->orderBy('id', 'desc')
            ->chunk(100, function ($attachments) {
                $attachments->each->purge();
            });
    }
}
