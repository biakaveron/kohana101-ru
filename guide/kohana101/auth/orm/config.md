# Настройка

## База данных {#database}

Для хранения моделей **ORM** требуются таблицы базы данных. Модуль **ORM** содержит тексты запросов для таких СУБД, как
 **MySQL** ([`auth-schema-mysql.sql`](https://github.com/kohana/orm/blob/3.2/master/auth-schema-mysql.sql)) и **PostgreSQL**
 ([`auth-schema-postgresql.sql`](https://github.com/kohana/orm/blob/3.2/master/auth-schema-postgresql.sql)). После их выполнения появятся следующие таблицы:

 * `users` - таблица для хранения профилей (логин, хеш пароля, email и тд).
 * `roles` - список зарегистрированных в системе ролей. Скрипт по умолчанию создает роли `admin` и `login`.
 * `roles_users` - промежуточная таблица для хранения привязок "пользователь" - "роль".
 * `user_tokens` - таблица с пользовательскими [токенами](auth/orm/autologin#about).