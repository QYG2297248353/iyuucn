<?php

while (true) {
    print_r([
        bin2hex(pack('d', microtime(true))),
        uniqid(mt_rand(100000, 999999)),
        bin2hex(pack('d', microtime(true)) . random_bytes(8))
    ]);
    sleep(1);
}
