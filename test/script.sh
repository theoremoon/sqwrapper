#!/bin/bash

err=0
for i in `find src -name "*php"`; do
	php -l $i || err=1
done

php test/example.php || err=1

exit $err
