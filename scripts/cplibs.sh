#!/bin/bash
if [ ! -e ./lib/jsjac ]; then
  echo "jsjac is missing, checking out ..."
  git clone git://github.com/sstrigler/JSJaC.git lib/jsjac
  echo "done"
fi

echo "building jsjac ..."
make -C lib/jsjac nodoc
echo "done"

for i in ./lib/*; do for j in $i/*.{js,html}; do if [ -e "$j" ]; then cp "$j" ./htdocs; for k in ./htdocs.*; do cp "$j" "$k"; done ; fi; done; done;
