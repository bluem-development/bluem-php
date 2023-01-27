<?php

namespace Bluem\BluemPHP\Helpers;

class IdentityCategoryList {
    
    private array $cats = [];

    public function getCats(): array {
        return $this->cats;
    }

    /**
     * @param $cat
     */
    public function addCat( $cat ): IdentityCategoryList {
        if ( ! in_array( $cat, $this->cats ) ) {
            $this->cats[] = $cat;
        }
        return $this;
    }
}