<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../../fixtures/myI18nExtractTest.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfI18nExtractTest extends Symfony1ApplicationTestCase
{
    use PhpUnitSfTestHelperTrait;

    public function getI18NGlobalDirs()
    {
        return array(__DIR__.'/../../fixtures/messages');
    }

    public function getRootDir()
    {
        return sfConfig::get('sf_test_cache_dir', sys_get_temp_dir());
    }

    public function testTodoMigrate()
    {
        $this->resetSfConfig();

        $cache = new sfNoCache();
        $i18n = new sfI18N($this->getApplicationConfiguration(), $cache);

        // ->initialize()
        $this->diag('->initialize()');
        $extract = new myI18nExtractTest($i18n, 'fr');
        $this->is(count($extract->getCurrentMessages()), 4, '->initialize() initializes the current i18n messages');
        $extract->extract();

        // ->getOldMessages()
        $this->diag('->getOldMessages()');
        $this->is($extract->getOldMessages(), array_diff($extract->getCurrentMessages(), $extract->getMessages()), '->getOldMessages() returns old messages');

        // ->getNewMessages()
        $this->diag('->getNewMessages()');
        $this->is($extract->getNewMessages(), array_diff($extract->getMessages(), $extract->getCurrentMessages()), '->getNewMessages() returns new messages');
    }
}
