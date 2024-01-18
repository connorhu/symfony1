<?php

/*
 * This file is part of the Symfony1 package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../bootstrap/unit.php';

$t = new \lime_test(2);

$handler = new \sfCompileConfigHandler();
$handler->initialize();

$dir = __DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'sfCompileConfigHandler'.DIRECTORY_SEPARATOR;

$t->diag('execute');

\sfConfig::set('sf_debug', true);
$data = $handler->execute([$dir.'simple.yml']);
$t->ok(str_contains($data, "class sfInflector\n{\n    /**"), '->execute() return complete classe codes');

\sfConfig::set('sf_debug', false);
$data = $handler->execute([$dir.'simple.yml']);
$t->ok(str_contains($data, "class sfInflector\n{\n    public"), '->execute() return minified classe codes');
