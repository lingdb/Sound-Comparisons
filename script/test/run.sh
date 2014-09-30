#!/bin/bash
rm log
for i in `seq 1 100`; do
curl "http://127.0.0.1/shk/main/?study=Mapudungun&languages=Huilliche%20%28Lagos%29&pageView=languageView&hl=en&wo_order=alphabetical&wo_phLang=Ancestral%20Mapudungun" | grep "Page generated in" >> log
done
php -f run.php
