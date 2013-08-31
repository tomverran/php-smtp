<?php
/**
 * Message.php
 * @author Tom
 * @since 29/08/13
 */

namespace Smtp\Command\Library;

/**
 * A class modelling an email to be received from a client
 * @package Smtp\Command
 */
class Message
{

    /**
     * @var string The identity of the client,
     * as transmitted with the HELO command
     */
    private $clientIdentity = null;

    /**
     * Get the identity of our client
     * @return string|null
     */
    public function getClientIdentity()
    {
        return $this->clientIdentity;
    }

    /**
     * Set the identity of our client.
     * @param $identity
     */
    public function setClientIdentity($identity)
    {
        $this->clientIdentity = $identity;
    }
}