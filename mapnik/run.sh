#!/bin/bash
if [ ! -d "mapbox-studio-osm-bright.tm2" ]; then
  git clone https://github.com/mapbox/mapbox-studio-osm-bright.tm2.git
fi
if [ ! -f "planet.mbtiles" ]; then
  #wget -c "https://osm2vectortiles-downloads.os.zhdk.cloud.switch.ch/v2.0/planet_z0-z8.mbtiles"
  wget -c "https://osm2vectortiles-downloads.os.zhdk.cloud.switch.ch/v2.0/planet.mbtiles"
fi
docker run -v $(pwd):/data  \
           -l lingdb=mapnik \
           -p 127.0.0.1:27374:80 \
           --rm klokantech/tileserver-mapnik
