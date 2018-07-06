namespace Bullhorn\FastRest\Api\Services;

class Filter__constructClosureOne
{

    public function __construct()
    {
        
    }

    public function __invoke(value)
    {
    if value === "" || value === "null" {
        return null;
    } else {
        return value;
    }
    }
}
    