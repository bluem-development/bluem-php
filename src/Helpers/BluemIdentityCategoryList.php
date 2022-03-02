<?php

namespace Bluem\BluemPHP\Helpers;

class BluemIdentityCategoryList {
    public $_cats = [];

    public function getCats(): array {
        return $this->_cats;
    }

    /**
     * @param array $cats
     *
     * @return void
     */
    public function addCat( $cat ) {
        if ( ! in_array( $cat, $this->_cats ) ) {
            $this->_cats[] = $cat;
        }
    }
}