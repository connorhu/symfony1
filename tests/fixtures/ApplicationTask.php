<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ApplicationTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, '', true);
    }

    protected function execute($arguments = array(), $options = array())
    {
        if (!$this->configuration instanceof sfApplicationConfiguration) {
            throw new Exception('This task requires an application configuration be loaded.');
        }
    }

    public function getServiceContainer()
    {
        return parent::getServiceContainer();
    }

    public function getRouting()
    {
        return parent::getRouting();
    }

    public function getMailer()
    {
        return parent::getMailer();
    }
}
