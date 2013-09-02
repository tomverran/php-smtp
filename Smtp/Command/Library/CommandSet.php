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
     * @var callable|null A callable that returned FLAG_GREEDY as part of a Response
     * to a previous command so should be unconditionally routed data until further notice
     */
    private $greedyCallable = null;

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
     * @return \Smtp\Command\Library\Response|null
     */
    public function run($command, Message $message)
    {
        //init response
        $response = false;
        $callableCalled = null;

        //first call any greedy callables
        if (is_callable($this->greedyCallable)) {
            $response = call_user_func_array($this->greedyCallable, array($command, $message));
            $callableCalled = $this->greedyCallable;
        } else {

            //normal case is to find a callable matching our command data
            foreach ($this->commandMap as $validCommand=>$function) {
                if (strpos($command, $validCommand) === 0) {
                    $commandWithoutCommand = preg_replace('/'.preg_quote($validCommand, '/').'/', '', $command, 1);
                    $response = call_user_func_array($function, array($commandWithoutCommand, $message));
                    $callableCalled = $function;
                    break;
                }
            }
        }

        //now process the response from wherever
        if ($response instanceof Response) {

            //save our greedy callable to unconditionally call next time
            if ($response->hasFlag(Response::FLAG_MULTILINE)) {
                $this->greedyCallable = $callableCalled;
            } else {
                $this->greedyCallable = null;
            }

            return $response;
        } else if ($response === null) {
            return null;
        }

        //if we got here then we had no functions with which we could parse the command so eh
        return new Response('Unrecognised command', Response::SYNTAX_ERROR_COMMAND_UNRECOGNISED);
    }
}