<p align="center"><img alt="Nova Froala Field" src="docs/froala-nova.png" width="380"></p>

<p align="center"><strong>Froala WYSIWYG Editor</strong> field for Laravel Nova</p>

<p align="center">
  <a href="https://packagist.org/packages/froala/nova-froala-field"><img src="https://img.shields.io/packagist/v/froala/nova-froala-field.svg?style=flat-square" alt="Latest Version on Packagist"></img></a>
  <a href="https://travis-ci.org/froala/nova-froala-field"><img src="https://img.shields.io/travis/froala/nova-froala-field/master.svg?style=flat-square" alt="Build Status"></img></a>
  <a href="https://github.styleci.io/repos/151840835"><img src="https://github.styleci.io/repos/151840835/shield?branch=master" alt="Code Style Status"></a>
  <a href="https://packagist.org/packages/froala/nova-froala-field"><img src="https://img.shields.io/packagist/dt/froala/nova-froala-field.svg?style=flat-square" alt="Total Downloads"></a>
</p>

## Introduction

This is a fork of the original `froala/nova-froala-field` repository. The original is no longer maintained. I created this fork because our company needs a working version of Froala in Nova. This fork contains an updated version of Froala (v4) **without** 3rd party plugin support (you can read [here](#3rd-party-integrations) why).

### Upgrading from v3 to v4

This version has some new minimum requirements:
- `PHP` ^8.0
- `laravel/framework` ^9.0
- `laravel/nova` ^3.0
- `league/flysystem` ^3.0 (default with Laravel 9)

You may replace `"froala/nova-froala-field": "^3.x"` with `"pathfindermediagroup/nova-froala-field": "^4.0"` in your `composer.json` if you were using the old version from Froala. Just make sure to force publish the new assets like so:
```bash
php artisan vendor:publish --tag=froala-styles --provider=Froala\\NovaFroalaField\\FroalaFieldServiceProvider --force
```

You may also remove the `public/vendor/nova/froala` folder from your project, as it is no longer needed or supported.

### Froala WYSIWYG Editor Field

Full support of attaching Images, Files and Videos

![Form Field](.github/docs/form-field.png)

Notifications for _Froala_ events are handled by [Toasted](https://nova.laravel.com/docs/1.0/customization/frontend.html#notifications) which is provided in _Nova_ by default.


## Installation

You can install the package into a Laravel application that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require pathfindermediagroup/nova-froala-field
```

## Usage

Just use the `Froala\NovaFroalaField\Froala` field in your Nova resource:

```php
namespace App\Nova;

use Froala\NovaFroalaField\Froala;

class Article extends Resource
{
    // ...

    public function fields(Request $request)
    {
        return [
            // ...

            Froala::make('Content'),

            // ...
        ];
    }
}
```

## Override Config Values

To change any of config values for _froala field_, publish a config file:

```bash
php artisan vendor:publish --tag=config --provider=Froala\\NovaFroalaField\\FroalaFieldServiceProvider
```

## Customize Editor Options

For changing any [Available Froala Option](https://www.froala.com/wysiwyg-editor/docs/options)
edit `nova.froala-field.options` value:

```php
/*
|--------------------------------------------------------------------------
| Default Editor Options
|--------------------------------------------------------------------------
|
| Setup default values for any Froala editor option.
|
| To view a list of all available options check out the Froala documentation
| {@link https://www.froala.com/wysiwyg-editor/docs/options}
|
*/

'options' => [
    'toolbarButtons' => [
        [
            'bold',
            'italic',
            'underline',
        ],
        [
            'formatOL',
            'formatUL',
        ],
        [
            'insertImage',
            'insertFile',
            'insertLink',
            'insertVideo',
        ],
        [
            'html',
        ],
    ],
],

//...
```

If you want to set options only to specific field, just pass them to `options` method:

```php
public function fields(Request $request)
{
    return [
        // ...

        Froala::make('Content')->options([
            'editorClass' => 'custom-class',
            'height' => 300,
        ]),

        // ...
    ];
}
```

## Attachments

**Nova Froala Field** provides native attachments driver which works similar to [Trix File Uploads](https://nova.laravel.com/docs/1.0/resources/fields.html#file-uploads), but with ability to optimize images and preserve file names.
Also you have an ability to switch to the `trix` driver to use its upload system.

* It's Recommended to use `froala` driver (enabled by default) to be able to use current and future
 additional features for attachments, provided by *Froala*.

### Froala Driver

To use `froala` driver, publish and run a migration:

```bash
php artisan vendor:publish --tag=migrations --provider=Froala\\NovaFroalaField\\FroalaFieldServiceProvider 
php artisan migrate
```

### Trix Driver

If previously you have used *Trix* attachments and you want to preserve behavior with same tables and handlers
you can use `trix` driver in config file:

```php
/*
|--------------------------------------------------------------------------
| Editor Attachments Driver
|--------------------------------------------------------------------------
|
| If you have used `Trix` previously and want to save the same flow with
| `Trix` attachments handlers and database tables you can use
| "trix" driver.
|
| *** Note that "trix" driver doesn't support image optimization
| and file names preservation.
|
| It is recommended to use "froala" driver to be able to automatically
| optimize uploaded images and preserve attachments file names.
|
| Supported: "froala", "trix"
|
*/

'attachments_driver' => 'trix'

//...
```

### Attachments Usage

To allow users to upload images, files and videos, just like with _Trix_ field, chain the `withFiles` method onto the field's definition. When calling the `withFiles` method, you should pass the name of the filesystem disk that photos should be stored on:

```php
use Froala\NovaFroalaField\Froala;

Froala::make('Content')->withFiles('public');
```

And also, in your `app/Console/Kernel.php` file, you should register a [daily job](https://laravel.com/docs/5.7/scheduling) to prune any stale attachments from the pending attachments table and storage:

```php
use Froala\NovaFroalaField\Jobs\PruneStaleAttachments;


/**
* Define the application's command schedule.
*
* @param  \Illuminate\Console\Scheduling\Schedule  $schedule
* @return void
*/
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        (new PruneStaleAttachments)();
    })->daily();
}
```

#### Filenames Preservation

A unique ID is generated by default to serve as the file name according to `store` [method specification](https://laravel.com/docs/master/filesystem#file-uploads).
If you want to preserve original client filenames for uploaded attachments, change `preserve_file_names` option in config file to `true`.

```php
/*
|--------------------------------------------------------------------------
| Preserve Attachments File Name
|--------------------------------------------------------------------------
|
| Ability to preserve client original file name for uploaded
| image, file or video.
|
*/

