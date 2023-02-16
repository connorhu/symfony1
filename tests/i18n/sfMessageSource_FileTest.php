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
class sfMessageSource_FileTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $source = sfMessageSource::factory('Simple', __DIR__.'/../fixtures/messages');
        $source->setCulture('fr_FR');

        // ->getCatalogueByDir()
        $this->diag('->getCatalogueByDir()');
        $this->is($source->getCatalogueByDir('messages'), array('fr_FR/messages.xml', 'fr/messages.xml'), '->getCatalogueByDir() returns catalogues by directory');

        // ->getCatalogueList()
        $this->diag('->getCatalogueList()');
        $this->is($source->getCatalogueList('messages'), array('fr_FR/messages.xml', 'fr/messages.xml', 'messages.fr_FR.xml', 'messages.fr.xml', 'messages.xml'), '->getCatalogueByDir() returns all catalogues for the current culture');

        // ->getSource()
        $this->diag('->getSource()');
        $this->is($source->getSource('fr_FR/messages.xml'), __DIR__.'/../fixtures/messages/fr_FR/messages.xml', '->getSource() returns the full path name to a specific variant');

        // ->isValidSource()
        $this->diag('->isValidSource()');
        $this->is($source->isValidSource($source->getSource('fr_FR/messages.xml')), false, '->isValidSource() returns false if the source is not valid');
        $this->is($source->isValidSource($source->getSource('messages.fr.xml')), true, '->isValidSource() returns true if the source is valid');

        // ->getLastModified()
        $this->diag('->getLastModified()');
        $this->is($source->getLastModified($source->getSource('fr_FR/messages.xml')), 0, '->getLastModified() returns 0 if the source does not exist');
        $this->is($source->getLastModified($source->getSource('messages.fr.xml')), filemtime($source->getSource('messages.fr.xml')), '->getLastModified() returns the last modified time of the source');
    }
}
