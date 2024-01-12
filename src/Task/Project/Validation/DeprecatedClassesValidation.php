<?php

namespace Symfony1\Components\Task\Project\Validation;

use Symfony1\Components\Util\Finder;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Util\Toolkit;
use function file_get_contents;
use function preg_match;
use function preg_quote;
use function implode;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Finds deprecated classes usage.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class DeprecatedClassesValidation extends Validation
{
    public function getHeader()
    {
        return 'Checking usage of deprecated classes';
    }
    public function getExplanation()
    {
        return array('', '  The files above use deprecated classes', '  that have been removed in symfony 1.4.', '', '  You can find a list of all deprecated classes under the', '  "Classes" section of the DEPRECATED tutorial:', '', '  http://www.symfony-project.org/tutorial/1_4/en/deprecated', '');
    }
    public function validate()
    {
        $classes = array(
            'sfDoctrineLogger',
            'sfNoRouting',
            'sfPathInfoRouting',
            'sfRichTextEditor',
            'sfRichTextEditorFCK',
            'sfRichTextEditorTinyMCE',
            'sfCrudGenerator',
            'sfAdminGenerator',
            'sfPropelCrudGenerator',
            'sfPropelAdminGenerator',
            'sfPropelUniqueValidator',
            'sfDoctrineUniqueValidator',
            'sfLoader',
            'sfConsoleRequest',
            'sfConsoleResponse',
            'sfConsoleController',
            'sfDoctrineDataRetriever',
            'sfPropelDataRetriever',
            'sfWidgetFormI18nSelectLanguage',
            'sfWidgetFormI18nSelectCurrency',
            'sfWidgetFormI18nSelectCountry',
            'sfWidgetFormChoiceMany',
            'sfWidgetFormPropelChoiceMany',
            'sfWidgetFormDoctrineChoiceMany',
            'sfValidatorChoiceMany',
            'sfValidatorPropelChoiceMany',
            'sfValidatorPropelDoctrineMany',
            'SfExtensionObjectBuilder',
            'SfExtensionPeerBuilder',
            'SfMultiExtendObjectBuilder',
            'SfNestedSetBuilder',
            'SfNestedSetPeerBuilder',
            'SfObjectBuilder',
            'SfPeerBuilder',
            'sfWidgetFormPropelSelect',
            'sfWidgetFormPropelSelectMany',
            'sfWidgetFormDoctrineSelect',
            'sfWidgetFormDoctrineSelectMany',
            // classes from sfCompat10Plugin
            'sfEzComponentsBridge',
            'sfZendFrameworkBridge',
            'sfProcessCache',
            'sfValidatorConfigHandler',
            'sfActionException',
            'sfValidatorException',
            'sfFillInFormFilter',
            'sfValidationExecutionFilter',
            'sfRequestCompat10',
            'sfFillInForm',
            'sfCallbackValidator',
            'sfCompareValidator',
            'sfDateValidator',
            'sfEmailValidator',
            'sfFileValidator',
            'sfNumberValidator',
            'sfRegexValidator',
            'sfStringValidator',
            'sfUrlValidator',
            'sfValidator',
            'sfValidatorManager',
            'sfMailView',
            'sfMail',
        );
        $found = array();
        $files = Finder::type('file')->name('*.php')->prune('vendor')->in(array(Config::get('sf_apps_dir'), Config::get('sf_lib_dir'), Config::get('sf_test_dir'), Config::get('sf_plugins_dir')));
        foreach ($files as $file) {
            $content = Toolkit::stripComments(file_get_contents($file));
            $matches = array();
            foreach ($classes as $class) {
                if (preg_match('#\\b' . preg_quote($class, '#') . '\\b#', $content)) {
                    $matches[] = $class;
                }
            }
            if ($matches) {
                $found[$file] = implode(', ', $matches);
            }
        }
        return $found;
    }
}
class_alias(DeprecatedClassesValidation::class, 'sfDeprecatedClassesValidation', false);