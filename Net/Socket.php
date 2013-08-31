<?php
/**
 * Socket.php
 * @author Tom
 * @since 29/08/13
 */

namespace Net;
use Event\Pollable;

class Socket implements Pollable
{

    /**
     * @var \resource
     */
    private $socket;

    /**
     * @var Client[]
     */
    private $clients = array();

    /**
     * Per-client line buffers because PHP_NORMAL_READ works badly.
     * @var array
     */
    private $buffers = array();

    /*
     * Types of handler we can have
     */
    const DISCONNECT = 0;

    const CONNECT = 1;

    const DATA = 2;

    /**
     * An array of callable handlers for each type
     * @var array[callable[]]
     */
    private $handlers = array(
        Socket::DISCONNECT => array(),
        Socket::CONNECT => array(),
        Socket::DATA => array()
    );

    /**
     * Construct this Socket, setting up an actual non blocking socket listening on port 25
     * with PHP's rather archaic procedural library
     */
    public function __construct()
    {
        //create a non-blocking TCP socket to receive emails on
        $this->socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        \socket_set_nonblock($this->socket);

        //we want to listen on localhost:25 for connections
        \socket_bind($this->socket, '127.0.0.1', 25);
        \socket_listen($this->socket);
    }

    /**
     * Add a handler for a particular connection event
     * @param int $type The type of event to handle, one of the consts
     * @param callable $handler A callable to respond to the event
     */
    public function addHandler($type, callable $handler)
    {
        $this->handlers[$type][] = $handler;
    }

    /**
     * Run our handlers for a particular type of event
     * @param int $type The event type - one of the class constants
     * @param array $args An optional array of arguments to pass to the callables
     */
    private function runHandlers($type, $args = array())
    {
        foreach ($this->handlers[$type] as $callable) {
            call_user_func_array($callable, $args);
        }
    }

    /**
     * Get polled by an external driver of some kind
     * allowing us to periodically process events
     */
    public function poll()
    {
        //handle new connections
        if ($connection = @\socket_accept($this->socket)) {
            $this->clients[] = new Client($connection);
            $this->runHandlers(Socket::CONNECT, array(end($this->clients)));
            $this->buffers[] = ''; //initialise an empty line buffer for the client
        }

        //handle receiving data
        foreach ($this->clients as $id => $client) {
            /** @var Client $client */
            $connection = $client->getConnection();
            if ($data = \socket_read($connection, 1)) {

                $this->buffers[$id] .= $data;
                if (substr($this->buffers[$id], -2) == "\r\n") {
                    $this->runHandlers(Socket::DATA, array($client, substr($this->buffers[$id], 0, -2)));
                    $this->buffers[$id] = '';
                }
            }
        }
    }
}