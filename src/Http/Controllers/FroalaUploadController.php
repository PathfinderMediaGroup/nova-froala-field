<?php

namespace Froala\NovaFroalaField\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class FroalaUploadController extends Controller
{
    public function store(NovaRequest $request): JsonResponse
    {
        $field = $request->newResource()
            ->availableFields($request)
            ->findFieldByAttribute($request->field, function () {
                abort(404);
            });

        return response()->json(['link' => call_user_func(
            $field->attachCallback,
            $request
        )]);
    }

    public function destroyAttachment(NovaRequest $request): mixed
    {
        $field = $request->newResource()
            ->availableFields($request)
            ->findFieldByAttribute($request->field, function () {
                abort(404);
            });

        return call_user_func($field->detachCallback, $request);
    }

    public function destroyPending(NovaRequest $request): mixed
    {
        $field = $request->newResource()
            ->availableFields($request)
            ->findFieldByAttribute($request->field, function () {
                abort(404);
            });

        return call_user_func($field->discardCallback, $request);
    }
}
