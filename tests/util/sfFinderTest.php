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
class sfFinderTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    protected $fixtureDir = null;
    protected $permissionDir = null;

    protected $phpFiles = array(
        'dir1/dir2/file21.php',
        'dir1/file12.php',
    );

    protected $regexpFiles = array(
        'dir1/dir2/file21.php',
        'dir1/dir2/file22',
        'dir1/dir2/file23',
        'dir1/dir2/file24',
        'file2.txt',
    );

    protected $regexpWithModifierFiles = array(
        'FILE5.txt',
        'dir1/dir2/file21.php',
        'dir1/dir2/file22',
        'dir1/dir2/file23',
        'dir1/dir2/file24',
        'file2.txt',
    );

    protected $txtFiles = array(
        'FILE5.txt',
        'file2.txt',
    );

    protected $allFiles = array(
        'dir1/dir2/dir3/file31',
        'dir1/dir2/dir4/file41',
        'dir1/dir2/file21.php',
        'dir1/dir2/file22',
        'dir1/dir2/file23',
        'dir1/dir2/file24',
        'dir1/file11',
        'dir1/file12.php',
        'dir1/file13',
        'file1',
        'FILE5.txt',
        'file2.txt',
    );

    protected $minDepth1Files = array(
        'dir1/dir2/dir3/file31',
        'dir1/dir2/dir4/file41',
        'dir1/dir2/file21.php',
        'dir1/dir2/file22',
        'dir1/dir2/file23',
        'dir1/dir2/file24',
        'dir1/file11',
        'dir1/file12.php',
        'dir1/file13',
    );

    protected $maxDepth2Files = array(
        'FILE5.txt',
        'dir1/dir2/file21.php',
        'dir1/dir2/file22',
        'dir1/dir2/file23',
        'dir1/dir2/file24',
        'dir1/file11',
        'dir1/file12.php',
        'dir1/file13',
        'file1',
        'file2.txt',
    );

    protected $anyWithoutDir2 = array(
        'FILE5.txt',
        'dir1',
        'dir1/dir2',
        'dir1/file11',
        'dir1/file12.php',
        'dir1/file13',
        'file1',
        'file2.txt',
    );

    protected function setUp(): void
    {
        $this->fixtureDir = realpath(__DIR__.'/../fixtures/finder/finder');
        $this->permissionDir = realpath(__DIR__.'/../fixtures/finder/finder_permissions');
    }

    public function testTypeReturnValue()
    {
        $this->assertInstanceOf(sfFinder::class, sfFinder::type('file'), '::type() returns a sfFinder instance');
    }

    /**
     * @dataProvider typeValuesDataProvider
     */
    public function testTypeValues($type, $expectedValue)
    {
        $finder = sfFinder::type($type);
        $this->assertSame($expectedValue, $finder->get_type(), '::type() takes a file, dir, or any as its first argument');
    }

    public function typeValuesDataProvider()/*: \Generator*/
    {
        yield ['file', 'file'];
        yield ['dir', 'directory'];
        yield ['any', 'any'];
        yield ['somethingelse', 'file'];
    }

    public function testSetGetType()
    {
        $finder = sfFinder::type('file');
        $finder->setType('dir');
        $this->assertSame('directory', $finder->get_type(), '->getType() returns the type of searched files');
        $this->assertSame($finder, $finder->setType('file'), '->setType() implements a fluent interface');
    }

    public function testNameReturnType()
    {
        $finder = sfFinder::type('file');
        $this->assertSame($finder, $finder->name('*.php'), '->name() implements the fluent interface');
    }

    public function testNameFileNameSupport()
    {
        $finder = sfFinder::type('file')->name('file21.php')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $this->assertSame(array('dir1/dir2/file21.php'), $result, '->name() can take a file name as an argument');
    }

    public function testNameGlobSupport()
    {
        $finder = sfFinder::type('file')->name('*.php')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $this->assertSame($this->phpFiles, $result, '->name() can take a glob pattern as an argument');
    }

    public function testNameRegexSupport()
    {
        $finder = sfFinder::type('file')->name('/^file2.*$/')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $this->assertSame($this->regexpFiles, $result, '->name() can take a regexp as an argument');
    }

    public function testNameRegexSupportWithModifier()
    {
        $finder = sfFinder::type('file')->name('/^file(2|5).*$/i')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $this->assertSame($this->regexpWithModifierFiles, $result, '->name() can take a regexp with a modifier as an argument');
    }

    public function testNameArgumentArray()
    {
        $finder = sfFinder::type('file')->name(array('*.php', '*.txt'))->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_merge($this->phpFiles, $this->txtFiles);
        sort($expected);

        $this->assertSame($expected, $result, '->name() can take an array of patterns');
    }

    public function testNameTwoArguments()
    {
        $finder = sfFinder::type('file')->name('*.php', '*.txt')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_merge($this->phpFiles, $this->txtFiles);
        sort($expected);

        $this->assertSame($expected, $result, '->name() can take patterns as arguments');
    }

    public function testNameChaining()
    {
        $finder = sfFinder::type('file')->name('*.php')->name('*.txt')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_merge($this->phpFiles, $this->txtFiles);
        sort($expected);

        $this->assertSame($expected, $result, '->name() can be called several times');
    }

    public function testNotName()
    {
        $finder = sfFinder::type('file');
        $this->assertSame($finder, $finder->not_name('*.php'), '->not_name() implements the fluent interface');
    }

    public function testNotNameFilenameSupport()
    {
        $finder = sfFinder::type('file')->not_name('file21.php')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, array('dir1/dir2/file21.php')));
        sort($expected);

        $this->assertSame($expected, $result, '->not_name() can take a file name as an argument');
    }

    public function testNotNameGlobSupport()
    {
        $finder = sfFinder::type('file')->not_name('*.php')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, $this->phpFiles));
        sort($expected);

        $this->assertSame($expected, $result, '->not_name() can take a glob pattern as an argument');
    }

    public function testNotNameRegexSupport()
    {
        $finder = sfFinder::type('file')->not_name('/^file2.*$/')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, $this->regexpFiles));
        sort($expected);

        $this->assertSame($expected, $result, '->not_name() can take a regexp as an argument');
    }

    public function testNotNameArgumentArray()
    {
        $finder = sfFinder::type('file')->not_name(array('*.php', '*.txt'))->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, array_merge($this->phpFiles, $this->txtFiles)));
        sort($expected);

        $this->assertSame($expected, $result, '->not_name() can take an array of patterns');
    }

    public function testNotNameTwoArguments()
    {
        $finder = sfFinder::type('file')->not_name('*.php', '*.txt')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, array_merge($this->phpFiles, $this->txtFiles)));
        sort($expected);

        $this->assertSame($expected, $result, '->not_name() can take patterns as arguments');
    }

    public function testNotNameChaining()
    {
        $finder = sfFinder::type('file')->not_name('*.php')->not_name('*.txt')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, array_merge($this->phpFiles, $this->txtFiles)));
        sort($expected);

        $this->assertSame($expected, $result, '->not_name() can be called several times');
    }

    public function testNameNotNameSameQuery()
    {
        $finder = sfFinder::type('file')->not_name('/^file2.*$/')->name('*.php')->relative();

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array('dir1/file12.php');

        $this->assertSame($expected, $result, '->not_name() and ->name() can be called in the same query');
    }

    public function testSizeReturnValue()
    {
        $finder = sfFinder::type('file');
        $this->assertSame($finder, $finder->size('> 2K'), '->size() implements the fluent interface');
    }

    /** @dataProvider sizeDataProvider */
    public function testSize($query, $expected)
    {
        $finder = sfFinder::type('file');

        foreach ($query as $stringSize) {
            $finder->size($stringSize);
        }
        $finder->relative();

        $this->assertSame($expected, $finder->in($this->fixtureDir), '->size() takes a size comparison string as its argument');
    }

    public function sizeDataProvider()/*: \Generator*/
    {
        yield [['> 100K'], array()];
        yield [['> 1K'], array('file1')];
        yield [['> 1K', '< 2K'], array()];
    }

    public function testDeptReturnValue()
    {
        $finder = sfFinder::type('file');
        $this->assertSame($finder, $finder->mindepth(1), '->mindepth() implements the fluent interface');
        $this->assertSame($finder, $finder->maxdepth(1), '->maxdepth() implements the fluent interface');
    }

    public function testMinDept()
    {
        $result = sfFinder::type('file')->relative()->mindepth(1)->in($this->fixtureDir);
        sort($result);
        $this->assertSame($this->minDepth1Files, $result, '->mindepth() takes a minimum depth as its argument');
    }

    public function testMaxDept()
    {
        $result = sfFinder::type('file')->relative()->maxdepth(2)->in($this->fixtureDir);
        sort($result);
        $this->assertSame($this->maxDepth2Files, $result, '->maxdepth() takes a maximum depth as its argument');
    }

    public function testMaxAndMinDept()
    {
        $result = sfFinder::type('file')->relative()->mindepth(1)->maxdepth(2)->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_intersect($this->minDepth1Files, $this->maxDepth2Files));
        sort($expected);

        $this->assertSame($expected, $result, '->maxdepth() and ->mindepth() can be called in the same query');
    }

    public function testDiscardReturnValue()
    {
        $finder = sfFinder::type('file');
        $this->assertSame($finder, $finder->discard('file2.txt'), '->discard() implements the fluent interface');
    }

    public function testDiscardFilename()
    {
        $finder = sfFinder::type('file')->relative()->discard('file2.txt');

        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, array('file2.txt')));
        sort($expected);

        $this->assertSame($expected, $result, '->discard() can discard a file name');
    }

    public function testDiscardGlobSupport()
    {
        $finder = sfFinder::type('file')->relative()->discard('*.php');
        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, $this->phpFiles));
        sort($expected);

        $this->assertSame($expected, $result, '->discard() can discard a glob pattern');
    }

    public function testDiscardRegexSupport()
    {
        $finder = sfFinder::type('file')->relative()->discard('/^file2.*$/');
        $result = $finder->in($this->fixtureDir);
        sort($result);

        $expected = array_values(array_diff($this->allFiles, $this->regexpFiles));
        sort($expected);

        $this->assertSame($expected, $result, '->discard() can discard a regexp pattern');
    }

    public function testPruneReturnValue()
    {
        $finder = sfFinder::type('file');
        $this->is($finder->prune('dir2'), $finder, '->prune() implements the fluent interface');
    }

    public function testPrune()
    {
        $finder = sfFinder::type('any')->relative()->prune('dir2');
        $result = $finder->in($this->fixtureDir);
        sort($result);

        $this->assertSame($this->anyWithoutDir2, $result, '->prune() ignore all files/directories under the given directory');
    }

    public function testPermissions()
    {
        chmod($this->permissionDir.'/secret', 0000);

        $result = sfFinder::type('file')->relative()->in($this->permissionDir);
        sort($result);

        $this->assertSame(array(), $result, '->in() ignores directories it cannot read');

        chmod($this->permissionDir.'/secret', 0755);
    }
}
