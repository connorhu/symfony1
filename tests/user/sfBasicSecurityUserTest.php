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
require_once __DIR__.'/../fixtures/MySessionStorage.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfBasicSecurityUserTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();
        $sessionPath = sys_get_temp_dir().'/sessions_'.rand(11111, 99999);
        $storage = new MySessionStorage(array('session_path' => $sessionPath));

        $user = new sfBasicSecurityUser($dispatcher, $storage);

        // ->initialize()
        $this->diag('->initialize()');
        $this->todo('->initialize() times out the user if no request made for a long time');

        // ->getCredentials()
        $this->diag('->getCredentials()');
        $user->clearCredentials();
        $user->addCredential('user');
        $this->is($user->getCredentials(), array('user'), '->getCredentials() returns user credentials as an array');

        // ->setAuthenticated() ->isAuthenticated()
        $this->diag('->setAuthenticated() ->isAuthenticated()');
        $this->is($user->isAuthenticated(), false, '->isAuthenticated() returns false by default');
        $user->setAuthenticated(true);
        $this->is($user->isAuthenticated(), true, '->isAuthenticated() returns true if the user is authenticated');
        $user->setAuthenticated(false);
        $this->is($user->isAuthenticated(), false, '->setAuthenticated() accepts a boolean as its first parameter');

        // session id regeneration
        $user->setAuthenticated(false);
        $id = $storage->getSessionId();
        $user->setAuthenticated(true);
        $this->isnt($id, $id = $storage->getSessionId(), '->setAuthenticated() regenerates the session id if the authentication changes');
        $user->setAuthenticated(true);
        $this->is($storage->getSessionId(), $id, '->setAuthenticated() does not regenerate the session id if the authentication does not change');
        $user->addCredential('foo');
        $this->isnt($id, $id = $storage->getSessionId(), '->addCredential() regenerates the session id if a new credential is added');
        $this->is($id, $storage->getSessionId(), '->addCredential() does not regenerate the session id if the credential already exists');
        $user->removeCredential('foo');
        $this->isnt($id, $id = $storage->getSessionId(), '->removeCredential() regenerates the session id if a credential is removed');
        $this->is($id, $storage->getSessionId(), '->removeCredential() does not regenerate the session id if the credential does not exist');

        // ->setTimedOut() ->getTimedOut()
        $user = new sfBasicSecurityUser($dispatcher, $storage);
        $this->diag('->setTimedOut() ->isTimedOut()');
        $this->is($user->isTimedOut(), false, '->isTimedOut() returns false if the session is not timed out');
        $user->setTimedOut();
        $this->is($user->isTimedOut(), true, '->isTimedOut() returns true if the session is timed out');

        // ->hasCredential()
        $this->diag('->hasCredential()');
        $user->clearCredentials();
        $this->is($user->hasCredential('admin'), false, '->hasCredential() returns false if user has not the credential');

        $user->addCredential('admin');
        $this->is($user->hasCredential('admin'), true, '->addCredential() takes a credential as its first argument');

        // admin AND user
        $this->is($user->hasCredential(array('admin', 'user')), false, '->hasCredential() can takes an array of credential as a parameter');

        // admin OR user
        $this->is($user->hasCredential(array(array('admin', 'user'))), true, '->hasCredential() can takes an array of credential as a parameter');

        // (admin OR user) AND owner
        $this->is($user->hasCredential(array(array('admin', 'user'), 'owner')), false, '->hasCredential() can takes an array of credential as a parameter');
        $user->addCredential('owner');
        $this->is($user->hasCredential(array(array('admin', 'user'), 'owner')), true, '->hasCredential() can takes an array of credential as a parameter');

        // [[root, admin, editor, [supplier, owner], [supplier, group], accounts]]
        // root OR admin OR editor OR (supplier AND owner) OR (supplier AND group) OR accounts
        $user->clearCredentials();
        $credential = array(array('root', 'admin', 'editor', array('supplier', 'owner'), array('supplier', 'group'), 'accounts'));
        $this->is($user->hasCredential($credential), false, '->hasCredential() can takes an array of credential as a parameter');
        $user->addCredential('admin');
        $this->is($user->hasCredential($credential), true, '->hasCredential() can takes an array of credential as a parameter');
        $user->clearCredentials();
        $user->addCredential('supplier');
        $this->is($user->hasCredential($credential), false, '->hasCredential() can takes an array of credential as a parameter');
        $user->addCredential('owner');
        $this->is($user->hasCredential($credential), true, '->hasCredential() can takes an array of credential as a parameter');

        // [[root, [supplier, [owner, quasiowner]], accounts]]
        // root OR (supplier AND (owner OR quasiowner)) OR accounts
        $user->clearCredentials();
        $credential = array(array('root', array('supplier', array('owner', 'quasiowner')), 'accounts'));
        $this->is($user->hasCredential($credential), false, '->hasCredential() can takes an array of credential as a parameter');
        $user->addCredential('root');
        $this->is($user->hasCredential($credential), true, '->hasCredential() can takes an array of credential as a parameter');
        $user->clearCredentials();
        $user->addCredential('supplier');
        $this->is($user->hasCredential($credential), false, '->hasCredential() can takes an array of credential as a parameter');
        $user->addCredential('owner');
        $this->is($user->hasCredential($credential), true, '->hasCredential() can takes an array of credential as a parameter');
        $user->addCredential('quasiowner');
        $this->is($user->hasCredential($credential), true, '->hasCredential() can takes an array of credential as a parameter');
        $user->removeCredential('owner');
        $this->is($user->hasCredential($credential), true, '->hasCredential() can takes an array of credential as a parameter');
        $user->removeCredential('supplier');
        $this->is($user->hasCredential($credential), false, '->hasCredential() can takes an array of credential as a parameter');

        $user->clearCredentials();
        $user->addCredential('admin');
        $user->addCredential('user');
        $this->is($user->hasCredential('admin'), true);
        $this->is($user->hasCredential('user'), true);

        $user->addCredentials('superadmin', 'subscriber');
        $this->is($user->hasCredential('subscriber'), true);
        $this->is($user->hasCredential('superadmin'), true);

        // admin and (user or subscriber)
        $this->is($user->hasCredential(array(array('admin', array('user', 'subscriber')))), true);

        $user->addCredentials(array('superadmin1', 'subscriber1'));
        $this->is($user->hasCredential('subscriber1'), true);
        $this->is($user->hasCredential('superadmin1'), true);

        // admin and (user or subscriber) and (superadmin1 or subscriber1)
        $this->is($user->hasCredential(array(array('admin', array('user', 'subscriber'), array('superadmin1', 'subscriber1')))), true);

        // numerical credentials
        $user->clearCredentials();
        $user->addCredentials(array('1', 2));
        $this->is($user->hasCredential(1), true, '->hasCrendential() supports numerical credentials');
        $this->is($user->hasCredential('2'), true, '->hasCrendential() supports numerical credentials');
        $this->is($user->hasCredential(array('1', 2)), true, '->hasCrendential() supports numerical credentials');
        $this->is($user->hasCredential(array(1, '2')), true, '->hasCrendential() supports numerical credentials');

        // ->removeCredential()
        $this->diag('->removeCredential()');
        $user->removeCredential('user');
        $this->is($user->hasCredential('user'), false);

        // ->clearCredentials()
        $this->diag('->clearCredentials()');
        $user->clearCredentials();
        $this->is($user->hasCredential('subscriber'), false);
        $this->is($user->hasCredential('superadmin'), false);

        // timeout
        $user->setAuthenticated(true);
        $user->shutdown();
        $user = new sfBasicSecurityUser($dispatcher, $storage, array('timeout' => 0));
        $this->is($user->isTimedOut(), true, '->initialize() times out the user if no request made for a long time');

        $user = new sfBasicSecurityUser($dispatcher, $storage, array('timeout' => false));
        $this->is($user->isTimedOut(), false, '->initialize() takes a timeout parameter which can be false to disable session timeout');

        sfToolkit::clearDirectory($sessionPath);
    }
}
