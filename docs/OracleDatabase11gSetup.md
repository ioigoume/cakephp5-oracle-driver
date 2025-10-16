Oracle 11g Database development setup (XE)
--------------

Please refer to your Oracle Database product documentation for production setup.

We did a quick development/test setup in a disposable [DigitalOcean](https://m.do.co/c/e4bdb61d7b5f) box, following the instructions provided here
 https://www.digitalocean.com/community/questions/how-can-i-install-oracle-11g?answer=15590

## Step by step setup

* Setup a new CentOS 6 image, we used a 2 GB box
* Download oracle script utility and run it

```
curl -O https://linux.oracle.com/switch/centos2ol.sh
sh centos2ol.sh
yum upgrade
yum install oracle-rdbms-server-11gR2-preinstall
mkdir /oracle
mkdir /home/OraDB11g
cd /home/OraDB11g
yum install wget
wget http://download.oracle.com/otn/linux/oracle11g/xe/oracle-xe-11.2.0-1.0.x86_64.rpm.zip
yum install unzip
unzip /root/oracle-xe-11.2.0-1.0.x86_64.rpm.zip
cd Disk1/
rpm -ivh oracle-xe-11.2.0-1.0.x86_64.rpm
```

* We had to create some swap space to let the configuration work

```
swapon -s
sudo fallocate -l 4G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
swapon -s
echo "/swapfile   swap    swap    sw  0   0" >> /etc/fstab
```

* Ensure your hostname is added to your hosts file pointing to 127.0.0.1

```
vi /etc/hosts
```

* Configure oracle and start the db server

```
/etc/init.d/oracle-xe configure
/etc/init.d/oracle-xe start
```

* Install some extra packages to use sqlplus command line. Download files from here http://www.oracle.com/technetwork/topics/linuxx86-64soft-092277.html

```
rpm -ivh oracle-instantclient11.2-basic-11.2.0.1.0-1.x86_64.rpm
rpm -ivh oracle-instantclient11.2-sqlplus-11.2.0.1.0-1.x86_64.rpm
```

* Export ORACLE_HOME environment path

```
export ORACLE_HOME=/u01/app/oracle/product/11.2.0/xe
```

* Console client access

```
$ORACLE_HOME/bin/sqlplus username/password@XE
```


