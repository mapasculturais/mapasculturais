#!/bin/bash
set -e

php -r '
$dbhost = @$_ENV["DB_HOST"] ?: "db";
$dbname = @$_ENV["DB_NAME"] ?: "mapas";
$dbuser = @$_ENV["DB_USER"] ?: "mapas";
$dbpass = @$_ENV["DB_PASS"] ?: "mapas";

$pdo = null;
echo "\naguardando o banco de dados subir corretamente...";
while(true){
    try {
        $pdo = new PDO("pgsql:host={$dbhost};port=5432;dbname={$dbname};user={$dbuser};password={$dbpass}");
        echo "\nconectado com sucesso ao banco pgsql:host={$dbhost};port=5432;dbname={$dbname};user={$dbuser};\n";
        break;
    } catch (Exception $e) {
        echo "..";
    }
    sleep(1);
}

echo "\ncorrigindo status da fila de criação de cache de permissão\n\n";

$pdo->query("UPDATE permission_cache_pending SET status = 0;");
'
mkdir -p /var/www/html/protected/DoctrineProxies
mkdir -p /var/www/SaaS
if ! cmp /var/www/version.txt /var/www/private-files/deployment-version >/dev/null 2>&1
then
    /var/www/scripts/deploy.sh
    cp /var/www/version.txt /var/www/private-files/deployment-version
else
    /var/www/scripts/db-update.sh
    /var/www/scripts/mc-db-updates.sh
fi

cd /

touch /nohup.out
sudo -E -u www-data nohup /jobs-cron.sh >> /dev/stdout &
sudo -E -u www-data nohup /recreate-pending-pcache-cron.sh >> /dev/stdout &

tail -f /nohup.out > /dev/stdout &
touch /mapas-ready

exec "$@"
