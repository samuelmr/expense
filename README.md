Expense
=======

Expense tracking

Installation
------------

Create databases

 mysql -u <samp>username</samp> -p <samp>database</samp> < sql/expense2_config.sql
 mysql -u <samp>username</samp> -p <samp>database</samp> < sql/user_auth.sql

Create configuration file and set your database credentials

 cp include/db_config-sample.php include/db_config.php
 $EDITOR include/db_config.php

Create reference data (averages and minimum budgets)

 php /www/seuranta/kulutus/scripts/create-references.php

You can log on to your own installation with username `demo` and password `demo`. You should also be able to create a new user account and insert your own data.

If you want to keep the reference data up to date or reset the demo account
every now and then, add these to your crontab:

 # reset the demo user's data with lorem ipsum every night
 30 3 * * * /usr/bin/php /www/seuranta/kulutus/scripts/reset-demo.php
 
 # stat.fi updates price index information 15th of every month,
 # update reference tables
 30 9 15 * * /usr/bin/php /www/seuranta/kulutus/scripts/create-references.php
