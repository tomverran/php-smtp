<?php
/**
 * Cl.php
 * @author Tom
 * @since 31/08/13
 */

namespace Net;


class Client
{

    /**
     * A valid connection to a client
     * @var resource
     */
    private $socket;

    /**
     * An array of any data attached to this client
     * @var mixed[]
     */
    private $data;

    /**
     * Construct this client
     * @param \resource $socket
     */
    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    /**
     * Send a message to the client
     * @param $message
     */
    public function send($message)
    {
        \socket_write($this->socket, $message, strlen($message));
    }

    /**
     * Attach something to our client with a particular tag,
     * allowing state to be preserved between responses
     * @param string $tag The tag of the item
     * @param mixed $data The data to attach
     */
    public function attachData($tag, $data)
    {
        $this->data[$tag] = $data;
    }

    /**
     * Get data from our client
     * @param string $tag The tag to get data
     * @return mixed|null
     */
    public function getData($tag)
    {
        if (isset($this->data[$tag])) {
            return $this->data[$tag];
        }
        return null;
    }

    /**
     * Get the underlying socket connection
     * @return resource
     */
    public function getConnection()
    {
        return $this->socket;
    }

    public function disconnect()
    {
        \socket_close($this->socket);
    }

    public function isConnected()
    {
        return true;
    }
}