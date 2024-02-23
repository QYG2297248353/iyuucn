#!/bin/bash
pwd_dir=$(cd $(dirname $0); pwd)
echo $pwd_dir
cd $(dirname $0)
php82 start.php restart -d
#php82 gateway_worker.php restart -d
