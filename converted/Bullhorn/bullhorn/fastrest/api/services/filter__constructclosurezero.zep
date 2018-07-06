namespace Bullhorn\FastRest\Api\Services;

class Filter__constructClosureZero
{

    public function __construct()
    {

    }

    public function __invoke(value)
    {
    return filter_var(value, FILTER_VALIDATE_BOOLEAN, ["flags" : FILTER_NULL_ON_FAILURE]);
    }
}
