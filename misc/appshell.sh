#!/bin/bash

cd "$( dirname "${BASH_SOURCE[0]}" )"
cd ..
php -a -d auto_prepend_file=misc/appshell.php