<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../fixtures/sfMessageSource_Simple.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfMessageSourceTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ::factory()
        $this->diag('::factory()');
        $source = sfMessageSource::factory('Simple');
        $this->ok($source instanceof sfIMessageSource, '::factory() returns a sfMessageSource instance');

        // ->getCulture() ->setCulture()
        $this->diag('->getCulture() ->setCulture()');
        $source->setCulture('en');
        $this->is($source->getCulture(), 'en', '->setCulture() changes the source culture');
        $source->setCulture('fr');
        $this->is($source->getCulture(), 'fr', '->getCulture() gets the current source culture');
    }
}
