#!/bin/bash

type='css'
compressor="java -jar $HOME/dev/extern/yuicompressor-2.4.8.jar --type $type"
target="min.$type"
rm -f $target
for i in 'main' 'myflow' 'style'
do
echo "Compressing $i.$type…"
$compressor $i.$type >> $target
done
