Oracle 11 and PHP-FPM Setup
---------------------------

After our initial setup, we had some issues to let PHP extensions know the ORACLE_HOME environment
variable, so we added this line to `/etc/init.d/php-fpm`.

Credit goes to:
* http://soyuka.me/install-nginx-php-fpm-with-oracle-on-debian-wheezy/
* https://help.ubuntu.com/community/Oracle%20Instant%20Client

```
# add this around line 30 after "Additional environment file"
. /etc/profile.d/oracle.sh
```

* Create `/etc/profile.d/oracle.sh` with the following contents:

```
#!/bin/bash
#Check http://soyuka.me/install-nginx-php-fpm-with-oracle-on-debian-wheezy/ for details
#This is the lib path were the instantclient is installed
ORACLE_HOME=/usr/lib/oracle/11.2/client64
#This locates C headers from your instantclient
C_INCLUDE_PATH=/usr/include/oracle/11.2/client64
#I've never had trouble with that but it might save you a couple debugging hours
LD_LIBRARY_PATH=$ORACLE_HOME/lib
#This is really important as that php-fpm does not set this variable comparing to apache that does (1)
NLS_LANG=American_America.UTF8

export ORACLE_HOME LD_LIBRARY_PATH NLS_LANG
```

* Restart `sudo service php-fpm restart` and check Oracle Database is now connecting you configured instance
