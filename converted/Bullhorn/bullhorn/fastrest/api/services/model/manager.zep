namespace Bullhorn\FastRest\Api\Services\Model;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager as ModelManager;
class Manager extends ModelManager
{
    /**
     * clearReusableForModel
     * @param Model $model
     * @return void
     */
    public function clearReusableForModel(<Model> model)
    {
        var values, className, key, value;
    
        let values =  this->_reusable;
        if is_null(values) {
            return;
        }
        let className =  get_class(model);
        for key, value in values {
            if strpos(key, className) === 0 {
                unset values[key];
            
            }
        }
        let this->_reusable = values;
    }

}