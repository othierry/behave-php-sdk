<?php

namespace Behave\Plugin;

use Guzzle\Common\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Guzzle\Service\Description\Parameter;

use Behave\Command\TokenAuthCommand;

/**
 * Adds specified curl auth to all requests sent from a client. Defaults to CURLAUTH_BASIC if none supplied.
 */
class TokenAuthPlugin implements EventSubscriberInterface
{
    private $token;

    /**
     * Constructor
     *
     * @param string $username HTTP basic auth username
     * @param string $password Password
     * @param int    $scheme   Curl auth scheme
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array('command.before_prepare' => array('onCommandPrepare', 255));
    }

    /**
     * Use session for building command
     *
     * @param Event $event
     */
    public function onCommandPrepare(Event $event)
    {
		if ($event['command'] instanceof TokenAuthCommand) {
			$event['command']['token'] = $this->token;
		}
    }
}