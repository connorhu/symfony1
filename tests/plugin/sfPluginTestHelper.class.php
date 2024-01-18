<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfPluginTestHelper
{
    public static function convertUrlToFixture($url)
    {
        $file = preg_replace(array('/_+/', '#/+#', '#_/#'), array('_', '/', '/'), preg_replace('#[^a-zA-Z0-9\-/\.]#', '_', $url));

        $dir = dirname($file);
        $file = basename($file);

        $dest = SF_PLUGIN_TEST_DIR.'/'.$dir.'/'.$file;

        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0777, true);
        }

        $fixturePath = __DIR__.'/../fixtures/plugin/'.$dir.'/'.$file;
        $fixturePath = realpath($fixturePath);

        if (!file_exists($fixturePath)) {
            throw new sfException(sprintf('Unable to find fixture for %s (%s)', $url, $file));
        }

        copy($fixturePath, $dest);

        return $dest;
    }
}
