# Bulongwa Health Sciences Institute

## Get started

### Requirements

- `php >= 5.5.*`

- `composer`

### Steps

- Clone the repo

```
git clone https://github.com/GOTIFRIDI/bulongwa.git
```

- Install php dependencies

```
composer install
```

- Copy configuration files

```
cp phinx.yml.example.yml phinx.yml
cp .env.example .env
```
  and change settings accordingly

- Run database migrations

```
vendor/bin/phinx migrate
```

- Run database seeders

```
vendor/bin/phinx seed:run
```

- You are done!
