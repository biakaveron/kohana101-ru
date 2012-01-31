## Драйверы для работы с конфигурацией

В ядре **Kohana** изначально доступен только драйвер `Kohana_Config_File`, он работает со стандартными файловыми конфигами
 и _только на чтение_. Если подключить модуль [Database](intro/database), то можно будет подключить драйвер `Kohana_Config_Database`.
 Он хранит настройки в базе данных, и имеет возможность сохранять изменения:

 // запрашиваем конфиг
 $group = Kohana::$config->load('foo');
 // изменяем его
 $group->set('bar', 'baz');