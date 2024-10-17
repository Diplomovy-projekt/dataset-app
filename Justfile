start:
    docker compose up

start-d:
    docker compose up -d

stop:
    docker compose down

build:
    sudo docker compose build --no-cache

build-cache:
    sudo docker compose build

app-sh:
    docker compose exec -it app sh
