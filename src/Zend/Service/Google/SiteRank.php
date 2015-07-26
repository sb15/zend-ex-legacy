class SiteRank {

}

<?php
   if ($action == "exec")
   {
    // Определяем константу GOOGLE_MAGIC
    define('GOOGLE_MAGIC', 0xE6359A60);

    // Удаляем "www." из $url
    $url = str_replace("www.", "", $_REQUEST['url']);

    /* 1. Функции для определения ТИЦ */

    //
    // Функция, определяющая значение тематического индекса цитирования (ТИЦ) Яндекса
    //
    // Входные данные:
    // $url - адрес страницы, ТИЦ которой требуется определить
    //
    // Выходные данные:
    // $ci[1] - значение ТИЦ для страницы $url
    //
    function getCI($url)
    {
     $url = str_replace("www.", "", $url);
     $ci_url = "http://bar-navig.yandex.ru/u?ver=2&show=32&url=http://www.".$url."/";
     $ci_data = implode("", file("$ci_url"));
     preg_match("/value=\"(.\d*)\"/", $ci_data, $ci);

     if ($ci[1] == "")
      return 0; // Если не смогли определить ТИЦ...
     else
      return $ci[1]; // Вот оно счастье...
    }
    /* 2. Функции для определения PR */

    //
    // Функция, выполняющая беззнаковый сдвиг вправо
    //
    // Входные данные:
    // $a - переменная, подвергаемая сдвигу
    // $b - величина сдвига
    //
    // Выходные данные:
    // $a - результат выполнения сдвига
    //
    function zeroFill($a, $b)
    {
     $z = hexdec(80000000);

     if ($z & $a)
     {
      $a = ($a >> 1);
      $a &= (~$z);
      $a |= 0x40000000;
      $a = ($a >> ($b - 1));
     }
     else
     {
      $a = ($a >> $b);
     }

     return $a;
    }

    // Функция используется при вычислении Google Checksum (см. ниже)
    function mix($a, $b, $c)
    {
     $a -= $b;
     $a -= $c;
     $a ^= (zeroFill($c, 13));

     $b -= $c;
     $b -= $a;
     $b ^= ($a << 8);

     $c -= $a;
     $c -= $b;
     $c ^= (zeroFill($b, 13));

     $a -= $b;
     $a -= $c;
     $a ^= (zeroFill($c, 12));

     $b -= $c;
     $b -= $a;
     $b ^= ($a << 16);

     $c -= $a;
     $c -= $b;
     $c ^= (zeroFill($b, 5));

     $a -= $b;
     $a -= $c;
     $a ^= (zeroFill($c, 3));

     $b -= $c;
     $b -= $a;
     $b ^= ($a << 10);

     $c -= $a;
     $c -= $b;
     $c ^= (zeroFill($b, 15));

     return array($a, $b, $c);
    }

    //
    // Функция, вычисляющая Google Checksum (контрольную сумму Google)
    //
    // Входные данные:
    // $url - URL, для которого требуется определить Google Checksum
    // $length - длина строки $url (по умолчанию - null, вычисляется в функции)
    // $init - "волшебное" число
    //
    // Выходные данные:
    // $mix[2] - значение Google Checksum для заданного адреса $url
    //
    function GoogleCH($url, $length = null, $init = GOOGLE_MAGIC)
    {
     if (is_null($length))
     {
      $length = sizeof($url);
     }

     $a = $b = 0x9E3779B9;
     $c = $init;
     $k = 0;
     $len = $length;

     while($len >= 12)
     {
      $a += ($url[$k + 0] + ($url[$k + 1] << 8) + ($url[$k + 2] << 16) + ($url[$k + 3] << 24));
      $b += ($url[$k + 4] + ($url[$k + 5] << 8) + ($url[$k + 6] << 16) + ($url[$k + 7] << 24));
      $c += ($url[$k + 8] + ($url[$k + 9] << 8) + ($url[$k + 10] << 16) + ($url[$k + 11] << 24));
      $mix = mix($a, $b, $c);
      $a = $mix[0];
      $b = $mix[1];
      $c = $mix[2];
      $k += 12;
      $len -= 12;
     }

      $c += $length;

      switch ($len)
      {
       case 11: $c += ($url[$k + 10] << 24);
       case 10: $c += ($url[$k + 9] << 16);
       case 9 : $c += ($url[$k + 8] << 8);
       // Первый байт $c зарезервирован для значения $length
       case 8 : $b += ($url[$k + 7] << 24);
       case 7 : $b += ($url[$k + 6] << 16);
       case 6 : $b += ($url[$k + 5] << 8);
       case 5 : $b += ($url[$k + 4]);
       case 4 : $a += ($url[$k + 3] << 24);
       case 3 : $a += ($url[$k + 2] << 16);
       case 2 : $a += ($url[$k + 1] << 8);
       case 1 : $a += ($url[$k + 0]);
      }

      $mix = mix($a, $b, $c);

      return $mix[2];
    }

    //
    // Функция, преобразующая строку в массив целых чисел, содержащий числовые
    // значения каждого символа преобразуемой строки<br/>     //
    // Входные данные:
    // $string - преобразуемая строка
    //
    // Выходные данные:
    // $result - массив целых чисел, содержащий числовые значения каждого
    // символа строки $string
    //
    function strord($string)
    {
     for ($i = 0; $i < strlen($string); $i++)
     {
      $result[$i] = ord($string{$i});
     }
     return $result;
    }

    //
    // Функция, определяющая значение Google PageRank (PR)
    //
    // Входные данные:
    // $url - адрес страницы, PageRank которой требуется определить
    // $prefix - тип получаемых сведений (в данном примере получаем
    // сведения Google Toolbar - $prefix = "info:")
    // $datacenter - сервер, с которого требуется получить сведения (по
    // умолчанию - www.google.com)
    //
    // Выходные данные:
    // $rank - значение PageRank для страницы $url
    //
    function getrank($url, $prefix = "info:", $datacenter = "www.google.com")
    {
     $url = $prefix.$url;
     $ch = GoogleCH(strord($url)); // Вычисляем Google Checksum
     $file = "http://$datacenter/search?client=navclient-auto&ch=6$ch&features=Rank&q=$url";

     // Примечание: для получения детализированного результата запроса (в XML
     // формате) требуется убрать "&features=Rank"; обработка подобного результата
     // существенно отличается от рассматриваемой обработки результата запроса
     // и в данном скрипте не рассматривается

     $olderrorlevel = error_reporting(0); // Временно приостанавливаем выдачу сообщений об ошибках
     $data = file($file);
     error_reporting($olderrorlevel); // Устанавливаем предыдущий уровень сообщений об ошибках
     // Если $data не содержит данных Google или введен неверный URL
     // Примечание: здесь функция preg_match выполняет роль простого средства,
     // проверяющего URL на корректность
     if (!$data || preg_match("/(.*)\.(.*)/i", $url) == 0)
      return "Нет данных";

     $rankarray = explode(':', $data[2]); // Две строчки отделяют значение Google PageRank
     $rank = trim($rankarray[2]); // Удаляем ненужные пробелы и символы новой строки
     // Если не удалось определить PageRank
     if ($rank == "")
      return "Нет данных";

     return $rank; // Вот оно счастье...
    } 