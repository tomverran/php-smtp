<?php
/**
 * Loop.php
 * @author Tom
 * @since 31/08/13
 */
namespace Event;

/**
 * An event loop that Pollable objects can register themselves with to be polled periodically.
 * @package Event
 */
class Loop {

    /**
     * @var Pollable[]
     */
    private $pollables;

    /**
     * Construct this Loop.
     * Not much to do here presently.
     */
    public function __construct()
    {
    }

    /**
     * Register a Pollable component to be periodically polled by our loop
     * @param Pollable $pollable
     */
    public function register(Pollable $pollable)
    {
        $this->pollables[] = $pollable;
    }

    /**
     * Start the event loop going.
     */
    public function start()
    {
        while (true) {

            sleep(0.25);
            foreach ($this->pollables as &$pollable) {
                $pollable->poll();
            }
        }
    }

}