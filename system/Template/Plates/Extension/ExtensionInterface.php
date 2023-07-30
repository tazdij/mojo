<?php

namespace Mojo\Template\Plates\Extension;

use Mojo\Template\Plates\Engine;

/**
 * A common interface for extensions.
 */
interface ExtensionInterface
{
    public function register(Engine $engine);
}
