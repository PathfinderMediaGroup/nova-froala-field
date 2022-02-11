<?php

namespace Froala\NovaFroalaField\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $table = 'nova_froala_attachments';

    protected $guarded = [];

    public function purge(): void
    {
        Storage::disk($this->disk)->delete($this->attachment);

        $this->delete();
    }
}
