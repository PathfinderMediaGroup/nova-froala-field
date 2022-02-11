<?php

namespace Froala\NovaFroalaField\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Nova\Http\Controllers\TrixAttachmentController;
use Laravel\Nova\Http\Requests\NovaRequest;

class FroalaToTrixAttachmentAdapterController extends TrixAttachmentController
{
    public function store(NovaRequest $request): JsonResponse
    {
        $response = parent::store($request);

        return $response->setData([
            'link' => $response->getData()->url,
        ]);
    }
}
