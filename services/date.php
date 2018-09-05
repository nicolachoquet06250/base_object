<?php

namespace project\services;


class date {
	private $days2Fr = [
		'Mon' => 'Lun',
		'Tue' => 'Mar',
		'Wed' => 'Mer',
		'Thu' => 'Jeu',
		'Fri' => 'ven',
		'Sat' => 'Sam',
		'Sun' => 'Dim',
	];

	private $months2Fr = [
		'Jan' => 'Jan',
		'Fab' => 'Fev',
		'Mar' => 'Mar',
		'Apr' => 'Avr',
		'May' => 'Mai',
		'Jun' => 'Jui',
		'Jul' => 'Jui',
		'Aug' => 'Aou',
		'Sep' => 'Sep',
		'Oct' => 'Oct',
		'Nov' => 'Nov',
		'Dec' => 'Dec',
	];
	public function format($format, $date = null) {
		if(!$date) {
			$date = time();
		}
		date($format, $date);
	}

	public function en2fr($date, $houre = false, $second = false) {
		$format = 'd/m/Y';
		if($houre) {
			$format .= ' H:i';
			if($second) {
				$format .= ':s';
			}
		}
		$this->format($format, $date);
	}

	public function day2fr($day_code) {
		if(isset($this->days2Fr[$day_code])) {
			return $this->days2Fr[$day_code];
		}
		return '';
	}

	public function month2fr($month_code) {
		if(isset($this->months2Fr[$month_code])) {
			return $this->months2Fr[$month_code];
		}
		return '';
	}

	public function curent_day2fr() {
		return $this->day2Fr(date('D'));
	}

	public function curent_month2fr() {
		return $this->day2Fr(date('M'));
	}
}