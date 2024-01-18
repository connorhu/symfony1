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
class sfMessageSource_AggregateTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public static function get_source($temp1, $temp2)
    {
        $source1 = sfMessageSource::factory('XLIFF', $temp1);
        $source2 = sfMessageSource::factory('XLIFF', $temp2);

        return sfMessageSource::factory('Aggregate', array($source1, $source2));
    }

    public function testTodoMigrate()
    {
        // setup
        $temp1 = @tempnam(sys_get_temp_dir().'/i18ndir', 'tmp');
        unlink($temp1);
        mkdir($temp1);

        $temp2 = @tempnam(sys_get_temp_dir().'/i18ndir', 'tmp');
        unlink($temp2);
        mkdir($temp2);

        // copy fixtures to tmp directory
        copy(__DIR__.'/../fixtures/messages/messages.fr.xml', $temp1.'/messages.fr.xml');
        copy(__DIR__.'/../fixtures/messages/messages_bis.fr.xml', $temp2.'/messages.fr.xml');

        $source = self::get_source($temp1, $temp2);
        $source->setCulture('fr_FR');

        // ->save()
        $this->diag('->save()');
        $this->is($source->save(), false, '->save() returns false if no message is saved');
        $source->append('New message');
        $this->is($source->save(), true, '->save() returns true if some messages are saved');
        $source = self::get_source($temp1, $temp2);
        $source->setCulture('fr_FR');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message'), 'New message', '->save() saves new messages');

        // test new culture
        $source->setCulture('it');
        $source->append('New message (it)');
        $source->save();

        $source = self::get_source($temp1, $temp2);
        $source->setCulture('it');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message (it)'), 'New message (it)', '->save() saves new messages');

        $source->setCulture('fr_FR');

        // ->update()
        $this->diag('->update()');
        $this->is($source->update('New message', 'Nouveau message', 'Comments'), true, '->update() returns true if the message has been updated');
        $source = self::get_source($temp1, $temp2);
        $source->setCulture('fr_FR');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message'), 'Nouveau message', '->update() updates a message translation');

        // ->delete()
        $this->diag('->delete()');
        $this->is($source->delete('Non existant message'), false, '->delete() returns false if the message has not been deleted');
        $this->is($source->delete('New message'), true, '->delete() returns true if the message has been deleted');
        $source = self::get_source($temp1, $temp2);
        $source->setCulture('fr_FR');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message'), 'New message', '->delete() deletes a message');

        // teardown
        sfToolkit::clearDirectory($temp1);
        sfToolkit::clearDirectory($temp2);
        rmdir($temp1);
        rmdir($temp2);
    }
}
