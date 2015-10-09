<?php
namespace Bullhorn\FastRest\Api\Services\DataValidation;
use Bullhorn\FastRest\Api\Services\Date\Date;
use Bullhorn\FastRest\Api\Services\Date\Formatter;
use InvalidArgumentException;

class StrToTime {

	/**
	 * Converts a string of any date to a timestamp
	 * @param int|string|Date $date
	 * @throws InvalidArgumentException
	 * @return int
	 */
	public function parse($date) {
		if(is_int($date) || is_double($date) || (is_string($date) && preg_match('@^[0-9]+$@', $date))) {
			return (double)$date;
		} elseif($date instanceof Date) {
			return $date->getEpoch();
		} elseif(is_null($date)) {
			return false;
		} else {
			$date = trim(Assert::isString($date));
		}
		$date = $this->parseHoursMinutes($date);
		if(preg_match('@^[0-9]+$@', $date)) {
			return (int)$date;
		}
		$date = $this->stripTimezone($date);
		$date = $this->stripRange($date);

		if(preg_match('@^(\d{4}-\d{2}-\d{2} )?24:00(:00)?$@', trim($date), $matches)) {
			if(isset($matches[1]) && strlen($matches[1])>0) {
				$date = strtotime($matches[1]);
			} else {
				$date = time();
			}
			return mktime(0, 0, 0, date('n', $date), date('j', $date)+1, date('Y', $date));

		}
		//change a/p to am/pm
		$date = preg_replace('@(^|[^a-z])(a|p)([^a-z]|$)@i', '\\1\\2m\\3', $date);
		//Add a colon from 315am,1215 am
		$date = preg_replace('@(^|[^0-9])(\d{1,2})(\d{2})\s*(am|pm)@i', '\\1\\2:\\3\\4', $date);

		//Fix for dashes instead of slashes
		$date = preg_replace('@(\d{2})-(\d{2})-(\d{4})@', '\\1/\\2/\\3', $date);
		//Fix for dots instead of slashes
		$date = preg_replace('@(\d{2})\.(\d{2})\.(\d{4})@', '\\1/\\2/\\3', $date);

		//Fix for UK/euro Version
		if(in_array(Formatter::getDefault()->getDateFormat(), array(Formatter::DATE_FORMAT_UK,Formatter::DATE_FORMAT_EURO))) {
			//Switch the d/m/Y to the US format m/d/Y to be able to parse
			$date = preg_replace('@(\d{2})/(\d{2})/(\d{4})@', '\\2/\\1/\\3', $date);
		}
		switch(strtolower($date)) {
			case '9999-12-31':
				return 253402232400;
				break;
			case '0000-00-00':
				return 0;
				break;
			case '0000-00-00 00:00:00':
				return 0;
				break;
			case '24:00:00':
			case '24:00':
				return mktime(0, 0, 0, date('n'), date('j')+1, date('Y'));
				break;
			case 'thanksgiving':
				$firstDay = mktime(0, 0, 0, 11, 1);
				$add = 26-date('w', $firstDay);
				if($add<22) {  $add += 7; }
				return mktime(0, 0, 0, 11, $add);
				break;
			default:
				return strtotime($date);
		}
	}

	/**
	 * stripTimezone
	 * @param string $date
	 * @return string
	 */
	private function stripTimezone($date) {
		$timezoneRegex = '|\sGMT[-+]\d*\s?\(.*?\)|';
		return preg_replace($timezoneRegex, '', $date);
	}

	/**
	 * stripRange
	 * @param string $date
	 * @return array
	 */
	private function stripRange($date) {
		if (preg_match('@\d{2}/\d{2}/\d{4} - (\d{2}/\d{2}/\d{4})@', $date, $matches)) {
			$date = $matches[1];
		}
		return $date;
	}

	/**
	 * parseHoursMinutes
	 * @param string $date
	 * @return string
	 */
	private function parseHoursMinutes($date) {
		if(preg_match('@^(?P<hour>\d{1,2})(?P<minute>\d{2})$@', $date, $matches)) {
			$date = $matches['hour'].':'.$matches['minute'];
		}
		return $date;
	}

}