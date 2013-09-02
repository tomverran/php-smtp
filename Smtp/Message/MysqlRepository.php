<?php
/**
 * MySql.php
 * @author Tom
 * @since 02/09/13
 *
 * Database Schema:
 *
 --
 -- Database: `mail`
 --

 -- --------------------------------------------------------

 --
 -- Table structure for table `mail`
 --

 CREATE TABLE IF NOT EXISTS `mail` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `from` text COLLATE utf8_unicode_ci NOT NULL,
   `to` text COLLATE utf8_unicode_ci NOT NULL,
   `message` text COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;
 *
 */

namespace Smtp\Message;


class MysqlRepository implements Repository
{

    /**
     * @var \PDO
     */
    private $db;

    /**
     * Construct this MySQL Message Repository.
     * Suffice to say this is very dodgy and for testing only
     */
    public function __construct()
    {
        $this->db = new \PDO('mysql:host=127.0.0.1;dbname=mail', 'root', '');
    }

    /**
     * Save a particular message
     * @param Message $message
     */
    public function save(Message $message)
    {
        $query = $this->db->prepare('INSERT INTO `mail` (`from`, `to`, `message`) VALUES(?, ?, ?)');
        $query->execute(array($message->getReturnPath(), $message->getForwardPath(), $message->getData()));
    }
}