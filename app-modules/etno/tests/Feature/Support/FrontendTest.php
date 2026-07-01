<?php

use Metafori\Etno\Support\Frontend;

it('generates document detail url with locale and id', function () {
    config()->set('frontend.url', 'http://localhost:3000');

    $url = Frontend::documentUrl('AD000001', 'sk');

    expect($url)->toBe('http://localhost:3000/sk/documents/AD000001');
});

it('generates document detail url using default locale if not specified', function () {
    config()->set('frontend.url', 'http://localhost:3000');
    app()->setLocale('en');

    $url = Frontend::documentUrl('AD000002');

    expect($url)->toBe('http://localhost:3000/en/documents/AD000002');
});

it('generates item detail url with locale and id', function () {
    config()->set('frontend.url', 'http://localhost:3000');

    $url = Frontend::itemUrl('AD000001:a', 'sk');

    expect($url)->toBe('http://localhost:3000/sk/items/AD000001%3Aa');
});

it('generates item detail url using default locale if not specified', function () {
    config()->set('frontend.url', 'http://localhost:3000');
    app()->setLocale('en');

    $url = Frontend::itemUrl('AD000001:b');

    expect($url)->toBe('http://localhost:3000/en/items/AD000001%3Ab');
});
