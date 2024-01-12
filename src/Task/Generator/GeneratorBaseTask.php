<?php

namespace Symfony1\Components\Task\Generator;

use Symfony1\Components\Task\BaseTask;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Base class for all symfony generator tasks.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class GeneratorBaseTask extends BaseTask
{
}
class_alias(GeneratorBaseTask::class, 'sfGeneratorBaseTask', false);