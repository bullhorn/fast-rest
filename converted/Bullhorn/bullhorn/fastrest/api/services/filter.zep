namespace Bullhorn\FastRest\Api\Services;

use Phalcon\Filter as PhalconFilter;
class Filter extends PhalconFilter
{
    const FILTER_BOOLEAN = "boolean";
    const FILTER_NULLIFY = "nullify";
    /**
     * Constructor
     */
    public function __construct()
    {
        this->add("boolean", function(value) {
            return filter_var(
                value,
                FILTER_VALIDATE_BOOLEAN,
                ["flags":FILTER_NULL_ON_FAILURE]
            );
        });
        this->add("nullify", function(value) {
            if(value === "" || value === "null") {
                return null;
            } else {
                return value;
            }
        });
    }

}