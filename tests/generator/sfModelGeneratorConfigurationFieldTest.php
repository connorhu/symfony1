<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class sfModelGeneratorConfigurationFieldTest extends TestCase
{
    public function testPartialComponentLink()
    {
        $field = new sfModelGeneratorConfigurationField('my_field', array());
        $this->assertSame(false, $field->isPartial(), '->isPartial() defaults to false');
        $this->assertSame(false, $field->isComponent(), '->isComponent() defaults to false');
        $this->assertSame(false, $field->isLink(), '->isLink() defaults to false');

        $field = new sfModelGeneratorConfigurationField('my_field', array('flag' => '_'));
        $this->assertSame(true, $field->isPartial(), '->isPartial() returns true if flag is "_"');
        $this->assertSame(false, $field->isComponent(), '->isComponent() defaults to false');
        $this->assertSame(false, $field->isLink(), '->isLink() defaults to false');

        $field = new sfModelGeneratorConfigurationField('my_field', array('flag' => '~'));
        $this->assertSame(false, $field->isPartial(), '->isPartial() defaults to false');
        $this->assertSame(true, $field->isComponent(), '->isComponent() returns true if flag is "~"');
        $this->assertSame(false, $field->isLink(), '->isLink() defaults to false');

        $field = new sfModelGeneratorConfigurationField('my_field', array('flag' => '='));
        $this->assertSame(false, $field->isPartial(), '->isPartial() defaults to false');
        $this->assertSame(false, $field->isComponent(), '->isComponent() defaults to false');
        $this->assertSame(true, $field->isLink(), '->isLink() returns true if flag is "="');
    }
}
