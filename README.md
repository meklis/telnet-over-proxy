# Telnet over proxy client
###Help provider library

PHP library for working with module https://github.com/meklis/telnet-proxy
Library extends from meklis/telnet-client.

Install 
``` 
composer require meklis/telnet-over-proxy
```


Using 
``` 
<?php

$telnet = new Meklis\TelnetOverProxy\Telnet("10.0.0.1", "23", 30);
$telnet->connectOverProxy("tcp://127.0.0.1:3333");

... 

```
