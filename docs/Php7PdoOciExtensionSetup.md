PHP 7 PDO_OCI Extension setup
--------------------------

We've compiled the extension using the PHP 7 sources.

* First, upgrade to PHP 7
```
cat /etc/redhat-release
yum groupinstall "Development Tools"
rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
yum install php70w php70w-pdo php70w-mcrypt php70w-mbstring php70w-intl php70w-devel php70w-bcmath php70w-cli php70w-odbc php70w-xml php70w-pear php70w-xdebug
php -v
```

* Install instaclient for Oracle Database 11g. Download files from here http://www.oracle.com/technetwork/topics/linuxx86-64soft-092277.html

```
rpm -Uvh oracle-instantclient11.2-devel-11.2.0.1.0-1.x86_64.rpm
```

* Download the PHP sources, matching the current PHP version installed `php -v`

```
cd /root
mkdir src
cd src
wget http://be.php.net/distributions/php-7.0.2.tar.gz
tar xfvz php-7.0.2.tar.gz
```

* Compile and install the extension

```
cd php-7.0.2
cd ext
cd pdo_oci
phpize
./configure --with-pdo-oci=instantclient,/usr,11.2
make
make install
echo extension=pdo_oci.so > /etc/php.d/pdo_oci.ini
php -i |grep oci
```

* Check the PDO_OCI extension is installed, and restart your PHP engine to apply changes

```
php -i |grep oci
```
