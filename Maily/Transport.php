<?php
/**
 * the Maily mailing list system
 *
 * LICENSE:
 *
 * As long as you retain this notice you can do whatever you want with this
 * stuff. If we meet some day, and you think this stuff is worth it, you can
 * buy me a beer in return.
 *
 * @author  Mark Schmale <masch@masch.it>
 * @license Beerware
 * @package Maily
 * @version 0.1
 * @filesource
 */

/**
 * interface for transports
 *
 * @author  Mark Schmale <masch@masch.it>
 * @package Maily
 * @version 0.1
 */
interface Transport {
    public function send(MessagePart $msg);
}
?>
