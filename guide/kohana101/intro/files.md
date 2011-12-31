# Основные элементы

## Типовая структура каталогов

Если открыть директорию `application`, там будет примерно такая структура каталогов:

![Структура каталогов](intro/filetree.png)

Различают несколько типов файлов в Kohana:

* [контроллер](intro/controller). Класс, хранящийся в директории `classes/controller`.
* [модель](intro/model). Файлы этого класса лежат в `classes/model`.
* все прочие классы обычно тоже размещают в папке `classes`. Можно использовать поддиректории, например `classes/foo/bar.php`.
* [файлы конфигурации](intro/config), директория `config`. В них хранятся настройки проекта в целом или его отдельных компонентов.
* [файлы перевода](basic/i18n). Под них выделено две директории, `i18n` и `messages`.
* [представления](intro/views). Это шаблоны для вывода результатов работы приложения на экран. Обычно содержат комбинацию HTML и PHP.
* [вендорные классы](basic/3rdparty). Это так называемые *3rd-party libraries*, т.е. чужие библиотеки, не адаптированные под Kohana. Например,
 это могут быть классы `Zend Framework`. Такие библиотеки принято складывать в директорию `vendor`.

Директории `cache` и `logs` служебные, по названию очевидны их функции.

[!!] Обратите внимание, что имена директорий и файлов указаны в нижнем регистре. Это необходимо для нормальной работы [автозагрузчика](intro/autoload).

Такие же директории имеются и в папке `system`, ее должны соблюдать подключаемые модули Kohana.

## Файл index.php

Данный файл заслуживает отдельного внимания, т.к. с него начинается вся работа приложения. Основная его задача - определить
 расположение трех главных директорий фреймворка, `system`, `application` и `modules`.

    /**
     * The directory in which your application specific resources are located.
     * The application directory must contain the bootstrap.php file.
     *
     * @see  http://kohanaframework.org/guide/about.install#application
     */
    $application = 'application';

    /**
     * The directory in which your modules are located.
     *
     * @see  http://kohanaframework.org/guide/about.install#modules
     */
    $modules = 'modules';

    /**
     * The directory in which the Kohana resources are located. The system
     * directory must contain the classes/kohana.php file.
     *
     * @see  http://kohanaframework.org/guide/about.install#system
     */
    $system = 'system';

Пути к этим директориям могут быть абсолютные или относительные (считаем от `index.php`, обычно это и есть `DOCUMENT_ROOT` сервера).

[!!] Совсем не обязательно называть директории стандартно, главное - не забыть указать правильные имена в `index.php`.

В дальнейшем пути сохраняются в виде констант:

    // Define the absolute paths for configured directories
    define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
    define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
    define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

## application/bootstrap.php

Этот файл предназначен для настройки и управления выполнением приложения. Например, в дефолтной поставке файл делает следующее:

* устанавливает временнУю зону (timezone), локаль (locale) и язык для класса [I18n](basic/i18n).
* регистрирует стандартный [автозагрузчик](intro/autoloader) Kohana.
* подключает объекты для ведения [логов](intro/logs) и записи файлового [кеша](intro/cache).
* подключает [модули](intro/modules) (точнее, предоставляет возможность их подключить, т.к. изначально модули закомментированы).
* устанавливает [маршрут](basic/routing) по умолчанию.

Вы можете прописывать в этом файле все, что хотите. Например, можно вообще не подключать ни одиного модуля. Или заменить
 штатный автозагрузчик своей версией.

[!!] Вообще, все содержимое директории `application` в вашем распоряжении. Там можно модифицировать все, что угодно.