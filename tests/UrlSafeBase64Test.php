<?php

declare(strict_types=1);

namespace Upyx\Test\UriSignature;

use Upyx\UriSignature\UrlSafeBase64;

use function pack;

/**
 * @covers \Upyx\UriSignature\UrlSafeBase64
 */
final class UrlSafeBase64Test extends TestCase
{
    public function testBinary(): void
    {
        $binary = pack('H*', '5190c3e184b449c03e425b624fe0d031');

        $encoded = UrlSafeBase64::encode($binary);
        $decoded = UrlSafeBase64::decode($encoded);

        $this->assertSame($binary, $decoded);
    }
}
