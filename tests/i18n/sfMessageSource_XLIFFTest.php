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
class sfMessageSource_XLIFFTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // setup
        $temp = @tempnam(sys_get_temp_dir().'/i18ndir', 'tmp');
        unlink($temp);
        mkdir($temp);

        // copy fixtures to tmp directory
        copy(__DIR__.'/../fixtures/messages/messages.fr.xml', $temp.'/messages.fr.xml');

        $source = sfMessageSource::factory('XLIFF', $temp);
        $source->setCulture('fr_FR');

        // ->loadData()
        $this->diag('->loadData()');
        $messages = $source->loadData($source->getSource('messages.fr.xml'));
        $this->is($messages['an english sentence'][0], 'une phrase en français', '->loadData() loads messages from a XLIFF file');

        $this->is($source->loadData($source->getSource('invalid.xml')), false, '->loadData() returns false if it cannot load the messages from the file');

        // ->save()
        $this->diag('->save()');
        $this->is($source->save(), false, '->save() returns false if no message is saved');
        $source->append('New message');
        $this->is($source->save(), true, '->save() returns true if some messages are saved');
        $source = sfMessageSource::factory('XLIFF', $temp);
        $source->setCulture('fr_FR');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message'), 'New message', '->save() saves new messages');

        // test new culture
        $source->setCulture('it');
        $source->append('New message & <more> (it)');
        $source->save();

        $source = sfMessageSource::factory('XLIFF', $temp);
        $source->setCulture('it');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message & <more> (it)'), 'New message & <more> (it)', '->save() saves new messages');

        $source->setCulture('fr_FR');

        // ->update()
        $this->diag('->update()');
        $this->is($source->update('New message', 'Nouveau message', ''), true, '->update() returns true if the message has been updated');
        $source = sfMessageSource::factory('XLIFF', $temp);
        $source->setCulture('fr_FR');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message'), 'Nouveau message', '->update() updates a message translation');

        // ->delete()
        $this->diag('->delete()');
        $this->is($source->delete('Non existant message'), false, '->delete() returns false if the message has not been deleted');
        $this->is($source->delete('New message'), true, '->delete() returns true if the message has been deleted');
        $source = sfMessageSource::factory('XLIFF', $temp);
        $source->setCulture('fr_FR');
        $format = new sfMessageFormat($source);
        $this->is($format->format('New message'), 'New message', '->delete() deletes a message');

        // teardown
        sfToolkit::clearDirectory($temp);
        rmdir($temp);
    }
}
