<?php

declare(strict_types=1);

namespace Upyx\Test\UriSignature;

use Psr\Http\Message\UriInterface;
use RuntimeException;
use Upyx\UriSignature\Signer;

/**
 * @covers \Upyx\UriSignature\Signer
 */
abstract class SignerTest extends TestCase
{
    private Signer $signer;

    protected function setUp(): void
    {
        $this->signer = new Signer('sig', 's0me$ecret!');
    }

    /**
     * @return mixed[]
     */
    public function providerSign(): iterable
    {
        yield [
            'https://example.com/some/path?param1=value1&param2=vA%20e.',
            'https://example.com/some/path?param1=value1&param2=vA%20e.&sig=m3EaBLndIFulvWGJqUuxGepv000',
        ];
        yield [
            'https://example.com/some/path?param2=vA%20e.&param1=value1',
            'https://example.com/some/path?param2=vA%20e.&param1=value1&sig=m3EaBLndIFulvWGJqUuxGepv000',
        ];
        yield [
            'https://example.com/some/path?param%5B%5D=1&param%5B%5D=2',
            'https://example.com/some/path?param%5B%5D=1&param%5B%5D=2&sig=TZEYycd_uldtq0B3nHXlETRxT2Y',
        ];
        yield [
            '//example.com/?p=v',
            '//example.com/?p=v&sig=95-P_S7wP6TA6aaDq_sq6R33YvA',
        ];
    }

    /**
     * @dataProvider providerSign
     */
    public function testSign(string $uri, string $expected): void
    {
        $signed = (string) $this->signer->sign($this->createUri($uri));

        $this->assertSame($expected, $signed);
    }

    /**
     * @return mixed[]
     */
    public function providerVerify(): iterable
    {
        yield [
            'https://example.com/some/path?param1=value1&param2=vA%20e.&sig=m3EaBLndIFulvWGJqUuxGepv000',
            true,
        ];
        yield [
            'https://example.com/some/path?param2=vA%20e.&param1=value1&sig=m3EaBLndIFulvWGJqUuxGepv000',
            true,
        ];
        yield [
            '//example.com/?p=v&sig=95-P_S7wP6TA6aaDq_sq6R33YvA',
            true,
        ];
        yield [
            '//example.com/?p=w&p=v&sig=95-P_S7wP6TA6aaDq_sq6R33YvA',
            false,
        ];
        yield [
            'https://example.com/some/path?param2=vA%20e.&param1=value1',
            false,
        ];
        yield [
            'https://example.com/some/path?noparam&sig=some',
            false,
        ];
        yield [
            'https://example.com/some/path',
            false,
        ];
        yield [
            'sig',
            false,
        ];
    }

    /**
     * @dataProvider providerVerify
     */
    public function testVerify(string $uri, bool $expected): void
    {
        $verified = $this->signer->verify($this->createUri($uri));

        $this->assertSame($expected, $verified);
    }

    public function testNoParametersToSing(): void
    {
        $uri = 'https://example.com/some/path';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There is no parameters to sing.');

        $this->signer->sign($this->createUri($uri));
    }

    public function testCannotBeSigned(): void
    {
        $uri = 'https://example.com/some/path?param=1&param=2';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The uri has doubled parameters and cannot be signed.');

        $this->signer->sign($this->createUri($uri));
    }

    public function testAlreadySigned(): void
    {
        $uri = 'https://example.com/some/path?param2=vA%20e.&param1=value1&sig=m3EaBLndIFulvWGJqUuxGepv000';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The uri is signed already.');

        $this->signer->sign($this->createUri($uri));
    }

    abstract protected function createUri(string $uri): UriInterface;
}
