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
require_once __DIR__.'/../sfParameterHolderProxyTestCase.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfUserParameterHolderProxyTest extends sfParameterHolderProxyTestCase
{
    protected $dispatcher;
    protected $sessionPath;
    protected $storage;

    public function setUp(): void
    {
        $this->dispatcher = new sfEventDispatcher();
        $this->sessionPath = sys_get_temp_dir().'/sessions_'.rand(11111, 99999);
        $this->storage = new sfSessionTestStorage(array('session_path' => $this->sessionPath));

        $user = new sfUser($this->dispatcher, $this->storage);

        $this->methodName = 'attribute';
        $this->object = $user;
    }

    protected function tearDown(): void
    {
        $this->storage->clear();

        sfToolkit::clearDirectory($this->sessionPath);
    }
}
