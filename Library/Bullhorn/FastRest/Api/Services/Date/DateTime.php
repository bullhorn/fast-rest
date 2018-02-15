<?php
namespace Bullhorn\FastRest\Api\Services\Date;
class DateTime extends Date {
    /**
     * Converts to a date time format
     * @return string
     */
    public function __toString() {
        return $this->toDateTime();
    }

    /**
     * format
     * @param string|null $format
     * @return bool|string
     */
    public function format($format = null) {
        if(is_null($format)) {
            $format = $this->getFormatter()->getDateTimeFormat();
        }
        return date($format, $this->getEpoch());
    }

}