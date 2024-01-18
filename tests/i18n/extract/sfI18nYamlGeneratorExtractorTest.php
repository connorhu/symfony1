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
class sfI18nYamlGeneratorExtractorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        $e = new sfI18nYamlGeneratorExtractor();
        $this->ok($e instanceof sfI18nExtractorInterface, 'sfI18nYamlGeneratorExtractor implements the sfI18nExtractorInterface interface');

        // ->extract();
        $this->diag('->extract()');

        $content = <<<'EOF'
        generator:
          param:
            config:
              fields:
                name: { name: "Global Field Name", help: "Global Help for Name" }
              list:
                title: List title
                fields:
                  name: { name: "List Field Name", help: "List Help for Name" }
              edit:
                title: Edit title
                display:
                  NONE: []
                  First category: []
                  Last category: []
                fields:
                  name: { name: "Edit Field Name", help: "Edit Help for Name" }
        EOF;

        $this->is($e->extract($content), array(
            'List title',
            'Edit title',
            'Global Field Name',
            'Global Help for Name',
            'List Field Name',
            'List Help for Name',
            'Edit Field Name',
            'Edit Help for Name',
            'First category',
            'Last category',
        ), '->extract() extracts strings from generator.yml files');

        $content = <<<'EOF'
        generator:
          param:
            edit:
              display: [first_name, last_name]
        EOF;

        $this->is($e->extract($content), array(), '->extract() extracts strings from generator.yml files');
    }
}
