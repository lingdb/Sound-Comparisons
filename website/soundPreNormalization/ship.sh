#!/bin/bash
# This script puts all sound files from the current directory into the sound directory,
# and afterwards normalizes the volume of the soundfiles.
# It's possible that the normalization process leads to a loss in audio quality,
# especially when repeated several times.
# Therefore we've got the soundPreNormalization directory to keep originals in,
# and this ship to be run each time soundfiles are added and genscript.php was processed.
echo "Using rsync to check that we've got all soundfiles…"
rsync --include=*.ogg --include=*.mp3 --filter="-! */" -R --archive --progress --prune-empty-dirs ./ ../sound/
cd ../sound
echo "Switched directory to" `pwd`
echo "Normalizing .ogg files…"
find -type f -regex .*ogg -print0 | xargs -0 normalize-ogg -b --oggencode="oggenc -Q -b %b -o %m %w" --oggdecode="oggdec -Q -o %w %m"
echo "Normalizing .mp3 files…"
find -type f -regex .*mp3 -print0 | xargs -0 normalize-mp3 -b --mp3encode="lame --quiet -h -b %b %w %m" --mp3decode="mpg123 -q -w %w %m"
echo "List of .wav files due to failed conversion:"
find -type f -regex .*wav -print0 | xargs -0 echo
echo "Done :)"
