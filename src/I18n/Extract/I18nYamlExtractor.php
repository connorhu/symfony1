<?php

namespace Symfony1\Components\I18n\Extract;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class I18nYamlExtractor implements I18nExtractorInterface
{
}
class_alias(I18nYamlExtractor::class, 'sfI18nYamlExtractor', false);