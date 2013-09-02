<?php
/**
 * Messag.php
 * @author Tom
 * @since 02/09/13
 */

namespace Smtp\Message;


interface Repository {
    public function save(Message $message);
}