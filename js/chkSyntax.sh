#!/usr/bin/sh
find -type f -regex .*js | grep -v extern | xargs jshint
