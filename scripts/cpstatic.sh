#!/bin/sh
for i in ./htdocs.*; do
  cp ./src/*.css $i;
  cp -r ./src/images $i;
  cp -r ./src/sounds $i;
done
find ./htdocs.* -name "CVS" -exec rm -r {} \; > /dev/null 2>&1
exit 0
