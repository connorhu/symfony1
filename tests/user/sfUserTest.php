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
require_once __DIR__.'/../fixtures/mySfUser.php';

function user_flush($dispatcher, $user, $storage, $options = array())
{
    $user->shutdown();
    $user->initialize($dispatcher, $storage, $options);
    $parameters = $storage->getOptions();
    $storage->shutdown();
    $storage->initialize($parameters);
}

/**
 * @internal
 *
 * @coversNothing
 */
class sfUserTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $_SERVER['session_id'] = 'test';

        $dispatcher = new sfEventDispatcher();
        $sessionPath = sys_get_temp_dir().'/sessions_'.rand(11111, 99999);
        $storage = new sfSessionTestStorage(array('session_path' => $sessionPath));

        $user = new sfUser($dispatcher, $storage);

        // ->initialize()
        $this->diag('->initialize()');
        $this->is($user->getCulture(), 'en', '->initialize() sets the culture to "en" by default');

        $user->setCulture(null);
        $user->initialize($dispatcher, $storage, array('default_culture' => 'de'));

        user_flush($dispatcher, $user, $storage);

        $this->is($user->getCulture(), 'de', '->initialize() sets the culture to the value of default_culture if available');

        user_flush($dispatcher, $user, $storage);
        $this->is($user->getCulture(), 'de', '->initialize() reads the culture from the session data if available');

        $userBis = new sfUser($dispatcher, $storage);
        $this->is($userBis->getCulture(), 'de', '->initialize() serializes the culture to the session data');

        // ->setCulture() ->getCulture()
        $this->diag('->setCulture() ->getCulture()');
        $user->setCulture('fr');
        $this->is($user->getCulture(), 'fr', '->setCulture() changes the current user culture');

        // ->setFlash() ->getFlash() ->hasFlash()
        $this->diag('->setFlash() ->getFlash() ->hasFlash()');
        $user->initialize($dispatcher, $storage, array('use_flash' => true));
        $user->setFlash('foo', 'bar');
        $this->is($user->getFlash('foo'), 'bar', '->setFlash() sets a flash variable');
        $this->is($user->hasFlash('foo'), true, '->hasFlash() returns true if the flash variable exists');
        user_flush($dispatcher, $user, $storage, array('use_flash' => true));

        $userBis = new sfUser($dispatcher, $storage, array('use_flash' => true));
        $this->is($userBis->getFlash('foo'), 'bar', '->getFlash() returns a flash previously set');
        $this->is($userBis->hasFlash('foo'), true, '->hasFlash() returns true if the flash variable exists');
        user_flush($dispatcher, $user, $storage, array('use_flash' => true));

        $userBis = new sfUser($dispatcher, $storage, array('use_flash' => true));
        $this->is($userBis->getFlash('foo'), null, 'Flashes are automatically removed after the next request');
        $this->is($userBis->hasFlash('foo'), false, '->hasFlash() returns true if the flash variable exists');

        // array access for user attributes
        $user->setAttribute('foo', 'foo');

        $this->diag('Array access for user attributes');
        $this->is(isset($user['foo']), true, '->offsetExists() returns true if user attribute exists');
        $this->is(isset($user['foo2']), false, '->offsetExists() returns false if user attribute does not exist');
        $this->is($user['foo3'], false, '->offsetGet() returns false if attribute does not exist');
        $this->is($user['foo'], 'foo', '->offsetGet() returns attribute by name');

        $user['foo2'] = 'foo2';
        $this->is($user['foo2'], 'foo2', '->offsetSet() sets attribute by name');

        unset($user['foo2']);
        $this->is(isset($user['foo2']), false, '->offsetUnset() unsets attribute by name');

        $storage->clear();

        sfToolkit::clearDirectory($sessionPath);
    }
}
