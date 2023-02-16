<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myPluginManager extends sfPluginManager
{
    protected $mainPackageVersion = '1.0.0';

    public function setMainPackageVersion($version)
    {
        $this->mainPackageVersion = $version;
        $this->configure();
    }

    public function configure()
    {
        $this->environment->registerChannel('pear.example.com', true);

        $mainPackage = new PEAR_PackageFile_v2_rw();
        $mainPackage->setPackage('sfMainPackage');
        $mainPackage->setChannel('pear.example.com');
        $config = $this->environment->getConfig();
        $mainPackage->setConfig($config);
        $mainPackage->setPackageType('php');
        $mainPackage->setAPIVersion('1.0.0');
        $mainPackage->setAPIStability('stable');
        $mainPackage->setReleaseVersion($this->mainPackageVersion);
        $mainPackage->setReleaseStability('stable');
        $mainPackage->setDate(date('Y-m-d'));
        $mainPackage->setDescription('sfMainPackage');
        $mainPackage->setSummary('sfMainPackage');
        $mainPackage->setLicense('MIT License');
        $mainPackage->clearContents();
        $mainPackage->resetFilelist();
        $mainPackage->addMaintainer('lead', 'fabpot', 'Fabien Potencier', 'fabien.potencier@symfony-project.com');
        $mainPackage->setNotes('-');
        $mainPackage->setPearinstallerDep('1.4.3');
        $mainPackage->setPhpDep('5.1.0');

        $this->environment->getRegistry()->deletePackage('sfMainPackage', 'pear.example.com');
        if (!$this->environment->getRegistry()->addPackage2($mainPackage)) {
            throw new sfException('Unable to register our sfMainPackage');
        }
    }

    protected function isPluginCompatibleWithDependency($dependency)
    {
        if (isset($dependency['channel']) && 'sfMainPackage' == $dependency['name'] && 'pear.example.com' == $dependency['channel']) {
            return $this->checkDependency($dependency);
        }

        return true;
    }
}
