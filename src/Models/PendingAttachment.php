<?php

namespace Froala\NovaFroalaField\Models;

use Froala\NovaFroalaField\Froala;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PendingAttachment extends Model
{
    protected $table = 'nova_pending_froala_attachments';

    protected $guarded = [];

    public static function persistDraft(string $draftId, Froala $field, $model): void
    {
        static::where('draft_id', $draftId)->get()->each->persist($field, $model);
    }

    public function persist(Froala $field, $model): void
    {
        Attachment::create([
            'attachable_type' => get_class($model),
            'attachable_id' => $model->getKey(),
            'attachment' => $this->attachment,
            'disk' => $field->disk,
            'url' => Storage::disk($field->disk)->url($this->attachment),
        ]);

        $this->delete();
    }

    public function purge(): void
    {
        Storage::disk($this->disk)->delete($this->attachment);

        $this->delete();
    }
}
