<?php

namespace Froala\NovaFroalaField\Handlers;

use Froala\NovaFroalaField\Froala;
use Froala\NovaFroalaField\Models\Attachment;
use Illuminate\Http\Request;

class DeleteAttachments
{
    public function __construct(public Froala $field)
    {
    }

    public function __invoke(Request $request, $model): array
    {
        Attachment::where('attachable_type', get_class($model))
                ->where('attachable_id', $model->getKey())
                ->get()
                ->each
                ->purge();

        return [$this->field->attribute => ''];
    }
}
