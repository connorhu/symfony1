<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../PhpUnitSfTestHelperTrait.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfI18nYamlValidateExtractorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        $e = new sfI18nYamlValidateExtractor();
        $this->ok($e instanceof sfI18nExtractorInterface, 'sfI18nYamlValidateExtractor implements the sfI18nExtractorInterface interface');

        // ->extract();
        $this->diag('->extract()');

        $content = <<<'EOF'
        fields:
          name:
            required:
              msg: Name is required
            sfStringValidator:
              min_error: The name is too short
        
        validators:
          myStringValidator:
            class: sfStringValidator
            param:
              min_error: The name is really too short
              max_error: The name is really too long
        EOF;

        $this->is($e->extract($content), array(
            'Name is required',
            'The name is too short',
            'The name is really too short',
            'The name is really too long',
        ), '->extract() extracts strings from generator.yml files');
    }
}
