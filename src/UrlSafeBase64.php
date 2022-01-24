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

use function base64_decode;
use function base64_encode;
use function rtrim;
use function strtr;

final class UrlSafeBase64
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function encode(string $toEncode): string
    {
        $encoded = base64_encode($toEncode);
        $encoded = strtr($encoded, '+/', '-_');
        $encoded = rtrim($encoded, '=');

        return $encoded;
    }

    public static function decode(string $toDecode): string
    {
        $encoded = strtr($toDecode, '-_', '+/');
        $decoded = base64_decode($encoded, true);

        return (string) $decoded;
    }
}
