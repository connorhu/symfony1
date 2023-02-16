<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class testValidatorFile extends sfValidatorFile
{
    public function getMimeType($file, $fallback)
    {
        return parent::getMimeType($file, $fallback);
    }

    public function guessFromNothing($file)
    {
        return 'nothing/plain';
    }

    public function guessFromFileinfo($file)
    {
        return parent::guessFromFileinfo($file);
    }

    public function guessFromMimeContentType($file)
    {
        return parent::guessFromMimeContentType($file);
    }

    public function guessFromFileBinary($file)
    {
        return parent::guessFromFileBinary($file);
    }

    public function getMimeTypesFromCategory($category)
    {
        return parent::getMimeTypesFromCategory($category);
    }
}
