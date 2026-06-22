<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;

return static function (MBConfig $mbConfig): void {
    // Define package/apps directories
    $mbConfig->packageDirectories([
        __DIR__.'/app-modules',
        __DIR__.'/apps',
    ]);
};
