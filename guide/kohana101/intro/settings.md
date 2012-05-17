# Базовая настройка

В **Kohana** предусмотрен ряд различных настроек для управления работой приложения. Основные из них мы рассмотрим ниже.

## Файл index.php {#index}

Данный файл запускается самым первым, и в нем расположены самые первые настройки.

### Расположение директорий проекта {#directories}

Для начала, определяем, где расположены директории `application`, `modules` и `system`.

    $application = 'application';
    $modules = 'modules';
    $system = 'system';

Путь может быть абсолютным или относительным. Определив их, в дальнейшем мы сможем использовать пути в виде констант
 `APPPATH`, `MODPATH` и `SYSPATH` соответственно.

    define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
    define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
    define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

### Расширение файлов проекта {#extension}

Еще одна константа в `index.php` устанавливает расширение для файлов **Kohana**:

     define('EXT', '.php');

В первую очередь эта константа используется при поиске файлов в [Каскадной файловой системе](intro/cascadefs)
 (метод [Kohana::find_file], параметр `$ext`).

### Константы для профилирования {#profiling}

Поскольку это самое начало работы приложения, имеет смысл запомнить текущие значения для времени (timestamp) и расходуемой
 памяти (memory usage). Что и делается, результаты сохраняются в константы `KOHANA_START_TIME` и `KOHANA_START_MEMORY`.

    /**
     * Define the start time of the application, used for profiling.
     */
    if ( ! defined('KOHANA_START_TIME'))
    {
        define('KOHANA_START_TIME', microtime(TRUE));
    }

    /**
     * Define the memory usage at the start of the application, used for profiling.
     */
    if ( ! defined('KOHANA_START_MEMORY'))
    {
        define('KOHANA_START_MEMORY', memory_get_usage());
    }

Естественно, эти константы можно использовать и в своих целях.

### Загрузка пользовательских настроек для приложения {#load-bootstrap}

Следующим этапом в работе приложения является выполнение директив файла `APPPATH/bootstrap.php`. О нем будет рассказано отдельно.

    require APPPATH.'bootstrap'.EXT;

### Запуск собственно проекта {#execution}

Как только все первоначальные настройки выполнены, необходимо сделать главное - запустить проект. Для этого по умолчанию
 служит вот такая конструкция:

     echo Request::factory()
        ->execute()
        ->send_headers()
        ->body();

Здесь создается и выполняется начальный [запрос](basic/request), а также возвращаются результаты его работы - HTTP-заголовки
 и [ответ](basic/response).

### Что здесь можно менять? {#change-index}

Обычно в данном файле устанавливают только свои пути к директориям `application`, `modules` и `system` (если они отличаются).
 Иногда блок `echo Request::factory()...` оборачивают в `try ... catch` для [отлова ошибок](basic/errors), но это не совсем правильно.

Данный файл скорее системный, чем пользовательский, поэтому сюда не стоит вписывать свои дополнительные команды - используйте
 для этого файл `APPPATH/bootstrap.php`.

## Файл APPPATH/bootstrap.php {#bootstrap}

Этот файл - "склад" пользовательских настроек. Большинство общесистемных настроек/вызовов должны располагаться здесь.

### Подключение ядра фреймворка {load-core}

На самом деле до собственно **Kohana** дело еще не доходило. Работа фреймворка начинается с главного - с подключения ядра в виде
 класса `Kohana`:

    // Load the core Kohana class
    require SYSPATH.'classes/kohana/core'.EXT;

    if (is_file(APPPATH.'classes/kohana'.EXT))
    {
        // Application extends the core
        require APPPATH.'classes/kohana'.EXT;
    }
    else
    {
        // Load empty core extension
        require SYSPATH.'classes/kohana'.EXT;
    }

Точнее, не только класса `Kohana`, но и `Kohana_Core`. Причем `Kohana_Core` обязательно должен быть расположен в папке
 `system`. Именно в нем расположены методы, необходимые для дальнейшей работы приложения. Загрузка файла `kohana.php`
 сделана таким образом, что позволяет нам использовать свой вариант класса `Kohana`.

### Установка часового пояса и локали {#locale}

