Runcity - helper for runcity.org games
========================

*Deploy

-make db runcity
-run "php app/console doctrine:schema:update --force"
- php app/console kladr:import:region  (необязательный параметр --batch=x, кол-во записей на одну транзакцию, 2000 по умолчанию)
  php app/console kladr:import:street
  php app/console kladr:import:ems (по желанию)

  rm -rf app/cache/*
  php app/console cache:warmup --env=prod

