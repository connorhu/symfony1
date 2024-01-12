<?php

use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->in('lib');

return (new \LesPhp\PSR4Converter\Config())
    ->setClassNameFilter(function (string $className) {
        if (str_starts_with($className, 'sf')) {
            return ucfirst(substr($className, 2));
        }

        return $className;
    })
    ->setNamespaceFilter(function (string $namespace) {
        return str_replace(' ', '\\', ucwords(str_replace('\\', ' ', $namespace)));
    })
    ->setFinder($finder)
    ->addIgnorePath('vendor')
    ->addIgnorePath('plugins/sfDoctrinePlugin')
    ->addIgnorePath('controller/default/templates')
    ->addIgnorePath('task/generator/skeleton')
    ->addIgnorePath('i18n/Gettext')
    ->addIgnorePath('exception/data')
    ->addIgnorePath('command/cli.php')
    ->addIgnorePath('helper/EscapingHelper.php')
    ->addIgnorePath('helper/TextHelper.php')
    ->addIgnorePath('autoload/sfCoreAutoload.class.php')
    ;