'preserve_file_names' => true,

//...
```

#### Images Optimization

All uploaded images will be optimized by default by [spatie/image-optimizer](https://github.com/spatie/image-optimizer).

You can disable image optimization in config file:

```php
/*
|--------------------------------------------------------------------------
| Automatically Images Optimization
|--------------------------------------------------------------------------
|
| Optimize all uploaded images by default.
|
*/

'optimize_images' => false,

//...
```

Or set custom optimization options for any optimizer:

```php
/*
|--------------------------------------------------------------------------
| Image Optimizers Setup
|--------------------------------------------------------------------------
|
| These are the optimizers that will be used by default.
| You can setup custom parameters for each optimizer.
|
*/

'image_optimizers' => [
    Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
        '-m85', // this will store the image with 85% quality. This setting seems to satisfy Google's Pagespeed compression rules
        '--strip-all', // this strips out all text information such as comments and EXIF data
        '--all-progressive', // this will make sure the resulting image is a progressive one
    ],
    Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
        '--force', // required parameter for this package
    ],
    Spatie\ImageOptimizer\Optimizers\Optipng::class => [
        '-i0', // this will result in a non-interlaced, progressive scanned image
        '-o2', // this set the optimization level to two (multiple IDAT compression trials)
        '-quiet', // required parameter for this package
    ],
    Spatie\ImageOptimizer\Optimizers\Svgo::class => [
        '--disable=cleanupIDs', // disabling because it is known to cause troubles
    ],
    Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
        '-b', // required parameter for this package
        '-O3', // this produces the slowest but best results
    ],
],
```

> Image optimization currently supported only for local filesystems

### Upload Max Filesize

You can set max upload filesize for attachments. If set to `null`, max upload filesize equals to _php.ini_ `upload_max_filesize` directive value.

```php
/*
|--------------------------------------------------------------------------
| Maximum Possible Size for Uploaded Files
|--------------------------------------------------------------------------
|
| Customize max upload filesize for uploaded attachments.
| By default it is set to "null", it means that default value is
| retrieved from `upload_max_size` directive of php.ini file.
|
| Format is the same as for `uploaded_max_size` directive.
| Check out FAQ page, to get more detail description.
| {@link http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes}
|
*/

