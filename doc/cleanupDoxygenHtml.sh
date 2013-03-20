#!/bin/bash

find /var/www/wikidata-docs.wikimedia.de/doc -name "*.html" -print | xargs sed -i 's/\.java<\/a>/\.js<\/a>/g'
find /var/www/wikidata-docs.wikimedia.de/doc -name "*.html" -print | xargs sed -i 's/\.java File Reference</\.js File Reference</g'
find /var/www/wikidata-docs.wikimedia.de/doc -name "*.html" -print | xargs sed -i 's/\/home\/ajentzsch\/wikidata\/core\///g'
