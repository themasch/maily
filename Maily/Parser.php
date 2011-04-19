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

require_once __DIR__.'/MessagePart.php';

/**
 * a complete e-mail parser
 *
 * @author  Mark Schmale <masch@masch.it>
 * @package Maily
 * @version 0.1
 */
class Parser {

    /**
     * the mail
     * @var String
     */
    protected $content = '';


    /**
     * appends data to the mail
     * @param string $content
     */
    public function appendContent($content)
    {
        $this->content .= $content;
    }

    /**
     * sets the email content
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * reads the mail from a file
     * @param string $path
     */
    public function readContent($path)
    {
        $this->content = file_get_contents($path);
    }

    /**
     * parses the mail
     *
     * you need to append the content to the parser before u can parse
     * @return MessagePart
     */
    public function parse()
    {
        $text = $this->content;

       // normalize line breaks
        $text = str_replace("\r\n", "\n", $text);

        return $this->parsePart($text);
    }


    /**
     * parse a part of the message
     *
     * @param  string Message
     * @return MessagePart
     */
    protected function parsePart($text) {
        $mp    = new MessagePart();
        $heada = array();
        // seperate header from body
        list($headers, $body) = explode("\n\n", $text, 2);
        // delete linebreaks in headers
        $headers = preg_replace("(\n\s+)", ' ', $headers);

        // parse headers
        $lines = explode("\n", $headers);
        foreach($lines as $line) {
            $tmp = explode(':', $line, 2);
            if(count($tmp) < 2) { 
                continue;
            }
            list($name, $value) = $tmp;
            $name = strtolower(trim($name));
            $value = trim($value);

            if(isset($heada[$name])) {
                if(is_array($heada[$name])) {
                    $heada[$name][] = $value;
                } else {
                    $tmp = $heada[$name];
                    $heada[$name] = array($tmp, $value);
                }
            } else {
                $heada[$name] = $value;
            }
        }
        // add headers
        foreach($heada as $k => $v) {
            $mp->setHeader($k, $v);
        }

        // parse body

        if(isset($heada['content-type']) &&
            strpos($heada['content-type'], 'multipart/') === 0) {
            $ct = $heada['content-type'];
            preg_match('(boundary=(.*))', $ct, $matches);
            $boundary = trim($matches[1], '"' );

            $parts = explode('--'.$boundary, $body);
            $content = array_shift($parts);

            foreach($parts as $p) {
                if(trim($p) == '--') continue;
                $part = $this->parsePart($p);
                $mp->appendBody($part);
            }

        } else {
            $mp->setBody($body);
        }

        return $mp;
    }


}
