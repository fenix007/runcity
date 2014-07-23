php app/console cache:clear --env=dev
php app/console cache:clear --env=prod
chown -R www-data app/cache
chown -R www-data app/logs
