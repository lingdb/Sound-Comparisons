#!/bin/sh
find -type f -regex .*php -exec php -l {} \;
