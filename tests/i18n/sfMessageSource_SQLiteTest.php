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
class sfMessageSource_SQLiteTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    protected $tempSqlFilename;

    protected $source;

    public function setUp(): void
    {
        $this->tempSqlFilename = __DIR__.'/'.rand(11111, 99999);

        $queries = array(
            'CREATE TABLE catalogue (cat_id INTEGER PRIMARY KEY, name VARCHAR NOT NULL, source_lang VARCHAR, target_lang VARCHAR, date_created INT, date_modified INT, author VARCHAR);',
            'CREATE TABLE trans_unit (msg_id INTEGER PRIMARY KEY, cat_id INTEGER NOT NULL DEFAULT \'1\', id VARCHAR, source TEXT, target TEXT, comments TEXT, date_added INT, date_modified INT, author VARCHAR, translated INT(1) NOT NULL DEFAULT \'0\');',
            "INSERT INTO catalogue (cat_id, name) VALUES (1, 'messages.fr_FR')",
            "INSERT INTO catalogue (cat_id, name) VALUES (2, 'messages.it')",
            "INSERT INTO trans_unit (msg_id, cat_id, id, source, target, translated) VALUES (1, 1, 1, 'an english sentence', 'une phrase en français', 1)",
            "INSERT INTO trans_unit (msg_id, cat_id, id, source, target, translated) VALUES (2, 1, 2, 'another english sentence', 'une autre phrase en français', 1)",
        );

        if (version_compare(PHP_VERSION, '5.3', '>')) {
            $db = new SQLite3($this->tempSqlFilename);

            foreach ($queries as $query) {
                $db->exec($query);
            }

            $db->close();
        } else {
            $db = sqlite_open($this->tempSqlFilename);

            foreach ($queries as $query) {
                sqlite_query($query, $db);
            }

            sqlite_close($db);
        }

        $this->source = sfMessageSource::factory('SQLite', 'sqlite://localhost/'.$this->tempSqlFilename);
    }

    protected function tearDown(): void
    {
        if (isset($this->tempSqlFilename) && file_exists($this->tempSqlFilename)) {
            unlink($this->tempSqlFilename);
        }
    }

    public function testTodoMigrate(): void
    {
        $this->source->setCulture('fr_FR');

        // ->loadData()
        $this->diag('->loadData()');
        $messages = $this->source->loadData($this->source->getSource('messages.fr_FR'));
        $this->is($messages['an english sentence'][0], 'une phrase en français', '->loadData() loads messages from a SQLite file');

        // ->save()
        $this->diag('->save()');
        $this->is($this->source->save(), false, '->save() returns false if no message is saved');
        $this->source->append('New message');
        $this->is($this->source->save(), true, '->save() returns true if some messages are saved');
        $this->source = sfMessageSource::factory('SQLite', 'sqlite://localhost/'.$this->tempSqlFilename);
        $this->source->setCulture('fr_FR');
        $format = new sfMessageFormat($this->source);
        $this->is($format->format('New message'), 'New message', '->save() saves new messages');

        // test new culture
        $this->source->setCulture('it');
        $this->source->append('New message (it)');
        $this->source->save();

        $this->source = sfMessageSource::factory('SQLite', 'sqlite://localhost/'.$this->tempSqlFilename);
        $this->source->setCulture('it');
        $format = new sfMessageFormat($this->source);
        $this->is($format->format('New message (it)'), 'New message (it)', '->save() saves new messages');

        $this->source->setCulture('fr_FR');

        // ->update()
        $this->diag('->update()');
        $this->is($this->source->update('New message', 'Nouveau message', 'Comments'), true, '->update() returns true if the message has been updated');
        $this->source = sfMessageSource::factory('SQLite', 'sqlite://localhost/'.$this->tempSqlFilename);
        $this->source->setCulture('fr_FR');
        $format = new sfMessageFormat($this->source);
        $this->is($format->format('New message'), 'Nouveau message', '->update() updates a message translation');

        // ->delete()
        $this->diag('->delete()');
        $this->is($this->source->delete('Non existant message'), false, '->delete() returns false if the message has not been deleted');
        $this->is($this->source->delete('New message'), true, '->delete() returns true if the message has been deleted');
        $this->source = sfMessageSource::factory('SQLite', 'sqlite://localhost/'.$this->tempSqlFilename);
        $this->source->setCulture('fr_FR');
        $format = new sfMessageFormat($this->source);
        $this->is($format->format('New message'), 'New message', '->delete() deletes a message');
    }
}
