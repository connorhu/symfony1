<?php

use \Symfony1\Components\Action\Action;

$triggerMessage = sprintf('Using the "%s" class is deprecated since symfony1 version 1.6, use "%s" instead.', 'sfAction', 'Symfony1\Components\Action\Action');
@trigger_error($triggerMessage, \E_USER_DEPRECATED);

/** @deprecated since symfony1 1.6, use "Symfony1\Components\Action\Action" instead */
abstract class sfAction extends Action
{
}
