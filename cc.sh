php app/console cache:clear --env=dev
php app/console cache:clear --env=prod

sudo chown -R alex:alex .

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo /usr/bin/setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs app/sessions web/uploads web/media
sudo /usr/bin/setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs app/sessions web/uploads web/media

