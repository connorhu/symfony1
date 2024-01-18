<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/myViewConfigHandler.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfViewConfigHandlerTest extends TestCase
{
    public function testAddHtmlAsset()
    {
        $handler = new myViewConfigHandler();

        $handler->setConfiguration(array(
            'myView' => array(
                'stylesheets' => array('foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds stylesheets to the response');

        $handler->setConfiguration(array(
            'myView' => array(
                'stylesheets' => array(array('foobar' => array('position' => 'last'))),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('foobar', 'last', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds stylesheets to the response');

        $handler->setConfiguration(array(
            'myView' => array(
                'javascripts' => array('foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds JavaScript to the response');

        $handler->setConfiguration(array(
            'myView' => array(
                'javascripts' => array(array('foobar' => array('position' => 'last'))),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('foobar', 'last', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds JavaScript to the response');

        $handler->setConfiguration(array(
            'myView' => array(
                'stylesheets' => array('foobar'),
            ),
            'all' => array(
                'stylesheets' => array('all_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('all_foobar', '', array ());
  $response->addStylesheet('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific stylesheets after application-wide assets');

        $handler->setConfiguration(array(
            'all' => array(
                'stylesheets' => array('all_foobar'),
            ),
            'myView' => array(
                'stylesheets' => array('foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('all_foobar', '', array ());
  $response->addStylesheet('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific stylesheets after application-wide assets');

        $handler->setConfiguration(array(
            'myView' => array(
                'stylesheets' => array('foobar'),
            ),
            'default' => array(
                'stylesheets' => array('default_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('default_foobar', '', array ());
  $response->addStylesheet('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific stylesheets after default assets');

        $handler->setConfiguration(array(
            'default' => array(
                'stylesheets' => array('default_foobar'),
            ),
            'myView' => array(
                'stylesheets' => array('foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('default_foobar', '', array ());
  $response->addStylesheet('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific stylesheets after default assets');

        $handler->setConfiguration(array(
            'default' => array(
                'stylesheets' => array('default_foobar'),
            ),
            'all' => array(
                'stylesheets' => array('all_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('default_foobar', '', array ());
  $response->addStylesheet('all_foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds application-specific stylesheets after default assets');

        $handler->setConfiguration(array(
            'all' => array(
                'stylesheets' => array('all_foobar'),
            ),
            'default' => array(
                'stylesheets' => array('default_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('default_foobar', '', array ());
  $response->addStylesheet('all_foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds application-specific stylesheets after default assets');

        $handler->setConfiguration(array(
            'myView' => array(
                'javascripts' => array('foobar'),
            ),
            'all' => array(
                'javascripts' => array('all_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('all_foobar', '', array ());
  $response->addJavascript('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific javascripts after application-wide assets');

        $handler->setConfiguration(array(
            'all' => array(
                'javascripts' => array('all_foobar'),
            ),
            'myView' => array(
                'javascripts' => array('foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('all_foobar', '', array ());
  $response->addJavascript('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific javascripts after application-wide assets');

        $handler->setConfiguration(array(
            'myView' => array(
                'javascripts' => array('foobar'),
            ),
            'default' => array(
                'javascripts' => array('default_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('default_foobar', '', array ());
  $response->addJavascript('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific javascripts after default assets');

        $handler->setConfiguration(array(
            'default' => array(
                'javascripts' => array('default_foobar'),
            ),
            'myView' => array(
                'javascripts' => array('foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('default_foobar', '', array ());
  $response->addJavascript('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds view-specific javascripts after default assets');

        $handler->setConfiguration(array(
            'default' => array(
                'javascripts' => array('default_foobar'),
            ),
            'all' => array(
                'javascripts' => array('all_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('default_foobar', '', array ());
  $response->addJavascript('all_foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds application-specific javascripts after default assets');

        $handler->setConfiguration(array(
            'all' => array(
                'javascripts' => array('all_foobar'),
            ),
            'default' => array(
                'javascripts' => array('default_foobar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('default_foobar', '', array ());
  $response->addJavascript('all_foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() adds application-specific javascripts after default assets');

        $handler->setConfiguration(array(
            'all' => array(
                'stylesheets' => array('all_foo', 'all_bar'),
            ),
            'myView' => array(
                'stylesheets' => array('foobar', '-all_bar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('all_foo', '', array ());
  $response->addStylesheet('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() supports the - option to remove one stylesheet previously added');

        $handler->setConfiguration(array(
            'all' => array(
                'javascripts' => array('all_foo', 'all_bar'),
            ),
            'myView' => array(
                'javascripts' => array('foobar', '-all_bar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('all_foo', '', array ());
  $response->addJavascript('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() supports the - option to remove one javascript previously added');

        $handler->setConfiguration(array(
            'all' => array(
                'stylesheets' => array('foo', 'bar', '-*', 'baz'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('baz', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() supports the -* option to remove all stylesheets previously added');

        $handler->setConfiguration(array(
            'all' => array(
                'javascripts' => array('foo', 'bar', '-*', 'baz'),
            ),
        ));
        $content = <<<'EOF'
  $response->addJavascript('baz', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() supports the -* option to remove all javascripts previously added');

        $handler->setConfiguration(array(
            'all' => array(
                'stylesheets' => array('-*', 'foobar'),
            ),
            'default' => array(
                'stylesheets' => array('default_foo', 'default_bar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('foobar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() supports the -* option to remove all assets previously added');

        $handler->setConfiguration(array(
            'myView' => array(
                'stylesheets' => array('foobar', '-*', 'bar'),
                'javascripts' => array('foobar', '-*', 'bar'),
            ),
            'all' => array(
                'stylesheets' => array('all_foo', 'all_foofoo', 'all_barbar'),
                'javascripts' => array('all_foo', 'all_foofoo', 'all_barbar'),
            ),
            'default' => array(
                'stylesheets' => array('default_foo', 'default_foofoo', 'default_barbar'),
                'javascripts' => array('default_foo', 'default_foofoo', 'default_barbar'),
            ),
        ));
        $content = <<<'EOF'
  $response->addStylesheet('bar', '', array ());
  $response->addJavascript('bar', '', array ());

EOF;
        $this->assertSame(fix_linebreaks($handler->addHtmlAsset('myView')), fix_linebreaks($content), 'addHtmlAsset() supports the -* option to remove all assets previously added');
    }
}
