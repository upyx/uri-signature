<?php

/**
 * This file is part of upyx/uri-signature
 *
 * upyx/uri-signature is open source software: you can distribute
 * it and/or modify it under the terms of the MIT License
 * (the "License"). You may not use this file except in
 * compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Sergey Rabochiy <upyx.00@gmail.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Upyx\UriSignature;

use Psr\Http\Message\UriInterface;
use RuntimeException;

use function explode;
use function hash;
use function hash_algos;
use function hash_hmac;
use function hash_hmac_algos;
use function http_build_query;
use function in_array;
use function ksort;
use function parse_str;
use function rawurldecode;
use function sprintf;
use function str_ends_with;
use function strpos;
use function strstr;
use function strtolower;
use function substr;

final class Signer
{
    private string $signParam;
    private string $secretKey;
    private string $algorithm;
    private bool $hmac;

    public function __construct(string $signParam, string $secretKey, string $algorithm = 'sha1')
    {
        self::checkAlgorithm($algorithm, $isHmac, $isSupported);
        if (!$isSupported) {
            throw new RuntimeException(
                $isHmac
                    ? sprintf('The "%s" HMAC algorithm is not supported.', $algorithm)
                    : sprintf('The "%s" hash algorithm is not supported.', $algorithm),
            );
        }

        $this->signParam = $signParam;
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
        $this->hmac = (bool) $isHmac;
    }

    public static function isAlgorithmSupported(string $algorithm): bool
    {
        self::checkAlgorithm($algorithm, $isHmac, $isSupported);

        return (bool) $isSupported;
    }

    private static function checkAlgorithm(string &$algorithm, ?bool &$isHmac, ?bool &$isSupported): void
    {
        $algorithm = strtolower($algorithm);

        if (strpos($algorithm, 'hmac-') === 0) {
            $algorithm = substr($algorithm, 5);
            $supported = hash_hmac_algos();
            $isHmac = true;
        } else {
            $supported = hash_algos();
            $isHmac = false;
        }

        $isSupported = in_array($algorithm, $supported, true);
    }

    /**
     * @deprecated use signUriParams instead
     */
    public function sign(UriInterface $uri): UriInterface
    {
        return $this->signUriParams($uri);
    }

    public function signUriParams(UriInterface $uri): UriInterface
    {
        parse_str($uri->getQuery(), $params);

        if (!$params) {
            throw new RuntimeException('There is no parameters to sing.');
        }
        if (isset($params[$this->signParam])) {
            throw new RuntimeException('The uri is signed already.');
        }
        if (self::hasDoubledParameters($uri->getQuery())) {
            throw new RuntimeException('The uri has doubled parameters and cannot be signed.');
        }

        return $uri->withQuery($uri->getQuery() . '&' . $this->signParam . '=' . $this->makeSignature($params));
    }

    /**
     * @deprecated use verifyUriParams instead
     */
    public function verify(UriInterface $uri): bool
    {
        return $this->verifyUriParams($uri);
    }

    public function verifyUriParams(UriInterface $uri): bool
    {
        parse_str($uri->getQuery(), $params);

        if (!$params) {
            return false;
        }
        if (!isset($params[$this->signParam])) {
            return false;
        }
        if (self::hasDoubledParameters($uri->getQuery())) {
            return false;
        }

        return $this->makeSignature($params) === $params[$this->signParam];
    }

    /**
     * @param mixed[] $params
     */
    private function makeSignature(array $params): string
    {
        if ($this->hmac) {
            unset($params[$this->signParam]);
        } else {
            $params[$this->signParam] = $this->secretKey;
        }

        ksort($params);

        $query = http_build_query($params);

        if ($this->hmac) {
            $signature = hash_hmac($this->algorithm, $query, $this->secretKey, true);
        } else {
            $signature = hash($this->algorithm, $query, true);
        }

        return UrlSafeBase64::encode($signature);
    }

    private static function hasDoubledParameters(string $query): bool
    {
        $doubles = [];
        foreach (explode('&', $query) as $pair) {
            $name = strstr($pair, '=', true);
            if (!$name) {
                return true;
            }

            $name = rawurldecode($name);

            if (str_ends_with($name, '[]')) {
                continue;
            }
            if (isset($doubles[$name])) {
                return true;
            }

            $doubles[$name] = true;
        }

        return false;
    }
}
