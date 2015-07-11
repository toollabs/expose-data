#! /bin/bash

export LANG=en_US.UTF-8
export PATH=/usr/local/bin:/usr/bin:/bin:$PATH
mysql --defaults-file=~/replica.my.cnf -h commonswiki.labsdb commonswiki_p < ~/autopatrolled_candidates.sql > ~/www/static/autopatrolled_candidates.tsv
