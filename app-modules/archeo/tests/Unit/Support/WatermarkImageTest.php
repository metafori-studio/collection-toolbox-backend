<?php

use Metafori\Archeo\Support\WatermarkImage;

it('accepts https urls', function () {
    expect(WatermarkImage::isUsable('https://example.com/watermark.png'))->toBeTrue();
});

it('rejects unusable watermark values', function (?string $value) {
    expect(WatermarkImage::isUsable($value))->toBeFalse();
})->with([
    'null' => null,
    'empty' => '',
    'http url' => 'http://example.com/watermark.png',
    'missing local file' => '/nonexistent/watermark.png',
]);

it('accepts a local file that exists', function () {
    $path = sys_get_temp_dir().'/'.uniqid('wm_', true).'.png';
    file_put_contents($path, 'fake');

    expect(WatermarkImage::isUsable($path))->toBeTrue();

    @unlink($path);
});
