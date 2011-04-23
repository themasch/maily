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
 namespace Maily;

/**
 * a MIME message part
 *
 * @author  Mark Schmale <masch@masch.it>
 * @package Maily
 * @version 0.1
 */
class MessagePart {

    /**
     * the end of each line in the mail
     * @var string
     */
    protected $lineEnd = "\r\n";

    /**
     * headers of the mail
     * @var array
     */
    protected $header = array();

    /**
     * the mails body
     * @var array(string|MessagePart)
     */
    protected $body = array();


    /**
     * sets the headers value
     * @param string       $name  the mail headers name
     * @param string|array $value the mail headers value
     */
    public function setHeader($name, $value)
    {
        $this->header[$name] = $value;
    }

    /**
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getHeader($name, $default=null)
    {
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }

    /**
     *
     * @return array(mixed)
     */
    public function getAllHeader()
    {
        return $this->header;
    }

    /**
     * clears all headers
     * @param array $keep headers to keep
     */
    public function clearHeader(array $keep=array())
    {
        if(!is_array($keep)) {
            throw new InvalidArgumentException('expected array, got '.gettype($keep));
        }
	$backup = array();
	foreach($keep as $k) {
	    if(isset($this->header[$k])) {
		    $backup[$k] = $this->header[$k];
	    }
	}
	$this->header = $backup;
    }

    /**
     *
     * @param array|string $text
     */
    public function setBody($text)
    {
        if(!is_array($text)) {
            $text = array($text);
        }
        $this->body = $text;
    }

    /**
     *
     * @param string|MessagePart $b
     */
    public function appendBody($b)
    {
        $this->body[] = $b;
    }

    /**
     *
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * foo-bla => Foo-Bla
     * @param string $t
     * @return string
     */
    protected function capHeader($t)
    {
    	switch($t) {
	    case 'mime-version':
	        return 'MIME-Version';
	    default: 
            $parts = explode('-', $t);
            $head = '';
            foreach($parts as $p) {
                $head .= ucfirst($p).'-';
            }
            return trim($head, '-');
        }
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        $body = '';
        $le = $this->lineEnd;
        foreach($this->header as $name => $value) {
            if($name == '') continue;
            $name = $this->capHeader($name);
            if(is_array($value)) {
                foreach($value as $line) {
                    $body .= $name.': '.$line.$le;
                }
            } else {
                $body .= $name.': '.$value.$le;
            }
        }

        $body .= $le;

        if(isset($this->header['content-type']) &&
            strpos($this->header['content-type'], 'multipart/') === 0) {
            $ct = $this->header['content-type'];
            preg_match('(boundary=([^;]*))', $ct, $matches);
            $boundary = trim($matches[1], '"' );
            foreach($this->body as $part) {
                $body .= '--'.$boundary.$le;
                $body .= (string)$part;
            }
            $body .= '--'.$boundary.'--'.$le;
        } else {
            $body .= implode('', $this->body);
        }
        return $body.$le;
    }
}
