<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ProjectServiceContainer extends sfServiceContainer
{
    public $__bar;
    public $__foo_bar;
    public $__foo_baz;

    public function __construct()
    {
        parent::__construct();

        $this->__bar = new stdClass();
        $this->__foo_bar = new stdClass();
        $this->__foo_baz = new stdClass();
    }

    protected function getBarService()
    {
        return $this->__bar;
    }

    protected function getFooBarService()
    {
        return $this->__foo_bar;
    }

    protected function getFoo_BazService()
    {
        return $this->__foo_baz;
    }
}
