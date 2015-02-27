<?php
namespace Phalcon\ApiGenerator\DbCompare;
use Phalcon\ApiGenerator\Base;
class ParseHelper extends Base {
	/** @var  bool */
	private $tooManyClosingParenthesis;

	/**
	 * Getter
	 * @return boolean
	 */
	public function isTooManyClosingParenthesis() {
		return $this->tooManyClosingParenthesis;
	}

	/**
	 * Setter
	 * @param boolean $tooManyClosingParenthesis
	 */
	private function setTooManyClosingParenthesis($tooManyClosingParenthesis) {
		$this->tooManyClosingParenthesis = $tooManyClosingParenthesis;
	}


	/**
	 * parseSection
	 *
	 * @param string $section
	 * @param string $breakChar
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function parseSection($section, $breakChar=' ') {
		$this->setTooManyClosingParenthesis(false);
		$inQuotes = false;
		$lastWasQuote = false;
		$doSetLastWasQuote = false;
		$quoteType = null;
		$parenthesisCount = 0;
		$buffer = '';
		$length = strlen($section);
		for($i=0;$i<$length;$i++) {
			$char = $section[$i];
			if($inQuotes) {
				if($char == $quoteType) {
					$inQuotes = false;
					$doSetLastWasQuote = true;
				}
			} else {
				//Fix for double quotes to show a quote
				if($char == $quoteType && $lastWasQuote) {
					$buffer .= $quoteType;
					$inQuotes = true;
				} elseif($char == '"' || $char == '\'') {
					$inQuotes = true;
					$quoteType = $char;
				} elseif($char == $breakChar && $parenthesisCount == 0) {
					return $buffer;
				} elseif($char == '(') {
					$parenthesisCount++;
				} elseif($char == ')') {
					$parenthesisCount--;
					if($parenthesisCount == -1) {
						$this->setTooManyClosingParenthesis(true);
						return $buffer;
					}
				}
			}
			$buffer .= $char;
			if($doSetLastWasQuote) {
				$doSetLastWasQuote = false;
				$lastWasQuote = true;
			} else {
				$lastWasQuote = false;
			}
		}
		return $buffer;
	}

}