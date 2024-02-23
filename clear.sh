#!/bin/bash
pwd_dir=$(cd $(dirname $0); pwd)
echo $pwd_dir
cd $(dirname $0)
cat /dev/null > ./runtime/logs/gateway_worker.log
cat /dev/null > ./runtime/logs/gateway_worker_stdout.log
cat /dev/null > ./runtime/logs/stdout.log
cat /dev/null > ./runtime/logs/workerman.log
