<?php

declare(strict_types=1);

/**
 * © 2026 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Interfaces;

interface BluemResponseInterface
{
    public function Error(): string;

    public function ReceivedResponse(): bool;

    public function Status(): bool;
}
