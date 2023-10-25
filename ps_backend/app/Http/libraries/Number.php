<?php
	namespace Backend\libraries;
	
	class Number {

		private static $gDecPlaces=2;// global decimal places

		public static function globalFormat($number)
		{
			return custom_money_format($number);
		}
		
		public static function globalRound($number)
		{
			if(trim($number)=="")
			{
				$number=0;
			}
			$numberSplit=explode(".",$number);
			if(isset($numberSplit[1]))
			{
				$decimal=substr($numberSplit[1],0,2);
				$number=$numberSplit[0].".".$decimal;
			}
			else
			{
				$number=$numberSplit[0];
			}
			return $number;
		}
	}
?>