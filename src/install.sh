##################################################
#!/bin/bash
#
# Script for install app
#
installPath=$1
disk=$2
dbFile=$3
dbName=$4
dbPass=$5
appName=$6
userName=$7
userEmail=$8
userPassword=$9

#go to releases folder

cd $installPath

unzip $disk > /dev/null 2>&1

#rename to appName

mv $installPath/$disk $installPath/$appName

#go to app folder
cd $appName


dbUserName=$dbName
userPass="$(openssl rand -base64 12)"

mysql -uroot -p${dbPass} -e "CREATE DATABASE ${dbName}"

echo "Creating new user..."
mysql -uroot -p${dbPass} -e "CREATE USER ${dbUserName}@localhost IDENTIFIED BY '${userPass}';"
echo "User successfully created!"

echo "Granting ALL privileges on ${dbName} to ${username}!"
mysql -uroot -p${dbPass} -e "GRANT ALL PRIVILEGES ON ${dbName}.* TO '${dbUserName}'@'localhost';"
mysql -uroot -p${dbPass} -e "FLUSH PRIVILEGES;"

#Restore database
sleep 1
zcat $dbFile | mysql -u $dbUserName -p$userPass $dbName
sleep 1

#create .env
cd $appName

cat > .env << END_TEXT

APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:y3wjRkKAJ5ahMs7KTRiOBlGK2/fS3aCF028Bkb+He3s=
APP_URL=https://${dbName}.senthan.lh

PRODUCT_NAME='Senthan'

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${dbName}
DB_USERNAME=root
DB_PASSWORD=${dbPass}

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=database

RT_SERVER_PORT=6002

MAIL_DRIVER=log
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

END_TEXT

sleep 1
php artisan cache:clear
sudo chmod -R 777 storage/
sudo chmod -R 777 bootstrap/cache/
sleep 1
php artisan key:generate
sleep 1

php artisan create:auth ${userName} ${userPassword} ${userEmail}

sudo service php7.0-fpm reload

sleep 1
exit
