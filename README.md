<h1 align="center">upyx/uri-signature</h1>

<p align="center">
    <strong>Sing and verify URIs.</strong>
</p>

<p align="center">
    <a href="https://github.com/upyx/uri-signature"><img src="https://img.shields.io/badge/source-upyx/uri--signature-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/upyx/uri-signature"><img src="https://img.shields.io/packagist/v/upyx/uri-signature.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/upyx/uri-signature.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/upyx/uri-signature/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/upyx/uri-signature.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <a href="https://github.com/upyx/uri-signature/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/workflow/status/upyx/uri-signature/build/main?style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/upyx/uri-signature"><img src="https://img.shields.io/codecov/c/gh/upyx/uri-signature?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
    <a href="https://shepherd.dev/github/upyx/uri-signature"><img src="https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fupyx%2Furi-signature%2Fcoverage" alt="Psalm Type Coverage"></a>
</p>


## About

A simple tool to sing and verify URIs to protect them from fraud.

It depends on PRS-7 HTTP message implementation. It has been tested with
[Guzzle](https://github.com/guzzle/psr7) and [Nyholm](https://github.com/nyholm/psr7),
but you can try anyone.

## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require upyx/uri-signature
```

If you got an error `Could not find package psr/http-message-implementation`, it means
you miss a PSR-7 implementation. Try

``` bash
composer require nyholm/psr7
```

or

``` bash
composer require guzzlehttp/psr7
```

## Usage

``` php
use GuzzleHttp\Psr7\Uri;
use Upyx\UriSignature\Signer;
$signer = new Signer('sig', 's0me$ecret!');
$uri = new Uri('https://example.com/?sensitive=value');
$signed = $signer->sign($uri); // https://example.com/?sensitive=value&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY
$verified = $signer->verify($signed); // true

// use the same parameter name and the secret
$signer = new Signer('sig', 's0me$ecret!');
$hack = new Uri('https://example.com/?sensitive=changed&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY');
$failed = $signer->verify($signed); // false
```

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Copyright and License

The upyx/uri-signature library is copyright © [Sergey Rabochiy](mailto:upyx.00@gmail.com)
and licensed for use under the terms of the
MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
