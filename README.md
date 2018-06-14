
docker run --rm --interactive --tty \
    --volume $PWD:/app \
    --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
    --env SSH_AUTH_SOCK=/ssh-auth.sock \
    composer update


docker-compose exec php php /var/www/symfony/bin/console cache:clear # Symfony3


# Delete all containers
$ docker rm $(docker ps -aq)

# Delete all images
$ docker rmi $(docker images -q)


# F***ing cache/logs folder
$ sudo chmod -R 777 app/cache app/logs # Symfony2
$ sudo chmod -R 777 var/cache var/logs var/sessions # Symfony3




Creating database exports

mongoexport --port 27020 --db test --collection mycollection --out $(pwd)/data/db/dumps/mycollection.json
Creating database dumps

mongodump --port 27020 --db test --collection mycollection --out $(pwd)/data/db/dumps



db.tracking.createIndex({'track_no':1}, {"unique":true})
Cleaning project

Warning: Clears all containers and volumes.

$ ./bin/linux/clean.sh $(pwd)

//        "optimize-autoloader": true,
//        "classmap-authoritative": true


https://github.com/avtobiz/carprice.git

Example of docker x-debug
https://github.com/ImprontaAdvance/docker-compose-symfony#xdebug-on-mac

docker-compose exec php /app/bin/console create-job
