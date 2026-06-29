<?php

/**
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Bluem\BluemPHP\Interfaces;

use Bluem\BluemPHP\Requests\BluemRequest;
use SimpleXMLElement;

interface BluemRequestInterface
{
    public function HttpRequestURL(): string;

    public function RequestContext();

    public function RequestType(): string;

    public function Xml(): SimpleXMLElement;

    public function XmlString(): string;

    public function XmlWrapDebtorAdditionalData(): string;

    public function addAdditionalData($key, $value): BluemRequest;

    public function getContext();

    public function retrieveBICCodes(): array;

    public function retrieveBICObjects(): array;
}
