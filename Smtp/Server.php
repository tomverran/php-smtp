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
use Smtp\Message\Message;
use Smtp\Command\Library\Response;
use Smtp\Command\Library\CommandSet;
use Smtp\Command\Standard;
use Smtp\Message\MysqlRepository;
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
     * @var \Smtp\Message\Repository
     */
    private $repository;

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

        //initialise a very basic repository for saving
        $this->repository = new MysqlRepository();
    }

    /**
     * Set or reset a Client to an initial state
     * @param Client $client
     */
    protected function setupClient(Client $client)
    {
        $client->attachData('Message', new Message()); //attach a new message to our client
    }

    /**
     * Handle a new connection
     * @param Client $client
     */
    public function onNewConnection(Client $client)
    {
        $this->setupClient($client);
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
        echo $data."\n";

        //RFC 2821 Page 14 - 7 bit only plz.
        if (!mb_check_encoding($data, '7bit')) {
            $response = new Response('syntax error - invalid character',
                                     Response::SYNTAX_ERROR_COMMAND_UNRECOGNISED);

            $client->send("$response");
            return;
        }

        //If everything is okay we'll send the message to our CommandSet to parse it.
        $response = $this->commandSet->run($data, $client->getData('Message'));
        if ($response instanceof Response) {
            $client->send("$response");
            echo $response."\n";

            //the command specified that we should now disconnect
            if ($response->hasFlag(Response::FLAG_DISCONNECT)) {
                $client->disconnect();
            }

            //the command specified we're done
            if ($response->hasFlag(Response::FLAG_DONE)) {
                $this->repository->save($client->getData('Message'));
                $this->setupClient($client);
            }
        }
    }
}