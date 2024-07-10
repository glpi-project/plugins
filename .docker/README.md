# Docker development environment

The docker development environment can be easilly instanciated by running the `docker compose up -d` command
from the project root directory.
The container will be name `plugins.glpi-project.org` and you can access its terminal by running the
`docker exec --interactive --tty plugins.glpi-project.org bash` command.

## Custom configuration

You can customize the docker services by creating a `docker-compose.override.yaml` file in the project root directory.

## HTTP server

By default, the Apache HTTP port is published on the `8080` port, the Node HTTP server is published on the `9000` port
and the live server on the `35729` port.
You can change them in the `docker-compose.override.yaml` file.

```yaml
services:
  app:
    ports: !override
      - "8000:80"
      - "8001:9000"
      - "8002:35729"
```

The default uid/gid used by the docker container is `1000`. If your host user uses different uid/gid, you may encounter
file permissions issues. To prevent this, you can customize them using the corresponding build args in
the `docker-compose.override.yaml` file.

```yaml
services:
  app:
    build:
      args:
        HOST_GROUP_ID: "1001"
        HOST_USER_ID: "1001"
```

### Database server

By default, the database service is not provided. You can add it in the `docker-compose.override.yaml` file.

```yaml
services:
  database:
    container_name: "db"
    image: "mariadb:11.0"
    restart: "unless-stopped"
    environment:
      MYSQL_ROOT_PASSWORD: "R00tP4ssw0rd"
      MYSQL_DATABASE: "plugins"
      MYSQL_USER: "plugins"
      MYSQL_PASSWORD: "P4ssw0rd"
    ports:
      - "3306:3306"
    volumes:
      - "db:/var/lib/mysql"

volumes:
  db:
```
