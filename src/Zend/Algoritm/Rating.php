<?

class Zend_Algoritm_Rating
{
	/**
	* @param sender - Рейтинг пользователя голосующего
	* @param receiver - Рейтинг пользователя получающего голос
	*/
	public static function getRatePoints($sender, $receiver) 
	{		
		if ($receiver <= 0) { 
			$receiver = 1; 
		}
		$aInSquare = ($sender * 2) * ($sender * 2);
		$bInSquare = $receiver * $receiver;
		$cInSquare = $bInSquare + $aInSquare;
		$c = sqrt($cInSquare);

		$result = (int) round ($c / $receiver);

		if ($result > $receiver / 2) { 
			$result = (int) round($receiver / 2); 
		}
		return $result;
	}
}