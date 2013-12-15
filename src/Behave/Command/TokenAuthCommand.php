<?php

namespace Behave\Command;

use Guzzle\Service\Command\OperationCommand;
use Guzzle\Service\Description\Parameter;
use Guzzle\Service\Description\OperationInterface;
use Guzzle\Common\Exception\UnexpectedValueException;

class TokenAuthCommand extends OperationCommand
{	
	protected function init()
	{   
		$tokenParam = new Parameter(array('name' => 'token', 'sentAs' => 'X-Behave-Api-Token', 'location' => 'header', 'required' => true));

		$this->operation->addParam($tokenParam);
	}
}