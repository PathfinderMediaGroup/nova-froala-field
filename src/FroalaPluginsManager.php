<?php

namespace Froala\NovaFroalaField;

use Illuminate\Contracts\Config\Repository;

class FroalaPluginsManager implements FroalaPlugins
{
    public function __construct(private Repository $config)
    {
    }

    public function import(): void
    {
    }
}
