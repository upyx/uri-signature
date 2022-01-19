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

use function base64_encode;
use function explode;
use function http_build_query;
use function ksort;
use function parse_str;
use function rawurldecode;
use function rtrim;
use function sha1;
use function str_ends_with;
use function strstr;
use function strtr;

final class Signer
{
    private string $signParam;
    private string $secretKey;

    public function __construct(string $signParam, string $secretKey)
    {
        $this->signParam = $signParam;
        $this->secretKey = $secretKey;
    }

    public function sign(UriInterface $uri): UriInterface
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

    public function verify(UriInterface $uri): bool
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
        $params[$this->signParam] = $this->secretKey;

        ksort($params);

        $result = http_build_query($params);
        $result = sha1($result, true);
        $result = base64_encode($result);
        $result = strtr($result, '+/', '-_');
        $result = rtrim($result, '=');

        return $result;
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
