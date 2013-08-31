<?php
/**
 * Standard.php
 * @author Tom
 * @since 31/08/13
 */

namespace Smtp\Command;


use Smtp\Command\Library\CommandSet;
use Smtp\Command\Library\Message;
use Smtp\Command\Library\Response;

/**
 * Standard SMTP Commands (i.e. no EHLO nonsense).
 * Written with reference to RFC 2821.
 * @package Smtp\Command
 */
class Standard extends CommandSet
{

    /**
     * @command HELO
     * Deal with the HELO SMTP command.
     * @param string $command The text sent by the client
     * @param Message $message The message object to update
     * @return \Smtp\Command\Library\Response
     */
    public function hello($command, Message $message)
    {
        if (!$message->getClientIdentity()) {
            $message->setClientIdentity($command);
            return new Response('And a hearty HELO to you too', Response::MAIL_ACTION_OKAY_COMPLETED);
        }

        return new Response('You\'ve already said that', Response::BAD_COMMAND_SEQUENCE);
    }

    /**
     * @command NOOP
     * @return Response
     */
    public function noop()
    {
        return new Response('Okay', Response::MAIL_ACTION_OKAY_COMPLETED);
    }

    /**
     * @command RSET
     * @param $command
     * @param Message $message
     * @return Response
     */
    public function reset($command, Message $message)
    {
        $message = new Message(); //reset the message to an initial state by overwriting it
        return new Response('Everything has been reset', Response::MAIL_ACTION_OKAY_COMPLETED);
    }

    /**
     * @command QUIT
     * @return \Smtp\Command\Library\Response
     */
    public function quit()
    {
        return new Response('Have a nice day', Response::SERVICE_CLOSING_TRANSMISSION_CHANNEL,
                                               Response::FLAG_DISCONNECT);
    }
}