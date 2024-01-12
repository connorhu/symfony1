<?php

namespace Symfony1\Components\Filter;

use Symfony1\Components\Form\Form;
use Symfony1\Components\Form\FormField;
use Symfony1\Components\View\View;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
* sfRenderingFilter is the last filter registered for each filter chain. This
filter does the rendering.
*
* @author Fabien Potencier <fabien.potencier@symfony-project.com>
*
* @version SVN: $Id$
*/
class RenderingFilter extends Filter
{
    /**
     * Executes this filter.
     *
     * @param FilterChain $filterChain the filter chain
     *
     * @throws <b>sfInitializeException</b> If an error occurs during view initialization
     * @throws <b>sfViewException</b>       If an error occurs while executing the view
     */
    public function execute($filterChain)
    {
        // execute next filter
        $filterChain->execute();
        // get response object
        $response = $this->context->getResponse();
        // hack to rethrow sfForm and|or sfFormField __toString() exceptions (see sfForm and sfFormField)
        if (Form::hasToStringException()) {
            throw Form::getToStringException();
        }
        if (FormField::hasToStringException()) {
            throw FormField::getToStringException();
        }
        // send headers + content
        if (View::RENDER_VAR != $this->context->getController()->getRenderMode()) {
            $response->send();
        }
    }
}
class_alias(RenderingFilter::class, 'sfRenderingFilter', false);