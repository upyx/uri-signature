<?php

declare(strict_types=1);

namespace Upyx\Test\UriSignature;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

final class SignerNyholmTest extends SignerTest
{
    protected function createUri(string $uri): UriInterface
    {
        return new Uri($uri);
    }
}
