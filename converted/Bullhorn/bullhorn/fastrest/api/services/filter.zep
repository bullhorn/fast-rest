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
        this->add("boolean", new Filter__constructClosureOne());
        this->add("nullify", new Filter__constructClosureOne());
    }

}