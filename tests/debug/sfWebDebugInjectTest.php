<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/sfWebDebugTest.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWebDebugInjectTest extends TestCase
{
    private sfWebDebugTest $debug;

    protected function setUp(): void
    {
        $this->debug = new sfWebDebugTest();
    }

    public function testInjectToolbar()
    {
        $before = '<html><head></head><body></body></html>';
        $after = $this->debug->injectToolbar($before);

        $this->assertMatchesRegularExpression('/<style type="text\/css">/', $after, '->injectToolbar() adds styles');
        $this->assertMatchesRegularExpression('/<script type="text\/javascript">/', $after, '->injectToolbar() adds javascript');
        $this->assertMatchesRegularExpression('/<div id="sfWebDebug">/', $after, '->injectToolbar() adds the toolbar');

        $before = '';
        $after = $this->debug->injectToolbar($before);

        $this->assertDoesNotMatchRegularExpression('/<style type="text\/css">/', $after, '->injectToolbar() does not add styles if there is no head');
        $this->assertDoesNotMatchRegularExpression('/<script type="text\/javascript">/', $after, '->injectToolbar() does not add javascripts if there is no body');
        $this->assertMatchesRegularExpression('/<div id="sfWebDebug">/', $after, '->injectToolbar() adds the toolbar if there is no body');

        $before = <<<'HTML'
<html>
<head></head>
<body>
<textarea><html><head></head><body></body></html></textarea>
</body>
</html>
HTML;

        $after = $this->debug->injectToolbar($before);

        $this->assertSame(1, substr_count($after, '<style type="text/css">'), '->injectToolbar() adds styles once');
        $this->assertSame(1, substr_count($after, '<script type="text/javascript">'), '->injectToolbar() adds javascripts once');
        $this->assertSame(1, substr_count($after, '<div id="sfWebDebug">'), '->injectToolbar() adds styles once');

        $this->assertIsInt(strpos($after, '<textarea><html><head></head><body></body></html></textarea>'), '->injectToolbar() leaves inner pages untouched');
    }
}
