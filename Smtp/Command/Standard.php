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
     * @command MAIL FROM
     * This is quite a key command, really.
     * @param string $command What the client said to us
     * @param Message $message The message to modify
     * @return \Smtp\Command\Library\Response
     */
    public function mail($command, Message $message)
    {
        $command = trim($command);
        if ($message->getClientIdentity()) {

            $pos1 = strpos($command, '<');
            $pos2 = strrpos($command, '>');

            if ($pos1 === 0 && $pos2 !== false) {
                $address = substr($command, 1, $pos2);
                $message->setReturnPath($address);

                return new Response('Gotcha', Response::MAIL_ACTION_OKAY_COMPLETED);
            } else {
                return new Response('Syntax error, missing some angle brackets.'.$pos1 .', '.$pos2.': '.$command,
                                    Response::SYNTAX_ERROR_COMMAND_UNRECOGNISED);
            }
        }

        return new Response('It is rude to send mail without saying HELO', Response::BAD_COMMAND_SEQUENCE);
    }

    /**
     * @command RCPT TO
     * Set who this email is supposed to go to. Since it is the 21st century we reject any relay requests.
     * @param string $command The command the user ehas given
     * @param Message $message The message to updat
     */
    public function rcptTo($command, Message $message)
    {

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
        $message->reset();
        return new Response('Okay', Response::MAIL_ACTION_OKAY_COMPLETED);
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