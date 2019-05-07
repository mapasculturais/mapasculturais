#!/bin/bash
set -e

php -r '
$dbhost = @$_ENV["DB_HOST"] ?: "db";
$dbname = @$_ENV["DB_NAME"] ?: "mapas";
$dbuser = @$_ENV["DB_USER"] ?: "mapas";
$dbpass = @$_ENV["DB_PASS"] ?: "mapas";

echo "\naguardando o banco de dados subir corretamente...";

while(true){
    try {
        new PDO("pgsql:host={$dbhost};port=5432;dbname={$dbname};user={$dbuser};password={$dbpass}");
        echo "\nconectado com sucesso ao banco pgsql:host={$dbhost};port=5432;dbname={$dbname};user={$dbuser};\n";
        break;
    } catch (Exception $e) {
        echo "..";        
    }
    sleep(1);
}
'

if [ ! -f /.deployed ]; then
    cd /var/www/scripts
    ./deploy.sh
    touch /.deployed
fi

chown -R www-data:www-data /var/www/html/assets /var/www/html/files /var/www/private-files

nohup /recreate-pending-pcache-cron.sh &

exec "$@"
