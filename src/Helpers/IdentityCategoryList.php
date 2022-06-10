<?php

namespace Bluem\BluemPHP\Helpers;

class IdentityCategoryList {
    
    private $cats = [];

    public function getCats(): array {
        return $this->cats;
    }

    /**
     * @param $cat
     *
     * @return IdentityCategoryList
     */
    public function addCat( $cat ): IdentityCategoryList {
        if ( ! in_array( $cat, $this->cats ) ) {
            $this->cats[] = $cat;
        }
        return $this;
    }
}