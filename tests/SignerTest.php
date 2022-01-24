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
    /**
     * @return mixed[]
     */
    public function providerSignUriParams(): iterable
    {
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path?param1=value1&param2=vA%20e.',
            'expected' => 'https://example.com/some/path?param1=value1&param2=vA%20e.&sig=m3EaBLndIFulvWGJqUuxGepv000',
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'SHA1',
            'uri' => 'https://example.com/some/path?param2=vA%20e.&param1=value1',
            'expected' => 'https://example.com/some/path?param2=vA%20e.&param1=value1&sig=m3EaBLndIFulvWGJqUuxGepv000',
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path?param%5B%5D=1&param%5B%5D=2',
            'expected' => 'https://example.com/some/path?param%5B%5D=1&param%5B%5D=2&sig=TZEYycd_uldtq0B3nHXlETRxT2Y',
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'HMAC-MD5',
            'uri' => '//example.com/?p=v',
            'expected' => '//example.com/?p=v&sig=A2jiOSnhO4S5pN7yrSHpQQ',
        ];
        yield [
            'signParam' => 'hash',
            'secretKey' => 'key1',
            'algorithm' => 'sha1',
            'uri' => '//example.com/?p=v',
            'expected' => '//example.com/?p=v&hash=fM4oDHEDQXeDzgjpIh0w_plAzbg',
        ];
        yield [
            'signParam' => 'hash',
            'secretKey' => 'key2',
            'algorithm' => 'sha1',
            'uri' => '//example.com/?p=v',
            'expected' => '//example.com/?p=v&hash=DpOJTcX-SIVOt0bc0282tmFajpg',
        ];
    }

    /**
     * @dataProvider providerSignUriParams
     */
    public function testSignUriParams(
        string $signParam,
        string $secretKey,
        string $algorithm,
        string $uri,
        string $expected
    ): void {
        $signer = new Signer($signParam, $secretKey, $algorithm);

        $signed = (string) $signer->signUriParams($this->createUri($uri));

        $this->assertSame($expected, $signed);
    }

    /**
     * @return mixed[]
     */
    public function providerVerifyUriParams(): iterable
    {
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'SHA1',
            'uri' => 'https://example.com/some/path?param1=value1&param2=vA%20e.&sig=m3EaBLndIFulvWGJqUuxGepv000',
            'expected' => true,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path?param2=vA%20e.&param1=value1&sig=m3EaBLndIFulvWGJqUuxGepv000',
            'expected' => true,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path?param%5B%5D=1&param%5B%5D=2&sig=TZEYycd_uldtq0B3nHXlETRxT2Y',
            'expected' => true,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path?param%5B%5D=2&param%5B%5D=1&sig=TZEYycd_uldtq0B3nHXlETRxT2Y',
            'expected' => false,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'HMAC-MD5',
            'uri' => '//example.com/?p=v&sig=A2jiOSnhO4S5pN7yrSHpQQ',
            'expected' => true,
        ];
        yield [
            'signParam' => 'hash',
            'secretKey' => 'key1',
            'algorithm' => 'sha1',
            'uri' => '//example.com/?p=v&hash=fM4oDHEDQXeDzgjpIh0w_plAzbg',
            'expected' => true,
        ];
        yield [
            'signParam' => 'hash',
            'secretKey' => 'key2',
            'algorithm' => 'sha1',
            'uri' => '//example.com/?p=v&hash=DpOJTcX-SIVOt0bc0282tmFajpg',
            'expected' => true,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => '//example.com/?p=w&p=v&sig=95-P_S7wP6TA6aaDq_sq6R33YvA',
            'expected' => false,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path?param2=vA%20e.&param1=value1',
            'expected' => false,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path?noparam&sig=some',
            'expected' => false,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'https://example.com/some/path',
            'expected' => false,
        ];
        yield [
            'signParam' => 'sig',
            'secretKey' => 's0me$ecret!',
            'algorithm' => 'sha1',
            'uri' => 'sig',
            'expected' => false,
        ];
    }

    /**
     * @dataProvider providerVerifyUriParams
     */
    public function testVerifyUriParams(
        string $signParam,
        string $secretKey,
        string $algorithm,
        string $uri,
        bool $expected
    ): void {
        $signer = new Signer($signParam, $secretKey, $algorithm);

        $verified = $signer->verifyUriParams($this->createUri($uri));

        $this->assertSame($expected, $verified);
    }

    /**
     * @return mixed[]
     */
    public function providerIsAlgorithmSupported(): iterable
    {
        yield ['sha1', true];
        yield ['SHA1', true];
        yield ['md5', true];
        yield ['hmac-md5', true];
        yield ['hmac-SHA1', true];
        yield ['wrong', false];
        yield ['hmac-wrong', false];
    }

    /**
     * @dataProvider providerIsAlgorithmSupported
     */
    public function testIsAlgorithmSupported(string $algorithm, bool $expected): void
    {
        $this->assertSame($expected, Signer::isAlgorithmSupported($algorithm));
    }

    public function testNoParametersToSing(): void
    {
        $uri = 'https://example.com/some/path';
        $signer = new Signer('sig', 's0me$ecret!', 'sha1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There is no parameters to sing.');

        $signer->signUriParams($this->createUri($uri));
    }

    public function testCannotBeSigned(): void
    {
        $uri = 'https://example.com/some/path?param=1&param=2';
        $signer = new Signer('sig', 's0me$ecret!', 'sha1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The uri has doubled parameters and cannot be signed.');

        $signer->signUriParams($this->createUri($uri));
    }

    public function testAlreadySigned(): void
    {
        $uri = 'https://example.com/some/path?param2=vA%20e.&param1=value1&sig=m3EaBLndIFulvWGJqUuxGepv000';
        $signer = new Signer('sig', 's0me$ecret!', 'sha1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The uri is signed already.');

        $signer->signUriParams($this->createUri($uri));
    }

    public function testUnsupportedHashAlgorithm(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The "unknown" hash algorithm is not supported.');

        new Signer('sig', 's0me$ecret!', 'unknown');
    }

    public function testUnsupportedHmacAlgorithm(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The "unknown" HMAC algorithm is not supported.');

        new Signer('sig', 's0me$ecret!', 'hmac-unknown');
    }

    public function testLegacy(): void
    {
        $signer = new Signer('sig', 's0me$ecret!');

        $signed = $signer->sign($this->createUri('/?p=v'));
        $verified = $signer->verify($signed);

        $this->assertTrue($verified);
    }

    abstract protected function createUri(string $uri): UriInterface;
}
