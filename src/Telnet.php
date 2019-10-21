<?php

namespace Meklis\TelnetOverProxy;

use meklis\network\Telnet as TelnetClient;

class Telnet extends TelnetClient
{
    function __construct($host = '127.0.0.1', $port = 23, $timeout = 30, $stream_timeout = 10.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->setStreamTimeout($stream_timeout);

        // set some telnet special characters
        $this->NULL = chr(0);
        $this->DC1 = chr(17);
        $this->WILL = chr(251);
        $this->WONT = chr(252);
        $this->DO = chr(253);
        $this->DONT = chr(254);
        $this->IAC = chr(255);

        // open global buffer stream
        $this->global_buffer = new \SplFileObject('php://temp', 'r+b');
    }

    public function connectOverProxy($proxy_addr = "tcp://127.0.0.1:3333", $timeout = 60) {
        $exec = function ($command) {
            $this->write($command, true);
            $this->waitPrompt();
            return $this->getBuffer();
        };

        $ip = "127.0.0.1";
        $port = 3333;
        if(!preg_match('/^(tcp|udp):\/\/(.*?):(.*)$/', $proxy_addr, $matches)) {
            throw new \Exception("Incorrect proxy address for connect");
        }

        // attempt connection - suppress warnings
        $this->socket = @fsockopen($ip, $port, $this->errno, $this->errstr, $timeout);

        $this->setLinuxEOL();

        if (!$this->socket) {
            throw new \Exception("Cannot connect to proxy $ip on port $port");
        }

        // check if we need to convert host to IP
        if (!preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', $this->host)) {
            $ip = gethostbyname($this->host);

            if ($this->host == $ip) {
                throw new \Exception("Cannot resolve $this->host");
            } else {
                $this->host = $ip;
            }
        }

        $this->setPrompt(">>>");
        if (!empty($this->prompt)) {
            $this->waitPrompt();
        }
        try {
            $resp = $exec("CONN {$this->host} {$this->port}");
        } catch (\Exception $e) {
            throw new \Exception("Error write connection command to proxy", 1, $e);
        }

        if($resp) {
            list($status, $message) = @explode("###", $resp);
            if(!$status) {
                throw new \Exception("Not correct status response from proxy");
            }
            if($status == "CONNECTION_CLOSED") {
                throw new \Exception("Error connecting to remote device: {$message}");
            }
            if($status == "CONNECTION_LIMIT") {
                throw new \Exception("Connection limit - device is busy. Try connect later.");
            }
            if($status == "CONNECTED") {
                return $this;
            }
        } else {
            throw new \Exception("Incorrect response from proxy");
        }
        $this->setPrompt("");
        return $this;
    }
}