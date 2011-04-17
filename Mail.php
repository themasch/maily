<?php

class Mail 
{
    protected $headers;
    protected $body;
    protected $full;
    protected $use_headers = array('To', 'From', 'Reply-To', 'Subject');
    
    public function parse($text) 
    {
        $this->full = $text;
        // normalize line breaks
        $text = str_replace("\r\n", "\n", $text);

        // seperate header from body
        list($headers, $body) = explode("\n\n", $text, 2);

        // delete linebreaks in headers
        $headers = preg_replace("(\n\s+)", ' ', $headers);

        $lines = explode("\n", $headers);
        foreach($lines as $line) {
            list($name, $value) = explode(':', $line, 2);
            $name = trim($name); $value = trim($value);
            if(isset($this->headers[$name])) {
                if(is_array($this->headers[$name])) {
                    $this->headers[$name][] = $value;
                } else {
                    $tmp = $this->headers[$name];
                    $this->headers[$name] = array($tmp, $value);
                }
            } else {
                $this->headers[$name] = $value;
            }
        }

        $this->body = $body;
    }

    public function enableHeader($name) {
        if(!in_array($name, $this->use_headers)) {
            $this->use_headers[] = $name;
        }
    }

    public function disableHeader($name) 
    {
        foreach($this->use_headers as $k => $v) {
            if($v == $name) unset ($this->use_headers[$k]);
        }
    }
    
    public function buildHeader() 
    {
        $headers = array();
        foreach($this->use_headers as $k) {
            if(!isset($this->headers[$k])) 
                continue;
            if(!is_array($this->headers[$k])) { 
                $headers[] = $k.': '.$this->headers[$k];
            } else {
                foreach($this->headers[$k] as $h) { 
                    $headers[] = $k.': '.$h;
                }
            }
        }
        $head = implode($headers, "\r\n");
        return $head;
    }

    public function getHeader($name)
    {
        return $this->headers[$name];
    }

    public function setHeader($name, $value) 
    {
        $this->headers[$name] = $value;
    }
    
    public function getHash()
    {
        return md5($this->body.$this->headers['From']);
    }
    
    public function store($basedir)
    {
        $filename = $basedir.'/'.$this->getHash().'.eml';
        file_put_contents($filename, $this->full);
    }
    
    public function load($filename, $hash = null)
    {
        // if hash is null, filename is the complete file name
        // if hash is not null, filename is just the basedir
        if($hash !== null && is_string($hash)) {
            $filename = $filename.'/'.$hash.'.eml';
        }
        $this->parse(file_get_contents($filename));
    }

    public function send($to) 
    {
        $head = $this->buildHeader();
        $subject = $this->getHeader('Subject');
        $text = $this->body;
        var_dump($to, $subject, $text, $head);
        //mail($to, $subject, $text, $head);
    }
}
