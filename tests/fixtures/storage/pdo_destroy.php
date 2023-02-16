<?php

require_once __DIR__.'/common.inc';

$database = new sfPDODatabase(array('dsn' => 'sqlite::memory:'));
$connection = $database->getConnection();
$connection->exec('CREATE TABLE session (sess_id, sess_data, sess_time)');
ini_set('session.use_cookies', 0);
$session_id = '1';
$oldSessionData = 'foo:bar';

$storage = new sfPDOSessionStorage(array('db_table' => 'session', 'session_id' => $session_id, 'database' => $database));
$storage->sessionWrite($session_id, $oldSessionData);
$storage->regenerate(false);

$newSessionData = 'foo:bar:baz';
$storage->sessionWrite(session_id(), $newSessionData);

$storage->sessionGC(0);
$storage->sessionDestroy(session_id());

$result = $connection->query(sprintf('SELECT sess_id, sess_data FROM session WHERE sess_id = "%s"', session_id()));
$data = $result->fetchAll();
echo count($data);

