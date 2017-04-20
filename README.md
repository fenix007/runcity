Runcity - helper for runcity.org games
========================

# set hostname = runcity.local (due to yandex api key work)

-make db runcity
```bash
php composer.phar install -o
php app/console doctrine:schema:update --force
```
Быстрый старт для Москвы
```bash
mysql -u<user> -p<pass> <dbname> < MoscowStreet.sql
```
Обновление данных
```bash
mysql -u<user> -p<pass> <dbname> < KladrRegion.sql
mysql -u<user> -p<pass> <dbname> < KladrStreet.sql
php app/console kladr:import:moscow_street
php app/console kladr:update:moscow_street
```
