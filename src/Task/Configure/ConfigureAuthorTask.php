<?php

namespace Symfony1\Components\Task\Configure;

use Symfony1\Components\Task\BaseTask;
use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Config\Config;
use function parse_ini_file;
use function sprintf;
use function file_put_contents;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Configures the main author of the project.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ConfigureAuthorTask extends BaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('author', CommandArgument::REQUIRED, 'The project author')));
        $this->namespace = 'configure';
        $this->name = 'author';
        $this->briefDescription = 'Configure project author';
        $this->detailedDescription = <<<'EOF'
The [configure:author|INFO] task configures the author for a project:

  [./symfony configure:author "Fabien Potencier <fabien.potencier@symfony-project.com>"|INFO]

The author is used by the generates to pre-configure the PHPDoc header for each generated file.

The value is stored in [config/properties.ini].
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $file = Config::get('sf_config_dir') . '/properties.ini';
        $content = parse_ini_file($file, true);
        if (!isset($content['symfony'])) {
            $content['symfony'] = array();
        }
        $content['symfony']['author'] = $arguments['author'];
        $ini = '';
        foreach ($content as $section => $values) {
            $ini .= sprintf("[%s]\n", $section);
            foreach ($values as $key => $value) {
                $ini .= sprintf("  %s=%s\n", $key, $value);
            }
        }
        file_put_contents($file, $ini);
    }
}
class_alias(ConfigureAuthorTask::class, 'sfConfigureAuthorTask', false);