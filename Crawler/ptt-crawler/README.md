# README #

### What is this repository for? ###

* crawl data from ptt web
* save data into mysql DB

### How do I get start? ###

* install [composer](https://getcomposer.org/)
* `make init`
* `make`
* `./crawler --help`


### Sample Cronjob ###

```
0 0 * * * /home/copyleft/prj-ptt-crawler/crawler --board=politics --sleep-between-retry=10 --start-date=$(date +\%Y-\%m-\%d --date='-3 day') --storage=rdb --db-username=root --db-password=nccu --timeout=10 --sleep-between-article=1 --stop-date=2014-01-01 --stop-on-duplicate=true > /tmp/politics.log 2>&1

30 0 * * * /home/copyleft/prj-ptt-crawler/crawler --board=Kaohsiung --sleep-between-retry=10 --start-date=$(date +\%Y-\%m-\%d --date='-3 day') --storage=rdb --db-username=root --db-password=nccu --timeout=10 --sleep-between-article=1 --stop-date=2014-01-01 --stop-on-duplicate=true > /tmp/Kaohsiung.log 2>&1

0 2 * * * /home/copyleft/prj-ptt-crawler/crawler --board=HatePolitics --sleep-between-retry=10 --start-date=$(date +\%Y-\%m-\%d --date='-3 day') --storage=rdb --db-username=root --db-password=nccu --timeout=10 --sleep-between-article=1 --stop-date=2014-01-01 --stop-on-duplicate=true > /tmp/HatePolitics.log 2>&1

0 5 * * * /home/copyleft/prj-ptt-crawler/crawler --board=Gossiping --sleep-between-retry=10 --start-date=$(date +\%Y-\%m-\%d --date='-3 day') --storage=rdb --db-username=root --db-password=nccu --timeout=10 --sleep-between-article=1 --stop-date=2014-01-01 --stop-on-duplicate=true > /tmp/Gossiping.log 2>&1
```


### LICENSE

Copyright NCCU FloodFire Project
