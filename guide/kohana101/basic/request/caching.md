# Кэширование запросов

Если вы используете внешние запросы (например, API социальных сетей), рано или поздно возникнет вопрос о сокращении времени
 на получение ответа. Естественно, в первую очередь в голову приходит кэширование результатов (т.е. чистых данных), но может
 потребоваться и кэш самого запроса (точнее, ответа на него). И тут на помощь приходит [HTTP-кэширование](http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html).

## Включаем кэш {#setup}

Как мы [знаем](basic/request#new), при создании нового запроса можно передать объект `HTTP_Cache`, который в дальнейшем будет
 отвечать за кэширование запроса:

    $request = Request::factory('foo/bar', HTTP_Cache::factory('memcache'));
    // либо так
    $request = Request::factory('foo/bar');
    $request->client()->cache(HTTP_Cache::factory('memcache'));

[!!] Можно использовать любой драйвер из модуля `Cache` (сам модуль, естественно, должен быть подключен в `bootstrap.php`,
 например `file`, `memcache`, `sqlite`. И не забудьте настроить конфигурационный файл для драйвера.

Как видно, объект `HTTP_Cache` может быть добавлен в любой момент до выполнения запроса.

## Пишем в кэш {#writing}

Рассмотрим поподробнее, как происходит запись в кэш. При выполнении метода `execute()` объект `Request` осуществляет проверку,
 передан ли экземпляр `HTTP_Cache`. Если такой существует, то дальнейшее выполнение запроса идет через него.

 * Генерируется ключ кэша. По умолчанию он создается с помощью метода [HTTP_Cache::basic_cache_key_generator], и основан
  на различных данных запроса (тело, заголовки, URI и Query-строка). Можно установить свой собственный алгоритм с помощью
  метода [HTTP_Cache::cache_key_callback].
 * Выполняется собственно запрос. Результат его работы (`Response`) попадает в `HTTP_Cache`.
 * Для ответа подсчитывается время жизни (TTL), в соответствии со [спецификацией](http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.2).
 * Полученный ответ кэшируется соответствующим драйвером.
 * Оригинальный ответ возвращается системе с добавлением заголовка `X-Cache-Status: MISS`. Этот заголовок говорит о том,
  такой запрос ранее не встречался (по крайней мере в кэше нет актуальной его копии).

## Читаем из кэша {#reading}

 * Генерируется ключ кэша.
 * Извлекаем сохраненный `Response` из кэша.
 * Добавляется заголовок `X-Cache-Status: HIT` и `x-cache-hits: 1`. Значение `HIT` показывает, что ответ был извлечен из
  кэша, а число в заголовоке `X-Cache-Hits` - счетчик использований этого кэшированного ответа. Данные заголовки позволяют
  определить, "живой" это ответ или кэшированный.

## Условия использования кэша {#requirements}

Для того, чтобы появилась возможность запрашивать из кэша ответы, должен выполниться ряд условий:

 * Естественно, надо добавить в запрос объект `HTTP_Cache`.
 * Это должен быть `GET`-запрос. Точнее, не `POST`/`PUT`/`DELETE`. Для этих типов запросов кэширование никогда не включается, и в
  ответ добавляется заголовок `Cache-Control : no-cache, must-revalidate`).
 * Наличие заголовка `Pragma: no-cache` в запросе подразумевает (в целях совместимости с HTTP/1.0) запрет на кэширование.
  Поэтому запросы с такими заголовками всегда будут выполняться в обход кэша.

Кроме того, есть условия, при которых объект `Response` не будет кэширован:

 * Ответ имеет актуальное время жизни (TTL).
 * В заголовках ответа есть `Cache-Control` со значением `no-cache` или `no-store`.
 * Заголовок `Cache-Control` содержит значение `private`, а при создании используемого объекта `HTTP_Cache` не был установлен
  параметр `allow_private_cache` (по умолчанию `FALSE`):

~~~~
	$cache = HTTP_Cache::factory('memcache')->allow_private_cache(TRUE);
	$request->client()->cache($cache);
~~~~

[!!] Исключение - если в `Cache-Control` передан параметр `s-maxage` и его значение >=1. В таком случае ответ будет
  кэширован. Это немного странно, т.к. в спецификации [указано](http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.3),
  что для `private`-кэша параметр `s-maxage` должен игнорироваться.

В общих чертах, для успешного использования HTTP-кэширования должны выполняться основных условия: кэширование разрешено
 и кэш еще актуален.

## Пример кэширования {#example}

	class Controller_Welcome extends Controller {

		public function action_index()
		{
			// создаем ВНЕШНИЙ запрос с использованием HTTP_Cache
			$r = Request::factory('http://kohana/welcome/cached', HTTP_Cache::factory('memcache'))->execute();
			// нас не столько интересует ответ, сколько его заголовки
			echo Debug::vars($r->headers());
		}

		public function action_cached()
		{
			// чтобы кэширование работало, его на разрешить
			// max-age=3600 означает кэширование на 1 час
			$this->response->headers('cache-control', 'public, max-age=3600');
			$this->response->body('cached answer!');
		}

	}

Теперь, если запустить `http://kohana/welcome`, можно будет наблюдать заголовки ответа, что-то вроде:

	object HTTP_Header(8) {
	    public server => string(11) "nginx/1.2.2"
	    public date => string(29) "Sat, 27 Oct 2012 10:54:43 GMT"
	    public content-type => string(24) "text/html; charset=utf-8"
	    public transfer-encoding => string(7) "chunked"
	    public connection => string(10) "keep-alive"
	    public x-powered-by => string(10) "PHP/5.3.16"
	    public cache-control => string(20) "public, max-age=3600"
	    public x-cache-status => string(4) "MISS"
	}

Запрос выполнен, ответ получен. По заголовку `X-Cache-Status` видно, что он не из кэша. Обновим страницу:

	object HTTP_Header(9) {
	    public server => string(11) "nginx/1.2.2"
	    public date => string(29) "Sat, 27 Oct 2012 10:54:43 GMT"
	    public content-type => string(24) "text/html; charset=utf-8"
	    public transfer-encoding => string(7) "chunked"
	    public connection => string(10) "keep-alive"
	    public x-powered-by => string(10) "PHP/5.3.16"
	    public cache-control => string(20) "public, max-age=3600"
	    public x-cache-status => string(3) "HIT"
	    public x-cache-hits => integer 1
	}

Появился `X-Cache-Hits`, а значит наше кэширование работает! Кроме того, заголовок `Date` не меняется при обновлении страницы.