#Expense#

##Installation##

###Files###
####Method #1: virtual server####
The suggested installation method is to install everything into a virtual
server folder (e.g. /www/expense). Then you could add to your Apache
configuration somethin like:

	<VirtualHost *:80>
	 ServerAdmin me@example.com
 	 ServerName expense.example.com
	 DocumentRoot /www/expense/htdocs
	 # ... other settings
	</VirtualHost>

And into your php.ini:
	 include_path = ".:../include"

#### Method #2: home directory and symlinks####
You can keep the code where you like, e.g.
 cd /home/me
 git clone https://github.com/samuelmr/expense.git
 cd /var/www
 ln -s /home/me/expense/htdocs expense
 ln -s /home/me/expense/include expense/include

###Create databases###

	mysql -u <samp>username</samp> -p <samp>database</samp> < sql/expense2_config.sql
	mysql -u <samp>username</samp> -p <samp>database</samp> < sql/user_auth.sql

###Create configuration file and set your database credentials###

	cp include/db_config-sample.php include/db_config.php
	$EDITOR include/db_config.php

###Create reference data (averages and minimum budgets)###

	php scripts/create-references.php

You can log on to your own installation with username `demo` and password `demo`. You should also be able to create a new user account and insert your own data.

###Create cron jobs###
If you want to keep the reference data up to date or reset the demo account
every now and then, add these to your crontab (update all paths to reflect
your environment):

	# reset the demo user's data with lorem ipsum every night
	30 3 * * * /usr/bin/php /www/expense/scripts/reset-demo.php
	
	# stat.fi updates price index information 15th of every month,
	# update reference tables
	30 9 15 * * /usr/bin/php /www/expense/scripts/create-references.php
