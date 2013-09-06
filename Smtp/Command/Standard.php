<?php
/**
 * Standard.php
 * @author Tom
 * @since 31/08/13
 */

namespace Smtp\Command;


use Smtp\Command\Library\CommandSet;
use Smtp\Message\Message;
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

        return new Response("You've already said that", Response::BAD_COMMAND_SEQUENCE);
    }

    /**
     * @command MAIL FROM:
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
                $address = substr($command, 1, $pos2 - 1);
                $message->setReturnPath($address);

                return new Response('OK', Response::MAIL_ACTION_OKAY_COMPLETED);
            } else {
                return new Response('Syntax error, missing some angle brackets.',
                                    Response::SYNTAX_ERROR_IN_PARAMETERS);
            }
        }

        return new Response('It is rude to send mail without saying HELO', Response::BAD_COMMAND_SEQUENCE);
    }

    /**
     * @command RCPT TO:
     * Set who this email is supposed to go to. Since it is the 21st century we ignore forward paths.
     * @param string $command The command the user has given
     * @param Message $message The message to update
     * @return \Smtp\Command\Library\Response
     */
    public function rcptTo($command, Message $message)
    {
        $command = trim($command);
        if (!$message->getReturnPath()) {
            return new Response('You need to MAIL TO first, silly.', Response::BAD_COMMAND_SEQUENCE);
        }

        $pos1 = strpos($command, '<');
        $pos2 = strrpos($command, '>');

        if ($pos1 !== 0 || $pos2 === false) {
            return new Response('Syntax error, missing some angle brackets.',
                                Response::SYNTAX_ERROR_IN_PARAMETERS);
        }

        $forwardPaths = explode(',', substr($command, 1, $pos2 - 1));
        $finalForwardAddress = array_pop($forwardPaths);
        $message->setForwardPath($finalForwardAddress);

        return new Response('OK', Response::MAIL_ACTION_OKAY_COMPLETED);
    }

    /**
     * @command DATA
     * @param string $command The command the user sent. In this case it'll be their data.
     * @param Message $message The message object to modify
     * @return \Smtp\Command\Library\Response|null
     */
    public function data($command, Message $message)
    {
        if ($message->getData() === null) {
            $message->addData(''); //bit hacky, make our message not null so we get out of this conditional next time
            return new Response('I am all ears', Response::START_MAIL_INPUT, Response::FLAG_MULTILINE);
        }

        if (trim($command) == '.') {
            return new Response('Right-o', Response::MAIL_ACTION_OKAY_COMPLETED, Response::FLAG_DONE);
        }

        //helpfully CommandSet strips out crlfs so back they go
        $message->addData($command . "\r\n");
        return null;
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
        return new Response('OK', Response::MAIL_ACTION_OKAY_COMPLETED);
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