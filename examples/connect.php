<?php

require __DIR__ . "/../vendor/autoload.php";


$telnet = new Meklis\TelnetOverProxy\Telnet("10.0.0.1", "23", 30);

$telnet->connectOverProxy("tcp://127.0.0.1:3333");

$telnet->disableMagicControl()->login("login", "pass", "dlink");
print_r($telnet->exec("disa clip"));
print_r($telnet->exec("show  switch"));