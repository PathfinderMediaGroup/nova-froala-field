<?php

namespace Froala\NovaFroalaField\Handlers;

use Froala\NovaFroalaField\Models\Attachment;
use Illuminate\Http\Request;

class DetachAttachment
{
    public function __invoke(Request $request): void
    {
        Attachment::where('url', $request->src)
                        ->get()
                        ->each
                        ->purge();
    }
}
