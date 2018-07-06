<?php
namespace Bullhorn\FastRest\Api\Services\Date;

use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\DependencyInjectionHelper;
use InvalidArgumentException;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;

class Formatter implements InjectionAwareInterface {
    const DATE_FORMAT_UK = 'd/m/Y';
    const DATE_FORMAT_US = 'm/d/Y';
    const DATE_FORMAT_EURO = 'd-m-Y';

    /** @var  string */
    private $currentFormat;
    /** @type string[] */
    private $previousFormats = [];
    /** @type int 0=Sunday, 6=Saturday */
    private $weekOffset = 0;
    /** @type null|Formatter */
    private static $lastCreated = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->reset();
    }

    public function getDi() {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(DiInterface $di) {
        DependencyInjectionHelper::setDi($di);
    }

    /**
     * reset
     * @return void
     */
    public function reset() {
        $this->currentFormat = self::DATE_FORMAT_US;
        $this->setPreviousFormats([]);
        $this->setWeekOffset(0);
    }

	/**
	 * getFormatter
	 * @return Formatter
	 */
	public static function getDefault() {
		if(is_null(self::$lastCreated)) {
			self::$lastCreated = new self();
		}
		return self::$lastCreated;
	}

    /**
     * getMysqlDateFormat
     * @param string|null $dateFormat
     * @return string
     */
    public function getMysqlDateFormat($dateFormat = NULL) {
        if(is_null($dateFormat)) {
            $dateFormat = $this->getCurrentFormat();
        }
        switch($dateFormat) {
            case self::DATE_FORMAT_UK:
                return '%d/%m/%Y';
                break;
            case self::DATE_FORMAT_US:
                return '%m/%d/%Y';
                break;
            case self::DATE_FORMAT_EURO:
                return '%d-%m-%Y';
                break;
            default:
                throw new InvalidArgumentException('Unexpected Date Format: ' . $dateFormat);
                break;
        }
    }

    /**
     * getMysqlDateTimeFormat
     * @param string|null $dateFormat
     * @return string
     */
    public function getMysqlDateTimeFormat($dateFormat = NULL) {
        return $this->getMysqlDateFormat($dateFormat) . ' %l:%i %p';
    }

    /**
     * Getter
     * @return int
     */
    public function getWeekOffset() {
        return $this->weekOffset;
    }

    /**
     * Setter
     * @param int $weekOffset
     * @throws InvalidArgumentException
     */
    public function setWeekOffset($weekOffset) {
        $weekOffset = Assert::isInt($weekOffset);
        if($weekOffset > 6 || $weekOffset < 0) {
            throw new InvalidArgumentException('Week Offset must be between 0 and 6');
        }
        $this->weekOffset = $weekOffset;
    }

    /**
     * Getter
     * @return string[]
     */
    private function getPreviousFormats() {
        return $this->previousFormats;
    }

    /**
     * Setter
     * @param string[] $previousFormats
     */
    private function setPreviousFormats(array $previousFormats) {
        $this->previousFormats = $previousFormats;
    }

    /**
     * Gets the current format
     * @return string
     */
    public function getCurrentFormat() {
        return $this->currentFormat;
    }

    /**
     * getAllowedFormats
     * @return string[]
     */
    public function getAllowedFormats() {
        return [self::DATE_FORMAT_EURO, self::DATE_FORMAT_UK, self::DATE_FORMAT_US];
    }

    /**
     * getAllowedFormatsNamed
     * @return string[] string[format]=>label
     */
    public function getAllowedFormatsNamed() {
        return [self::DATE_FORMAT_US => 'US Format', self::DATE_FORMAT_UK => 'UK Format', self::DATE_FORMAT_EURO => 'Euro Format'];
    }

    /**
     * Sets the current format
     * @param string $currentFormat
     */
    public function setCurrentFormat($currentFormat) {
        $allowedFormats = $this->getAllowedFormats();
        if(!in_array($currentFormat, $allowedFormats)) {
            throw new InvalidArgumentException('Invalid Date Format: ' . $currentFormat . ', expected: ' . implode(', ', $allowedFormats));
        }

        $allFormats = $this->getPreviousFormats();
        $allFormats[] = $this->getCurrentFormat();
        $this->setPreviousFormats($allFormats);

        $this->currentFormat = $currentFormat;
    }

    /**
     * getTimeFormat
     * @return string
     */
    public function getTimeFormat() {
        return 'g:i a';
    }

    /**
     * getDateTimeFormat
     * @param bool|TRUE $includeYear
     * @return string
     */
    public function getDateTimeFormat($includeYear = TRUE) {
        return $this->getDateFormat($includeYear) . ' ' . $this->getTimeFormat();
    }

    /**
     * Gets the current date format
     *
     * @param bool $includeYear
     * @param bool $includeDate
     *
     * @return string
     */
    public function getDateFormat($includeYear = TRUE, $includeDate = TRUE) {
        $returnVar = $this->getCurrentFormat();
        if(!$includeYear) {
            if(preg_match('@^Y@', $returnVar)) {
                $returnVar = substr($returnVar, 2); //If it starts with the year
            } elseif(preg_match('@Y$@', $returnVar)) {
                $returnVar = substr($returnVar, 0, -2); //If ends with the year
            } else {
                $returnVar = preg_replace('@Y.@', '', $returnVar); //If in middle if year
            }
        }
        if(!$includeDate) {
            if(preg_match('@^d@', $returnVar)) {
                $returnVar = substr($returnVar, 2); //If it starts with the year
            } elseif(preg_match('@d$@', $returnVar)) {
                $returnVar = substr($returnVar, 0, -2); //If ends with the year
            } else {
                $returnVar = preg_replace('@d.@', '', $returnVar); //If in middle if year
            }
        }
        return $returnVar;
    }

    /**
     * reverts to the previous format, does nothing if there are no previous formats
     * @return void
     */
    public function revertFormat() {
        $allFormats = $this->getPreviousFormats();
        if(!empty($allFormats)) {
            $this->currentFormat = array_pop($allFormats);
            $this->setPreviousFormats($allFormats);
        }
    }

    /**
     * Formats a date
     *
     * @param Date $input
     *
     * @return string
     */
    public function formatDate(Date $input) {
        return $input->format($this->getCurrentFormat());
    }
}