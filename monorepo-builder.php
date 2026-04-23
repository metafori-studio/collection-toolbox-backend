<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;

return static function (MBConfig $mbConfig): void {
    // where are the packages located?
    $mbConfig->packageDirectories([
        __DIR__.'/packages',
        __DIR__.'/apps',
    ]);

    // optionally add other configuration here
};
