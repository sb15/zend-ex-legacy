<?

class Zend_Service_Yandex_SiteRank {

	public static function getRank($domain)
	{
		$url = 'http://' . $domain;
		$rateUrl = "http://bar-navig.yandex.ru/u?ver=2&url={$url}&show=1";
		$xml = file_get_contents($rateUrl);
		//echo $xml;
		$result = 0;
		if (preg_match('#<tcy rang="([0-9]+)" value="([0-9]+)"/>#is', $xml, $m)) {
			$result = $m[2];
		}
		return $result;
	}

}