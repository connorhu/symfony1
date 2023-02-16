<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DetailedDescriptionTestTask extends BaseTestTask
{
    protected function configure()
    {
        $this->detailedDescription = <<<'EOF'
The [detailedDescription|INFO] formats special string like [...|COMMENT] or [--xml|COMMENT]
EOF;
    }
}
