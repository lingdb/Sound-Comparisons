#!/bin/bash
# chdir to location of mkLinks.sh:
dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $dir
# Inspecting source directory:
srcDir="../../../container/sndcomp/sound"
sources=$(ls $srcDir)
# Creating softlinks:
for s in $sources; do
    ln -s $srcDir/$s $s
done
