# Configure cycle bundle

Create the configuration file `cycle.yaml` in directory `config/packages/`

```yaml
cycle:
  dbal:
    databases:
      default:
        connection: default_connection

    connections:
      default_connection:
        driver: mysql
        host: '%env(resolve:DB_HOST)%'
        port: '%env(int:DB_PORT)%'
        dbname: '%env(resolve:DB_NAME)%'
        user: '%env(resolve:DB_USER)%'
        password: '%env(resolve:DB_PASSWORD)%'
        charset: utf8

  orm:
    schema:
      type: attribute
      dir: "%kernel.project_dir%/src/Entity"
    cache_dir: "%kernel.cache_dir%/cycle"
    relation:
      fk_create: false
      index_create: false
  migration: ~

when@test:
  dbal:
  databases:
    default:
      prefix: '_test%env(default::TEST_TOKEN)%'
```