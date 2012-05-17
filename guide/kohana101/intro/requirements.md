# Требования фреймворка

## Обязательные требования {#system}

Существуют минимальные требования, необходимые для работы фреймворка. Вот самые существенные из них:

 - версия PHP должна быть не ниже 5.2.3
 - директории для ведения [логов](basic/logs) и хранения [кеша](basic/cache) должны быть доступны для записи
 - должны быть подключены расширения PHP:
   - [filter](http://ru2.php.net/filter)
   - [iconv](http://ru2.php.net/iconv)
   - [mbstring](http://ru2.php.net/mbstring)

## Опциональные требования {#optional}

Также для выполнения отдельных задач могут потребоваться:

 - [PECL HTTP](http://ru2.php.net/http)
 - [GD](http://ru2.php.net/gd)
 - [MySQL](http://ru2.php.net/mysql),[PDO](http://ru2.php.net/pdo)
 - [mcrypt](http://ru2.php.net/mcrypt)
