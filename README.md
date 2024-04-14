Здесь находятся 1 и 2 доп задания

Решение:
Изначально уже был сделан шаг в оптимизации - введен составной уникальный индекс BANNER_IDX в таблице search_banner для полей tag_id и feature_id.

Но можно пойти дальше и добавить партицирование.

В данной ветке изменен файл фикстур и миграций, потому будет лучше сделать так:

**Запуск проекта: - в ветке main**

**1 шаг** - 
```
docker compose up --build
```

**2 шаг** - перейти в контейнер с апи 
```
docker exec -it avito_api /bin/bash
```
и сделать 
```
bin/console doctrine:migrations:migrate prev
```

**3 шаг** - стопнуть проект (**docker ps** и стопнуть контейнеры по id (**docker stop <container_id>**), или, если запускаете без -d команду, то в окне первого шага сделать  Секд + С) и перейти на ветку feature/Partitioning (**git checkout feature/Partitioning**)

**4 шаг** - снова поднять проект 
```
docker compose up --build
```
**5 шаг** - залить фикстуры
```
php bin/console doctrine:fixtures:load

```


------

партицирование сделано в миграции ./backend-trainee-assignment-2024/api/migrations/Version20240411220131.php по полю feature_id. Последняя партиция выделяется огромным количеством - но это скорее для того, чтобы показать принцип


-------------

Нагрузочное тестирование:

К сожалению, не вышло уменьшения времени. Но я думаю, что это потому, что в таблице слишком мало данных и на большем количестве будет работать быстрее

Было:

![bad](https://github.com/korovenko-tatyana/backend-trainee-assignment-2024/assets/17434916/600840a1-de82-499b-9e14-9957f1d7d2a9)


Стало:

![good](https://github.com/korovenko-tatyana/backend-trainee-assignment-2024/assets/17434916/193db6bd-8ddd-4057-bc60-eb904b5f8a51)
