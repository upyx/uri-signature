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

A simple tool to sing and verify URIs' query parameters to protect them from fraud.
It supports different hash algorithms including HMAC.

It depends on PRS-7 HTTP message implementation. It has been tested with
[Guzzle](https://github.com/guzzle/psr7) and [Nyholm](https://github.com/nyholm/psr7),
but you can try anyone.

## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

```bash
composer require upyx/uri-signature
```

If you got an error `Could not find package psr/http-message-implementation`, it means
you miss a PSR-7 implementation. Try

```bash
composer require nyholm/psr7
```

or

```bash
composer require guzzlehttp/psr7
```

## Usage

To sign query parameters

```php
use GuzzleHttp\Psr7\Uri;
use Upyx\UriSignature\Signer;
$signer = new Signer('sig', 's0me$ecret!', 'sha1');

$uri = new Uri('https://example.com/?sensitive=value');
$signed = $signer->signUriParams($uri);
echo $signed; // https://example.com/?sensitive=value&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY
```

To check them

```php
use GuzzleHttp\Psr7\Uri;
use Upyx\UriSignature\Signer;
$signer = new Signer('sig', 's0me$ecret!', 'sha1');

$signed = new Uri('https://example.com/?sensitive=value&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY');
$verified = $signer->verifyUriParams($signed); // true

$hacked = new Uri('https://example.com/?sensitive=changed&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY');
$failed = $signer->verifyUriParams($hacked); // false
```

It signs query parameters only!

```php
use GuzzleHttp\Psr7\Uri;
use Upyx\UriSignature\Signer;
$signer = new Signer('sig', 's0me$ecret!', 'sha1');

$signed1 = new Uri('//some.example.com/?sensitive=value&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY');
$signed2 = new Uri('//other.example.com/?sensitive=value&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY');
$signed3 = new Uri('/?sensitive=value&sig=YQ_1AXL5Cdspng1W7SETkdvsLoY');

$verified = $signer->verifyUriParams($signed1); // true
$verifiedToo = $signer->verifyUriParams($signed2); // true
$verifiedAgain = $signer->verifyUriParams($signed3); // true
```

Parameters are being sorted so that the order is not important

```php
use GuzzleHttp\Psr7\Uri;
use Upyx\UriSignature\Signer;
$signer = new Signer('sig', 's0me$ecret!', 'sha1');

$signed1 = new Uri('/?param1=value1&param2=vA%20e.&sig=m3EaBLndIFulvWGJqUuxGepv000');
$signed2 = new Uri('/?param2=vA%20e.&param1=value1&sig=m3EaBLndIFulvWGJqUuxGepv000');

$verified = $signer->verifyUriParams($signed1); // true
$verifiedToo = $signer->verifyUriParams($signed2); // true
```

However, ordering of arrays is

```php
use GuzzleHttp\Psr7\Uri;
use Upyx\UriSignature\Signer;
$signer = new Signer('sig', 's0me$ecret!', 'sha1');

$signed = new Uri('https://example.com/?param[]=1&param[]=2&sig=TZEYycd_uldtq0B3nHXlETRxT2Y');
$hacked = new Uri('https://example.com/?param[]=2&param[]=1&sig=TZEYycd_uldtq0B3nHXlETRxT2Y');

$verified = $signer->verifyUriParams($signed1); // true
$failed = $signer->verifyUriParams($hacked); // false
```

To check the supported algorithms, the functions
[hash_algos()](https://www.php.net/manual/en/function.hash-algos) and
[hash_hmac_algos()](https://www.php.net/manual/en/function.hash-hmac-algos)
can be used. To use HMAC add the `hmac-` prefix. For example:

```php
new Signer('sig', 's0me$ecret!', 'sha1');
new Signer('sig', 's0me$ecret!', 'md5');
new Signer('sig', 's0me$ecret!', 'hmac-sha1');
new Signer('sig', 's0me$ecret!', 'hmac-md5');
```

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Copyright and License

The upyx/uri-signature library is copyright © [Sergey Rabochiy](mailto:upyx.00@gmail.com)
and licensed for use under the terms of the
MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
