# Test Project

Тестовый проект на Symfony с формами регистрации и обратной связи.

## Описание

Простое веб-приложение с двумя формами:
- Регистрация пользователей (имя, email, телефон, пароль)
- Форма обратной связи (email, сообщение)

Реализовано с использованием:
- Symfony 7.2
- Doctrine ORM
- Vanilla JavaScript (без фреймворков)
- Docker для локальной разработки

## Установка

```bash
# Клонировать репозиторий
git clone git@github.com:GesarDev/sibak-test.git

# Установить зависимости
composer install

# Запустить контейнеры
docker-compose up -d

# Применить миграции
php bin/console doctrine:migrations:migrate
```

## Запуск

Приложение доступно по адресу http://localhost:8000

## Тесты

```bash
# Запустить тесты
php bin/phpunit
```

## TODO

- [ ] Добавить отправку email при регистрации
- [ ] Вынести CSRF проверку в middleware
- [ ] Добавить debounce для валидации форм
