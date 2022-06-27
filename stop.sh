#!/bin/bash
basedir=$(dirname $0)
kill $(cat $basedir/pid)
rm $basedir/pid
