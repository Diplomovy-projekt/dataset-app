start:
    docker compose up

start-d:
    docker compose up -d

stop:
    docker compose down

build:
    sudo docker compose build

build-nocache:
    sudo docker compose build --no-cache

app-sh:
    docker compose exec -it app sh

nginx-sh:
    docker compose exec -it nginx sh

mysql-sh:
    docker compose exec -it mysql sh

test:
    php artisan test --env=testing

test-coverage:
    vendor\bin\phpunit --coverage-html tests/coverage
