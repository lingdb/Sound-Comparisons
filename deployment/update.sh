#!/bin/bash
cd /srv/soundcomparisons
git pull
cd site/js
npm install
nodejs ./node_modules/bower/bin/bower install
nodejs ./node_modules/grunt-cli/bin/grunt
