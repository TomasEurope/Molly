<?php

$ip = '176.102.65.65';

$r = gethostbyaddr($ip);

$x = gethostbynamel($r);

print_r($r);

print_r($x);
