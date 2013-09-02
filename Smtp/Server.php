<?php
/**
 * Server.php
 * @author Tom
 * @since 29/08/13
 */
namespace Smtp;

use Event\Loop;
use Net\Client;
use Net\Socket;
use Smtp\Command\Library\Message;
use Smtp\Command\Library\Response;
use Smtp\Command\Library\CommandSet;
use Smtp\Command\Standard;

class Server
{
    /**
     * @var Socket
     */
    private $socket;

    /**
     * @var CommandSet
     */
    private $commandSet;

    /**
     * Create a new PHP SMTP server.
     * You have no idea how wrong it felt typing that.
     */
    public function __construct(Loop $loop)
    {
        //we want only standard SMTP for now
        $this->commandSet = new Standard();

        $this->socket = new Socket();
        $this->socket->addHandler(Socket::CONNECT, array($this, 'onNewConnection'));
        $this->socket->addHandler(Socket::DATA, array($this, 'onData'));
        $loop->register($this->socket);
    }

    /**
     * Handle a new connection
     * @param Client $client
     */
    public function onNewConnection(Client $client)
    {
        $client->attachData('Message', new Message()); //attach a new message to our client
        $response = new Response('Hello there.', Response::SERVICE_READY, false);
        $client->send("$response");
    }

    /**
     * Respond to some data from a client
     * @param Client $client The client who sent the data
     * @param string $data A Buffered line of data
     */
    public function onData(Client $client, $data)
    {
        //RFC 2821 Page 14 - ASCII only plz.
        if (!mb_check_encoding($data, 'ASCII')) {
            $response = new Response('syntax error - invalid character',
                                     Response::SYNTAX_ERROR_COMMAND_UNRECOGNISED);

            $client->send("$response");
            return;
        }

        //If everything is okay we'll send the message to our CommandSet to parse it.
        $response = $this->commandSet->run($data, $client->getData('Message'));
        if ($response instanceof Response) {
            $client->send("$response");

            //the command specified that we should now disconnect
            if ($response->hasFlag(Response::FLAG_DISCONNECT)) {
                $client->disconnect();
            }
        }

    }
}