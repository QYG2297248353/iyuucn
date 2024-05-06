#!/bin/bash
pwd_dir=$(cd $(dirname $0); pwd)
echo $pwd_dir
cd $(dirname $0)
php start.php restart -d
