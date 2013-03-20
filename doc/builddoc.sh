#!/bin/sh

DIRs="./"

if [ $# -ne 0 ]; then
	DIRs=$@
fi

for DIR in $DIRs; do
	JSs=`find $DIR -name "*.js"`
	for JS in $JSs; do
		DOC=`echo $JS|sed 's/\(.*\)\.js/\1.java/g'`;
		if [ $JS -nt $DOC ]; then
			echo "rebuild $DOC"
			grep -e '^\s*\(///\|//\*\|/\*\*\| \* \| \*/\)' $JS | sed 's/^\s*\/\/\*\(.*\)$/\1/g'> $DOC
		fi
	done
done
