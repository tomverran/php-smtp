<?php
/**
 * Message.php
 * @author Tom
 * @since 29/08/13
 */

namespace Smtp\Message;

/**
 * A class modelling an email to be received from a client
 * @package Smtp\Message
 */
class Message
{
    /**
     * @var string|null Actual message data.
     * Can be either null or some kind of string
     */
    private $data;

    /**
     * @var string The identity of the client,
     * as transmitted with the HELO command
     */
    private $clientIdentity;

    /**
     * @var string The return path specified
     * by the client, i.e. who the email is from
     */
    private $returnPath;

    /**
     * @var string The forward path specified.
     * We ignore all of it except the final address
     * so this is essentially the To: address.
     */
    private $forwardPaths;

    /**
     * @param string $returnPath
     */
    public function setReturnPath($returnPath)
    {
        $this->returnPath = $returnPath;
    }

    /**
     * @return string
     */
    public function getReturnPath()
    {
        return $this->returnPath;
    }

    /**
     * Construct our message by calling reset
     * which should set it to an initial state
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * (re)set our message to an initial state.
     */
    public function reset()
    {
        $this->clientIdentity = null;
        $this->forwardPaths = array();
        $this->returnPath = null;
        $this->data = null;
    }

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

    /**
     * Set the forward path of our email
     * @param $forwardPath
     */
    public function addForwardPath($forwardPath)
    {
        $this->forwardPaths[] = $forwardPath;
    }

    /**
     * Get the forward path of our email
     * @return array
     */
    public function getForwardPaths()
    {
        return $this->forwardPaths;
    }

    public function addData($data)
    {
        if ($this->data == null) {
            $this->data = '';
        }

        $this->data .= $data;
    }

    public function getData()
    {
        return $this->data;
    }
}