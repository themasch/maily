<?php
/**
 *
 * @author Mark Schmale
 */
interface Logger {
    public function write($msg);
    public function close();
}