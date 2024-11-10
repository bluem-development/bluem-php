<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Helpers;

class BluemIdentityCategoryList
{
    /**
     * @var string[] $categories
     */
    public array $categories = [];

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function Add(string $cat): void
    {
        if (!in_array($cat, $this->categories, true)) {
            $this->categories[] = $cat;
        }
    }
}
