#!/bin/bash

err=0
for i in `find -name "src/*php"`; do
	php -l $i || err=1
done
exit $err
