В ветке main находятся все **основные** задания (и, возможно, 6 доп задание, смотреть Вопросы, с которыми столкнулась)

В ветке feature/Partitioning находятся **1 и 2 доп задания** (подробнее - в readmi этой ветки)

В ветке feature/add-queue находится **задание 4** (удаление баннеров в очереди по фиче)

----------------

**Запуск проекта:**

**1 шаг** - 
```
docker compose up --build
```

**2 шаг** - перейти в контейнер с апи 
```
docker exec -it avito_api /bin/bash
```
и выполнить команду 
```
composer install
```

**3 шаг** - залить фикстуры в этом же контейнере:
```
php bin/console doctrine:fixtures:load
```

**4 шаг** - получить токены для запросов можно в запросе /login (смотреть прикрепленную постман-коллекцию). Данные брать из фикстур, из файла ./backend-trainee-assignment-2024/api/src/DataFixtures/UserFixture.php

---------------

Тут скачать постман-коллекцию с запросами и импортировать ее в Postman:

https://drive.google.com/file/d/15-xvaIk8GuWBxQsUYcGuEJElPIzp4dcP/view?usp=drive_link

-------------------

**Тесты:**

- Запустить 1 шаг из описания выше
- Перейти в контейнер с апи:
```
docker exec -it avito_api /bin/bash
```

Выполнить:
```
APP_ENV=test bin/console doctrine:database:create
```

```
APP_ENV=test bin/console doctrine:migrations:migrate
```

```
APP_ENV=test bin/console doctrine:fixtures:load
```

- Запустить команду
```
php bin/phpunit
```

-------

**Вопросы, с которыми столкнулась**:

- У запроса GET /user_banner нет ошибки 403 - так как в условии нет других токенов, кроме токена пользователя и токена админа и для этого запроса они оба подходят
- У запроса DELETE /banner/{id} нет ошибки 400 - так как по сути нет некорректных данных
- Доп задача 6 - "Описать конфигурацию линтера". Использую пакеты friendsofphp/php-cs-fixer и squizlabs/php_codesniffer. Возможно, это считается за выполненное задание
