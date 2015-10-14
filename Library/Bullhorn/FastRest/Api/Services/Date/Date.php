<?php
namespace Bullhorn\FastRest\Api\Services\Date;
use Bullhorn\FastRest\Api\Services\DataValidation\StrToTime;
use Bullhorn\FastRest\DependencyInjection;
use Phalcon\DI\InjectionAwareInterface;

class Date implements InjectionAwareInterface {
	use DependencyInjection;

	/** @type  double|false */
	private $dateInt;
	/** @type bool */
	private $equal24;

	/**
	 * Constructor
	 * @param string|int|Date $dateTime
	 */
	public function __construct($dateTime=null) {
		if(is_int($dateTime) || is_double($dateTime)) {
			$this->dateInt = $dateTime;
		} elseif(is_object($dateTime) && $dateTime instanceof Date) {
			$this->dateInt = $dateTime->getEpoch();
		} elseif(is_null($dateTime)) {
			$this->dateInt = time();
		} elseif($dateTime === '') {
			$this->dateInt = false;
		} else {
			$strToTime = new StrToTime();
			$this->dateInt = $strToTime->parse($dateTime);
			if($this->dateInt===false) {
				throw new \InvalidArgumentException('Un-Parsable time: '.$dateTime);
			}
		}
		$this->setEqual24(in_array($dateTime, array('24:00:00','24:00')));
	}

	/**
	 * format
	 * @param string|null $format
	 * @return bool|string
	 */
	public function format($format=null) {
		if(is_null($format)) {
			$format = $this->getFormatter()->getCurrentFormat();
		}
		return date($format, $this->getEpoch());
	}

	/**
	 * Converts to a date format
	 * @return string
	 */
	public function __toString() {
		return $this->toDate();
	}

	/**
	 * Gets the current date format
	 *
	 * @param bool $includeYear
	 * @param bool $includeDate
	 *
	 * @return string
	 */
	public function getDateFormat($includeYear=TRUE, $includeDate=TRUE) {
		return $this->getFormatter()->getDateFormat($includeYear, $includeDate);
	}

	/**
	 * Getter
	 * @return boolean
	 */
	private function isEqual24() {
		return $this->equal24;
	}

	/**
	 * Setter
	 * @param boolean $equal24
	 */
	private function setEqual24($equal24) {
		$this->equal24 = $equal24;
	}



	/**
	 * Converts a timestamp to mysql date format
	 * @return string
	 */
	public function toDate() {
		if($this->getEpoch()==0) {
			return '0000-00-00';
		}
		return $this->format('Y-m-d');
	}

	/**
	 * Converts a timestamp to mysql datetime format
	 * @return string
	 */
	public function toTime() {
		if($this->isEqual24()) {
			return '24:00:00';
		}
		if($this->getEpoch()==0) {
			return '00:00:00';
		}
		return $this->format('H:i:s');
	}

	/**
	 * Converts a timestamp to mysql datetime format
	 * @return string
	 */
	public function toDateTime() {
		if($this->getEpoch()==0) {
			return '0000-00-00 00:00:00';
		}
		return $this->toDate().' '.$this->toTime();
	}

	/**
	 * getFormatter
	 * @return Formatter
	 */
	private function getFormatter() {
		return Formatter::getDefault();
	}

	/**
	 * Gets the raw date as an int timestamp
	 * @return double|false
	 */
	public function getEpoch() {
		if($this->dateInt === false) {
			return false;
		}
		return (double)$this->dateInt;
	}

	/**
	 * Compares two Dates
	 * @param Date $otherDate
	 * @return bool
	 */
	public function equals(Date $otherDate) {
		return $this->__toString() === $otherDate->__toString();
	}

	/**
	 * Getter
	 * @param Date $otherDate
	 * @return bool
	 */
	public function isGreaterThan(Date $otherDate) {
		return $this->__toString() > $otherDate->__toString();
	}


	/**
	 * Gets an offset day
	 * @param int $offsetNumDays
	 * @return Date
	 */
	public function getOffsetDay($offsetNumDays) {
		return new self(
			mktime(
				0,
				0,
				0,
				date('n', $this->getEpoch()),
				date('j', $this->getEpoch())+$offsetNumDays,
				date('Y', $this->getEpoch())
			)
		);
	}

	/**
	 * getNextDay
	 * @return Date
	 */
	public function getNextDay() {
		return $this->getOffsetDay(1);
	}

	/**
	 * getPreviousDay
	 * @return Date
	 */
	public function getPreviousDay() {
		return $this->getOffsetDay(-1);
	}

	/**
	 * Gets an offset of years
	 *
	 * @param int $offsetNumYears
	 *
	 * @return Date
	 */
	public function getOffsetYear($offsetNumYears) {
		return new self(
			mktime(
				0,
				0,
				0,
				date('n', $this->getEpoch()),
				date('j', $this->getEpoch()),
				date('Y', $this->getEpoch())+$offsetNumYears
			)
		);
	}

	/**
	 * Gets an offset of months
	 *
	 * @param int $offsetNumMonths
	 *
	 * @return Date
	 */
	public function getOffsetMonth($offsetNumMonths) {
		return new self(
			mktime(
				0,
				0,
				0,
				date('n', $this->getEpoch())+$offsetNumMonths,
				date('j', $this->getEpoch()),
				date('Y', $this->getEpoch())
			)
		);
	}

	/**
	 * Gets the first day in the month
	 * @return Date
	 */
	public function getStartOfMonth() {
		return new self(
			mktime(
				0,
				0,
				0,
				date('n', $this->getEpoch()),
				1,
				date('Y', $this->getEpoch())
			)
		);
	}

	/**
	 * Gets the last day in the month
	 * @return Date
	 */
	public function getEndOfMonth() {
		return new self(
			mktime(
				0,
				0,
				0,
				date('n', $this->getEpoch())+1,
				0,
				date('Y', $this->getEpoch())
			)
		);
	}

	/**
	 * Gets the first second in a week
	 * @return Date
	 */
	public function getStartOfWeek() {
		$dayInWeek = $this->getEpoch();
		if(date('G', $dayInWeek)<6) {
			$dayInWeek += 60*60*12; //Offset for daylight savings time
		}
		$week = date('w', $dayInWeek);
		$offset = $this->getFormatter()->getWeekOffset();
		if($week>=$offset) {
			$day = date('j', $dayInWeek)-date('w', $dayInWeek)+$offset;
		} else { //Go To previous week
			$day = date('j', $dayInWeek)-date('w', $dayInWeek)+$offset-7;
		}
		return new self(mktime(12, 0, 0, date('n', $dayInWeek), $day, date('Y', $dayInWeek)));
	}

	/**
	 * Gets the last second in a week
	 * @return Date
	 */
	public function getEndOfWeek( ) {
		$dayInWeek = $this->getEpoch();
		if(date('G', $dayInWeek)<11) {
			$dayInWeek += 60 * 60 * 12; //Offset for daylight savings time
		}
		$week = date('w', $dayInWeek);
		$offset = $this->getFormatter()->getWeekOffset();
		if($week>=$offset) {
			$day = date('j', $dayInWeek)-date('w', $dayInWeek)+$offset;
		} else { //Go To previous week
			$day = date('j', $dayInWeek)-date('w', $dayInWeek)+$offset-7;
		}
		return new self(mktime(-12, 0, 0, date('n', $dayInWeek), $day+7, date('Y', $dayInWeek)));
	}
}