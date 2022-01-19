<?php

declare(strict_types=1);

namespace Upyx\Test\UriSignature;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

final class SignerGuzzleTest extends SignerTest
{
    protected function createUri(string $uri): UriInterface
    {
        return new Uri($uri);
    }
}
