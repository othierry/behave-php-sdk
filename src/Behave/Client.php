<?php

namespace Behave;

use \Guzzle\Common\Collection;
use \Guzzle\Service\Description\ServiceDescription;
use \Guzzle\Service\Builder\ServiceBuilder;

use \Behave\Plugin\TokenAuthPlugin;

class Client extends \Guzzle\Service\Client
{
	public static function createClient($config = array())
	{
		$serviceDescriptionFile = __DIR__ . '/Resources/services.php';
		$serviceBuilder = ServiceBuilder::factory($serviceDescriptionFile);	

		$client = $serviceBuilder->get('behave');
		if (isset($config['app_token'])) {
			$tokenPlugin = new TokenAuthPlugin($config['app_token']);
			$client->addSubscriber($tokenPlugin);
		}
		else {
			//TODO throught good exception
			exit(1);
		}

		return $client;
	}
	
	
	public static function factory($config = array())
	{
		$client = new Client(Collection::fromConfig($config));

		return $client;
	}

	public function __construct(Collection $config)
	{
		parent::__construct($config->get("base_url"), $config);

		// Make sure the user agent is prefixed by the SDK version
		$this->setUserAgent('behave-sdk-php2/v1', true);

		$description = ServiceDescription::factory(__DIR__ . '/Resources/operations.php');
		$this->setDescription($description);

		return $this;		
	}
}