Для них используются стандартные функции PHP [date_default_timezone_set](http://ru.php.net/manual/ru/function.date-default-timezone-set.php)
 и [setlocale](http://ru.php.net/manual/en/function.setlocale.php):

    date_default_timezone_set('America/Chicago');
    setlocale(LC_ALL, 'en_US.utf-8');

Расположив их в данном файле, разработчики ненавязчиво напоминают о необходимости настройки данных параметров приложения.
 Для России типичными будут следующие значения:

    date_default_timezone_set('Europe/Moscow');
    setlocale(LC_ALL, 'ru_RU.utf-8');

### Регистрация автозагрузчика {#autoload}

**Kohana** вовсю использует преимущества PHP5, в частности [автозагрузку классов](http://php.net/manual/ru/language.oop5.autoload.php).
 Для этого надо зарегистрировать штатный автозагрузчик:

     spl_autoload_register(array('Kohana', 'auto_load'));

[!!] Автозагрузчик действует только для классов, расположенных в директориях `classes`.

Так как [spl_autoload_register](http://www.php.net/manual/ru/function.spl-autoload-register.php) поддерживает возможность
 указать несколько автозагрузчиков, вы можете указать и собственные функции для автозагрузки:

     // штатный
     spl_autoload_register(array('Kohana', 'auto_load'));
     // свой автолоадер
     spl_autoload_register(array('CMS', 'auto_load'));

[!!] Внимательно следите, чтобы в `bootstrap.php` располагался выше, чем обращение к какому-либо классу фреймворка (кроме `Kohana` разумеется).

Еще одна связанная с автозагрузкой команда:

     ini_set('unserialize_callback_func', 'spl_autoload_call');

Данная команда указывает, что при десериализации (т.е. распаковке) объекта для неизвестных системе классов будет использоваться
 все тот же штатный загрузчик [Kohana::auto_load]. Плюс другие автолоадеры, если они были определены.

### Настройка текущего языка приложения (I18n) {#i18n}

Для локализации приложений **Kohana** создан специальный класс [I18n](basic/i18n/i18n). Чтобы он корректно работал, необходимо
 установить язык по умолчанию (т.е. базовый язык).

    I18n::lang('en-us');

Более подробно о способах перевода описано в разделе [Интернационализация](basic/i18n), сейчас нам достаточно помнить, что
 для России обычно указывается один из следующих вариантов:

    I18n::lang('ru-ru');
    I18n::lang('ru');

[!!] Никто не мешает устанавливать это значение динамически, например на основании заголовка Accept-Language или IP-адреса.

### Проверка степени готовности приложения {#environment}

В **Kohana** готовность приложения может быть характеризована несколькими уровнями, которые представлены в виде констант:

 * PRODUCTION
 * STAGING
 * TESTING
 * DEVELOPMENT

Текущий уровень хранится в переменной `Kohana::$environment` (по умолчанию `Kohana::DEVELOPMENT`, т.е. "в разработке").
 Разработчики предлагают анализировать возможное значение переменной `$_SERVER['KOHANA_ENV']`:

    if (isset($_SERVER['KOHANA_ENV']))
    {
        Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
    }

Это достаточно удобно, т.к. значение `$_SERVER['KOHANA_ENV']` можно выставить извне PHP-проекта, например в файлах веб-сервера
 (директива `SetEnv` для Apache). От себя можно добавить проверку `$_SERVER['REMOTE_ADDR']` или `Request::$client_ip`.

 [!!] Можно создавать свои уровни. Добавляем файл `APPPATH/classes/kohana.php`, и в нем класс `Kohana extends Kohana_Core`.
  Внутри создаем новые константы.

### Инициализация фреймворка {#init}

Отправляем системе сигнал о приведении к полной боевой готовности. Для этого вызываем [Kohana::init] с передачей некоторых
 системных параметров:

    Kohana::init(array(
        'base_url'   => '/',
    ));

Всего доступно 8 параметров:

 * `base_url` - базовый адрес сайта. Например, если сайт находится в 'http://localhost/kohana/', то `base_url` равен 'kohana'.
  Значение по умолчанию - NULL, т.е. ожидается расположение в корне `DOCROOT`.
 * `index_file`  - имя фронтенд-файла. Используется для генерации УРЛов. По умолчанию 'index.php'.
 * `charset` - кодировка проекта, будет использована в HTTP-заголовках. По умолчанию 'utf-8'.
 * `cache_dir` - путь к директории со служебным кешем (для работы [Kohana::cache]). По умолчанию 'APPPATH/cache'. Не забудьте про права на запись.
 * `cache_life` - время жизни для служебного кеша (в секундах).
 * `caching` - флаг необходимости кеширования результатов поиска файлов по [КФС](intro/cascadefs). По умолчанию `FALSE`.
 * `errors` - флаг [обработки ошибок](basic/errors). Если `TRUE` (по умолчанию), то **Kohana** будет превращать ошибки PHP в исключения, а
  сами исключения перехватывать, показывая страницы с текстом ошибки и стеком вызовов для удобной отладки. Естественно, в
  режиме `Kohana::PRODUCTION` такого быть не должно.
 * `profile` - включение [профилирования](basic/profiling) (по умолчанию `TRUE`).
 * `expose` - "рекламный" флаг. Если `TRUE`, то в HTTP-заголовках появятся ключи 'X-Powered-By' и 'user-agent' с информацией
  о версии фреймворка. По умолчанию отключено (`FALSE`).

Кроме этого, в методе [Kohana::init] происходят различные полезные вещи, например чистка глобальных переменных ([Kohana::globals]),
 загрузка контейнера [конфигурационных файлов](intro/config) и [логов](basic/logs).

### Добавление дефолтных драйверов конфигов и логов {#logs-configs}

По умолчанию добавляются драйверы для файловых [конфигов](intro/config) и [логов](basic/logs):

    Kohana::$log->attach(new Log_File(APPPATH.'logs'));

    Kohana::$config->attach(new Config_File);

Обычно этих стандартных строчек хватает, пока вы не решите использовать другие типы хранилища (например, базу данных).

### Подключение модулей {#modules}

Изначально ни один штатный [модуль](intro/modules) не подключен, но все они добавлены в виде закомментированных строчек:

    Kohana::modules(array(
        // 'auth'       => MODPATH.'auth',       // Basic authentication
        // 'cache'      => MODPATH.'cache',      // Caching with multiple backends
        // 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
        // 'database'   => MODPATH.'database',   // Database access
        // 'image'      => MODPATH.'image',      // Image manipulation
        // 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
        // 'unittest'   => MODPATH.'unittest',   // Unit testing
        // 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
        ));

[!!] Не забывайте, что некоторые модули могут иметь зависимости от других модулей. Соответственно имеет смысл их расположить
 ниже по списку.

### Подключение маршрута по умолчанию

Добавляется [маршрут](basic/routing) по умолчанию, способный отловить самые простые запросы типа `<controller>/<action>/<id>`:

    Route::set('default', '(<controller>(/<action>(/<id>)))')
        ->defaults(array(
            'controller' => 'welcome',
            'action'     => 'index',
        ));

Навряд ли вы обойдетесь одним этим маршрутом. Поэтому добавляйте свои, но при этом помните, что они сработают только если их
 разместить выше этого дефолтного.

### Что еще можно здесь разместить? {#more}

Файл `bootstrap.php` изначально предназначен для пользовательских настроек, будь то вызовы **Kohana** или стандартные функции PHP.
 Например, для работы с классом `Cookie` необходимо установить свойство `Cookie::$salt`. Естественно, ему самое место в
 `bootstrap.php`, а не в базовом контроллере или каком-нибудь конфигурационном файле.

## Файлы MODPATH/*/init.php {#module-init}

Если файл `bootstrap.php` актуален для приложения в целом, то для каждого отдельно взятого [модуля](intro/modules) аналогичные функции
 выполняют файлы `init.php`.

Они загружаются один раз, после вызова [Kohana::modules]. В них обычно размещают динамические настройки модуля, роутинг и т.д.
 Например, в модуле `Userguide` файл `init.php` добавляет собственные маршруты, а также инициализирует автозагрузчик для
 парсера **Markdown**.