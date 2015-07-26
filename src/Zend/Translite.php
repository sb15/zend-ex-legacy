<?

class Zend_Translite
{
	public static function translite($text, $encoding = 'UTF-8')
	{
		$from = array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т',
					  'У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё',
					  'ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ',
					  'ъ','ы','ь','э','ю','я');

		if ($encoding != 'UTF-8') {
			foreach ($from as &$word) {
				$word = iconv('UTF-8', $encoding, $word);
			}
		}

		$to   = array('A','B','V','G','D','E','E','J','Z','I','I','K','L','M','N','O','P','R','S','T',
					  'U','F','H','C','Ch','Sh','Sch','\'','Y','\'','E','Yu','Ya','a','b','v','g','d',
					  'e','e','j','z','i','i','k','l','m','n','o','p','r','s','t','u','f','h','c','ch',
					  'sh','sch','\'','y','\'','e','yu','ya');
		return str_replace($from, $to, $text);
	}
}