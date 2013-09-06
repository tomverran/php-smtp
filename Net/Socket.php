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
     * Construct this Socket, setting up a socket listening on port 25
     */
    public function __construct()
    {
        //create a non-blocking TCP socket to receive emails on
        $this->socket = \stream_socket_server('tcp://127.0.0.1:25');
        if (!$this->socket) {
            throw new \Exception('Unable to listen on given port');
        }
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
        $sockets = array($this->socket);
        foreach ($this->clients as $key => $client) {
            if ($client->isConnected()) {
                $sockets[] = $client->getConnection();
            } else {
                unset($this->clients[$key]);
            }
        }

        $write = array();
        $except = array();

        //now wait for something to happen to one of our sockets
        \stream_select($sockets, $write, $except, null);

        foreach ($sockets as $socket)
        {
            //if its our main listen socket
            //then someone must want to connect
            if ($socket == $this->socket) {
                $connection = \stream_socket_accept($this->socket);
                $id = (int)$connection;

                $this->clients[$id] = new Client($connection);
                $this->runHandlers(Socket::CONNECT, array(end($this->clients)));
                $this->buffers[$id] = ''; //initialise an empty line buffer for the client
            } else {

                //this is highly dodgy
                $id = (int)$socket;

                //else find out what the client
                //has to say for themselves
                $data = \fread($socket, 1);

                if ($data === false) {
                    socket_close($socket);
                    unset($this->clients[$id]);
                    continue;
                }

                $this->buffers[$id] .= $data;
                if (substr($this->buffers[$id], -2) == "\r\n") {
                    $this->runHandlers(Socket::DATA, array($this->clients[$id], substr($this->buffers[$id], 0, -2)));
                    $this->buffers[(int)$socket] = '';
                }
            }
        }
    }
}