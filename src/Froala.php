<?php

namespace Froala\NovaFroalaField;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
use Froala\NovaFroalaField\Handlers\DetachAttachment;
use Froala\NovaFroalaField\Handlers\DeleteAttachments;
use Froala\NovaFroalaField\Handlers\AttachedImagesList;
use Froala\NovaFroalaField\Handlers\StorePendingAttachment;
use Froala\NovaFroalaField\Handlers\DiscardPendingAttachments;
use Laravel\Nova\Trix\PendingAttachment as TrixPendingAttachment;
use Froala\NovaFroalaField\Models\PendingAttachment as FroalaPendingAttachment;

class Froala extends Trix
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-froala-field';

    const DRIVER_NAME = 'froala';

    /**
     * The callback that should be executed to return attached images list.
     *
     * @var callable
     */
    public $imagesCallback;

    /** {@inheritdoc} */
    public function __construct(string $name, ?string $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $uploadLimits = [
            'fileMaxSize',
            'imageMaxSize',
            'videoMaxSize',
        ];

        $uploadMaxFilesize = $this->getUploadMaxFilesize();

        foreach ($uploadLimits as $key => $property) {
            $uploadLimits[$property] = $uploadMaxFilesize;
            unset($uploadLimits[$key]);
        }

        $this->withMeta([
            'options' => config('nova.froala-field.options', []) + $uploadLimits,
            'draftId' => Str::uuid(),
            'attachmentsDriver' => config('nova.froala-field.attachments_driver'),
        ]);
    }

    /**
     * Determine the server 'upload_max_filesize' as bytes.
     *
     * @return int
     */
    protected function getUploadMaxFilesize(): int
    {
        $uploadMaxFilesize = config('nova.froala-field.upload_max_filesize')
                            ?? ini_get('upload_max_filesize');

        if (is_numeric($uploadMaxFilesize)) {
            return $uploadMaxFilesize;
        }

        $metric = strtoupper(substr($uploadMaxFilesize, -1));
        $uploadMaxFilesize = (int) $uploadMaxFilesize;

        switch ($metric) {
            case 'K':
                return $uploadMaxFilesize * 1024;
            case 'M':
                return $uploadMaxFilesize * 1048576;
            case 'G':
                return $uploadMaxFilesize * 1073741824;
            default:
                return $uploadMaxFilesize;
        }
    }

    /**
     * Ability to pass any existing Froala options to the editor instance.
     * Refer to the Froala documentation {@link https://www.froala.com/wysiwyg-editor/docs/options}
     * to view a list of all available options.
     *
     * @param array $options
     * @return self
     */
    public function options(array $options)
    {
        return $this->withMeta([
            'options' => array_merge($this->meta['options'], $options),
        ]);
    }

    /**
     * Specify that file uploads should not be allowed.
     */
    public function withFiles($disk = null)
    {
        $this->withFiles = true;

        $this->disk($disk);

        if (config('nova.froala-field.attachments_driver', self::DRIVER_NAME) !== self::DRIVER_NAME) {
            $this->images(new AttachedImagesList($this));

            return parent::withFiles($disk);
        }

        $this->attach(new StorePendingAttachment($this))
            ->detach(new DetachAttachment)
            ->delete(new DeleteAttachments($this))
            ->discard(new DiscardPendingAttachments)
            ->images(new AttachedImagesList($this))
            ->prunable();

        return $this;
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     */
    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if (isset($this->fillCallback)) {
            call_user_func(
                $this->fillCallback,
                $request,
                $model,
                $attribute,
                $requestAttribute
            );
        }

        $this->fillAttributeFromRequest(
            $request,
            $requestAttribute,
            $model,
            $attribute
        );

        if ($request->{$this->attribute.'DraftId'} && $this->withFiles) {
            $pendingAttachmentClass =
                config('nova.froala-field.attachments_driver', self::DRIVER_NAME) === self::DRIVER_NAME
                ? FroalaPendingAttachment::class
                : TrixPendingAttachment::class;

            if ($model->exists) {
                $pendingAttachmentClass::persistDraft(
                    $request->{$this->attribute.'DraftId'},
                    $this,
                    $model
                );
            } else {
                $modelClass = get_class($model);

                $modelClass::saved(function ($model) use ($request, $pendingAttachmentClass) {
                    if ($model->wasRecentlyCreated) {
                        $pendingAttachmentClass::persistDraft(
                            $request->{$this->attribute.'DraftId'},
                            $this,
                            $model
                        );
                    }
                });
            }
        }
    }

    public function showOnIndex()
    {
        $this->showOnIndex = true;

        return $this;
    }

    /**
     * Specify the callback that should be used to get attached images list.
     *
     * @param  callable  $imagesCallback
     * @return $this
     */
    public function images(callable $imagesCallback)
    {
        $this->withFiles = true;

        $this->imagesCallback = $imagesCallback;

        return $this;
    }
}
