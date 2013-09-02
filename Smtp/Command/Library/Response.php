<?php
/**
 * Response.php
 * @author Tom
 * @since 31/08/13
 */

namespace Smtp\Command\Library;


/**
 * A class to model responses from SMTP commands.
 * Commands may also indicate they've been completed
 * and will not be called with any further input this session
 */
class Response
{

    /**
     * @var string The response text to give
     */
    private $text;

    /**
     * @var int the SMTP response code
     */
    private $code;

    /**
     * @var bool Does this response also complete the command?
     * If so the command won't be called again
     */
    private $complete;

    /**
     * @var int Flags to send, flags set individual bits.
     */
    private $flags;

    /*
     * SMTP response code consts
     */
    const SUCCESS = 200;

    const STATUS_OR_HELP_REPLY = 211;

    const HELP_MESSAGE = 214;

    const SERVICE_READY = 220;

    const SERVICE_CLOSING_TRANSMISSION_CHANNEL = 221;

    const MAIL_ACTION_OKAY_COMPLETED = 250;

    const USER_NOT_LOCAL_WILL_FORWARD = 251;

    const START_MAIL_INPUT = 354;

    const SERVICE_NOT_AVAILABLE_CLOSING_TRANSMISSION_CHANNEL = 421;

    const MAILBOX_UNAVAILABLE_MAIL_ACTION_NOT_TAKEN = 450;

    const ACTION_ABORTED_LOCAL_PROCESSING_ERROR = 451;

    const ACTION_ABORTED_INSUFFICIENT_STORAGE = 452;

    const SYNTAX_ERROR_COMMAND_UNRECOGNISED = 500;

    const SYNTAX_ERROR_IN_PARAMETERS = 501;

    const COMMAND_NOT_IMPLEMENTED = 502;

    const BAD_COMMAND_SEQUENCE = 503;

    const COMMAND_PARAMETER_NOT_IMPLEMENTED = 504;

    const DOES_NOT_ACCEPT_MAIL = 521;

    const ACCESS_DENIED = 530;

    const MAILBOX_UNAVAILABLE_ACTION_NOT_TAKEN = 550;

    const USER_NOT_LOCAL = 551;

    const EXCEEDED_STORAGE_ALLOCATION = 552;

    const MAILBOX_NAME_NOT_ALLOWED = 553;

    const TRANSACTION_FAILED = 554;

    /*
     * Flags to send by use of bitwise operations.
     * As such they should correspond to powers of two!
     */
    const FLAG_DISCONNECT = 1;

    //route the subsequent command to me please until I return a non multiline response
    const FLAG_MULTILINE = 2;

    /**
     * Constructor which provides shortcuts to initialise
     * the various properties of this response
     * @param string $text Response text
     * @param int $code The response code
     * @param int $flags Any other flags
     */
    public function __construct($text, $code, $flags = 0)
    {
        $this->text = $text;
        $this->code = $code;
        $this->flags = $flags;
    }

    /**
     * Set a text response
     * @param $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Set a numeric response code
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Set whether the commnd returning this Response
     * is complete and so shouldn't be run again
     * @param $complete
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;
    }

    /**
     * Get a text description of this command
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get the response code of this command
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get whether the command is complete
     * @return bool
     */
    public function commandIsComplete()
    {
        return $this->complete;
    }

    /**
     * Has the given flag been set?
     * @param int $flag One of the FLAG_ consts.
     * @return int 0 or whatever when the flag has been set
     */
    public function hasFlag($flag)
    {
        return $this->flags & $flag;
    }

    /**
     * Get a string representation of this response
     * @return string
     */
    public function __toString()
    {
        return $this->code . ' ' . $this->text . "\r\n";
    }
}