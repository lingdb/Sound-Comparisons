#!/bin/bash
# Using rsync to mirror the servers sound files:
rsync -avz --progress --delete -e ssh lingdb:/srv/soundcomparisons/site/sound/ site/sound/
