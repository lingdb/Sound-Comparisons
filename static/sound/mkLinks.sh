#!/bin/bash
srcDir="../../../docker.sndcomp/sound/"
sources=$(ls $srcDir)
for s in $sources
do
ln -s $srcDir/$s $s
done
