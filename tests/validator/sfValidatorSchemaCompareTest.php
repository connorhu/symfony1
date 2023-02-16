<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorSchemaCompareTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    /**
     * @dataProvider cleanDataProvider
     */
    public function testClean($values, $operator)
    {
        $validator = new sfValidatorSchemaCompare('left', $operator, 'right');

        $this->assertSame($values, $validator->clean($values));
    }

    public function cleanDataProvider(): Generator
    {
        yield array(array('left' => 'foo', 'right' => 'foo'), sfValidatorSchemaCompare::EQUAL);
        yield array(array(), sfValidatorSchemaCompare::EQUAL);
        yield array(null, sfValidatorSchemaCompare::EQUAL);
        yield array(array('left' => 1, 'right' => 2), sfValidatorSchemaCompare::LESS_THAN);
        yield array(array('left' => 2, 'right' => 2), sfValidatorSchemaCompare::LESS_THAN_EQUAL);
        yield array(array('left' => 2, 'right' => 1), sfValidatorSchemaCompare::GREATER_THAN);
        yield array(array('left' => 2, 'right' => 2), sfValidatorSchemaCompare::GREATER_THAN_EQUAL);
        yield array(array('left' => 'foo', 'right' => 'bar'), sfValidatorSchemaCompare::NOT_EQUAL);
        yield array(array('left' => '0000', 'right' => '0'), sfValidatorSchemaCompare::NOT_IDENTICAL);
        yield array(array('left' => '0000', 'right' => '0'), sfValidatorSchemaCompare::EQUAL);
        yield array(array('left' => '0000', 'right' => '0000'), sfValidatorSchemaCompare::IDENTICAL);

        yield array(array('left' => 'foo', 'right' => 'foo'), '==');
        yield array(array(), '==');
        yield array(null, '==');
        yield array(array('left' => 1, 'right' => 2), '<');
        yield array(array('left' => 2, 'right' => 2), '<=');
        yield array(array('left' => 2, 'right' => 1), '>');
        yield array(array('left' => 2, 'right' => 2), '>=');
        yield array(array('left' => 'foo', 'right' => 'bar'), '!=');
        yield array(array('left' => '0000', 'right' => '0'), '!==');
        yield array(array('left' => '0000', 'right' => '0'), '==');
        yield array(array('left' => '0000', 'right' => '0000'), '===');
    }

    /**
     * @dataProvider cleanThrowsErrorDataProvider
     */
    public function testCleanThrowsError($values, $operator)
    {
        $validator = new sfValidatorSchemaCompare('left', $operator, 'right');
        $validator->setOption('throw_global_error', false);

        $this->expectException(sfValidatorError::class);
        $this->expectExceptionCode('left [invalid]');

        $validator->clean($values);
    }

    /**
     * @dataProvider cleanThrowsErrorDataProvider
     */
    public function testCleanThrowsGlobalError($values, $operator)
    {
        $validator = new sfValidatorSchemaCompare('left', $operator, 'right');
        $validator->setOption('throw_global_error', true);

        $this->expectException(sfValidatorError::class);
        $this->expectExceptionCode('invalid');

        $validator->clean($values);
    }

    public function cleanThrowsErrorDataProvider(): Generator
    {
        yield array(array('left' => 'foo', 'right' => 'foo'), sfValidatorSchemaCompare::NOT_EQUAL);
        yield array(array(), sfValidatorSchemaCompare::NOT_EQUAL);
        yield array(null, sfValidatorSchemaCompare::NOT_EQUAL);
        yield array(array('left' => 1, 'right' => 2), sfValidatorSchemaCompare::GREATER_THAN);
        yield array(array('left' => 2, 'right' => 3), sfValidatorSchemaCompare::GREATER_THAN_EQUAL);
        yield array(array('left' => 2, 'right' => 1), sfValidatorSchemaCompare::LESS_THAN);
        yield array(array('left' => 3, 'right' => 2), sfValidatorSchemaCompare::LESS_THAN_EQUAL);
        yield array(array('left' => 'foo', 'right' => 'bar'), sfValidatorSchemaCompare::EQUAL);
        yield array(array('left' => '0000', 'right' => '0'), sfValidatorSchemaCompare::IDENTICAL);
        yield array(array('left' => '0000', 'right' => '0'), sfValidatorSchemaCompare::NOT_EQUAL);
        yield array(array('left' => '0000', 'right' => '0000'), sfValidatorSchemaCompare::NOT_IDENTICAL);

        yield array(array('left' => 'foo', 'right' => 'foo'), '!=');
        yield array(array(), '!=');
        yield array(null, '!=');
        yield array(array('left' => 1, 'right' => 2), '>');
        yield array(array('left' => 2, 'right' => 3), '>=');
        yield array(array('left' => 2, 'right' => 1), '<');
        yield array(array('left' => 3, 'right' => 2), '<=');
        yield array(array('left' => 'foo', 'right' => 'bar'), '==');
        yield array(array('left' => '0000', 'right' => '0'), '===');
        yield array(array('left' => '0000', 'right' => '0'), '!=');
        yield array(array('left' => '0000', 'right' => '0000'), '!==');
    }

    public function testCleanThrowsErrorArgumentIsNotAnArray()
    {
        $validator = new sfValidatorSchemaCompare('left', sfValidatorSchemaCompare::EQUAL, 'right');

        $this->expectException(InvalidArgumentException::class);

        $validator->clean('foo');
    }

    public function testInvalidOperator()
    {
        $this->expectException(InvalidArgumentException::class);
        $validator = new sfValidatorSchemaCompare('left', 'foo', 'right');
        $validator->clean(array());
    }

    public function testAsString()
    {
        $validator = new sfValidatorSchemaCompare('left', sfValidatorSchemaCompare::EQUAL, 'right');
        $this->assertSame('left == right', $validator->asString(), '->asString() returns a string representation of the validator');

        $validator = new sfValidatorSchemaCompare('left', sfValidatorSchemaCompare::EQUAL, 'right', array(), array('required' => 'This is required.'));
        $this->assertSame('left ==({}, { required: \'This is required.\' }) right', $validator->asString(), '->asString() returns a string representation of the validator with required');
    }
}
