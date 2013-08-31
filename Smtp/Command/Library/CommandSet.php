<?php
/**
 * CommandSet.php
 * @author Tom
 * @since 29/08/13
 */

namespace Smtp\Command\Library;


abstract class CommandSet
{

    /**
     * Maps commands to functions to execute.
     * @var callable[]
     */
    private $commandMap = array();

    /**
     * Construct this commandset
     * this builds up a dynamic list of functions to execute based on commands
     * with the programmer's best friend, reflection!
     */
    public function __construct()
    {
        $rc = new \ReflectionObject($this);
        foreach ($rc->getMethods() as $rm) {

            $matches = array(); //don't play with matches.
            $comment = $rm->getDocComment();
            if (preg_match('/@command ([^\r\n]+)/', $comment, $matches)) {
                $this->commandMap[$matches[1]] = array($this, $rm->getName());
            }
        }
    }

    /**
     * Run the given command sent from the client
     * @param string $command The unedited line from the client
     * @param Message $message The message object to update
     * @return \Smtp\Command\Library\Response
     */
    public function run($command, Message $message)
    {
        foreach ($this->commandMap as $validCommand=>$function) {
            if (strpos($command, $validCommand) === 0) {
                $response = call_user_func_array($function, array(preg_replace('/'.preg_quote($validCommand, '/').'/', $command, 1), $message));
                if ($response instanceof Response) {
                    return $response;
                }
            }
        }

        //if we got here then we had no functions with which we could parse the command so eh
        return new Response('Unrecognised command', Response::SYNTAX_ERROR_COMMAND_UNRECOGNISED);
    }
}