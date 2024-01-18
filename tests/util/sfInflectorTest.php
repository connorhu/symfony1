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
require_once __DIR__.'/../../lib/util/sfToolkit.class.php';
require_once __DIR__.'/../../lib/util/sfInflector.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfInflectorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ::camelize()
        $this->diag('::camelize()');
        $this->is(sfInflector::camelize('symfony'), 'Symfony', '::camelize() upper-case the first letter');
        $this->is(sfInflector::camelize('symfony_is_great'), 'SymfonyIsGreat', '::camelize() upper-case each letter after a _ and remove _');

        // ::underscore()
        $this->diag('::underscore()');
        $this->is(sfInflector::underscore('Symfony'), 'symfony', '::underscore() lower-case the first letter');
        $this->is(sfInflector::underscore('SymfonyIsGreat'), 'symfony_is_great', '::underscore() lower-case each upper-case letter and add a _ before');
        $this->is(sfInflector::underscore('HTMLTest'), 'html_test', '::underscore() lower-case all other letters');

        // ::humanize()
        $this->diag('::humanize()');
        $this->is(sfInflector::humanize('symfony'), 'Symfony', '::humanize() upper-case the first letter');
        $this->is(sfInflector::humanize('symfony_is_great'), 'Symfony is great', '::humanize() replaces _ by a space');
    }
}
