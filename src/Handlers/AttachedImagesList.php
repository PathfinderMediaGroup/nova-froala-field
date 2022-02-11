<?php

namespace Froala\NovaFroalaField\Handlers;

use Froala\NovaFroalaField\Froala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachedImagesList
{
    public function __construct(public Froala $field)
    {
    }

    public function __invoke(Request $request): array
    {
        $images = [];

        $disk = Storage::disk($this->field->disk);

        foreach ($disk->allFiles() as $file) {
            if (! app()->runningUnitTests() && Str::before((string) $disk->getMimetype($file), '/') !== 'image') {
                continue;
            }

            $url = $disk->url($file);
            $images[] = [
                'url' => $url,
                'thumb' => $url,
            ];
        }

        return $images;
    }
}