'upload_max_filesize' => null,

//...
```

## Display Edited Content

According to _Froala_ [Display Edited Content](https://www.froala.com/wysiwyg-editor/docs/overview#frontend) documentation you should publish _Froala_ styles:

```bash
php artisan vendor:publish --tag=froala-styles --provider=Froala\\NovaFroalaField\\FroalaFieldServiceProvider 
```

include into view where an edited content is shown:

```blade
<!-- CSS rules for styling the element inside the editor such as p, h1, h2, etc. -->
<link href="{{ asset('css/vendor/froala_styles.min.css') }}" rel="stylesheet" type="text/css" />
```

Also, you should make sure that you put the edited content inside an element that has the `.fr-view` class:

```html
<div class="fr-view">
    {!! $article->content !!}
</div>
```

## Show on Index Page

You have an ability to show field content on resource index page in popup window:

```php
use Froala/NovaFroalaField/Froala;

Froala::make('Content')->showOnIndex();
```

Just click **Show Content**

![Index Field](.github/docs/index-field.png)

## License Key

To setup your license key, uncomment `key` option in the config file and set `FROALA_KEY` environment variable

```php
// ...
'options' => [
    'key' => env('FROALA_KEY'),
    // ...
],
```

## 3rd Party Integrations

I decided to remove 3rd party integrations like embedly and Tui. The reason for this being is that I wanted an updated version of Froala in Nova. I could not get it to work properly with the 3rd party integrations. I do not use them, and thus I am not inclined to spend more time on trying to get them to work. If you know how to do this, feel free to create a pull request.

## Advanced

### Custom Event Handlers

If you want to setup custom event handlers for froala editor instance, create js file and assign `events` property to `window.froala`:

```javascript
window.froala = {
    events: {
        'image.error': (error, response) => {},
        'imageManager.error': (error, response) => {},
        'file.error': (error, response) => {},
    }
};
```

to all callbacks provided in `window.froala.events`, the context of _VueJS_ form field component is automatically applied, you can work with `this` inside callbacks like with _Vue_ instance component.

After that, load the js file into _Nova_ scripts in `NovaServiceProvider::boot` method:

```php
public function boot()
{
    parent::boot();

    Nova::serving(function (ServingNova $event) {
        Nova::script('froala-event-handlers', public_path('path/to/js/file.js'));
    });
}
```

### Customize Attachment Handlers

You can change any of attachment handlers by passing a `callable`:

```php
use App\Nova\Handlers\{
    StorePendingAttachment,
    DetachAttachment,
    DeleteAttachments,
    DiscardPendingAttachments,
    AttachedImagesList
};

Froala::make('Content')
    ->attach(new StorePendingAttachment)
    ->detach(new DetachAttachment)
    ->delete(new DeleteAttachments)
    ->discard(new DiscardPendingAttachments)
    ->images(new AttachedImagesList)
```

## Development

You may get started with this package as follows (after cloning the repository):
```bash
composer install
yarn install
```

### Fixing code-style
PHP
```bash
composer cgl
```
JS
```bash
yarn format
```

### Testing

``` bash
composer test
```

### Building dev assets
```bash
yarn dev
```

### Building production assets
```bash
yarn prod
```

## Contributing

To contribute, simply make a pull request to this repository with your changes. Make sure they are documented well in your pull request description.

## Credits

- [Slava Razum](https://github.com/slavarazum) For creating the original plugin
- [Woeler](https://github.com/slavarazum) For updating the abandoned plugin to Froala version 4, Laravel version 9 and Mix version 6
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-contributors]: ../../contributors
