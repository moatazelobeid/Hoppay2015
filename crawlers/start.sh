#!/bin/sh

killall php
rm *.log
rm -rf tmp/*
nohup php markavip.php > markavip.log 2>&1 &
nohup php wysada.php > wysada.log 2>&1 &
nohup php namshi.php > namshi.log 2>&1 &
nohup php souq.php > souq.log 2>&1 &

