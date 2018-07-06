namespace Bullhorn\FastRest;

use Phalcon\DI\FactoryDefault;
use Phalcon\DiInterface;
//To use this, you must also implement \Phalcon\DI\InjectionAwareInterface
final class DependencyInjectionHelper
{
    /** @var  DiInterface */
    protected static di;
    /**
     * Gets the dependency injector
     * @return DiInterface
     */
    public static function getDi() -> <DiInterface>
    {
        if is_null(self::di) {
            let self::di =  FactoryDefault::getDefault();
            if is_null(self::di) {
                let self::di =  new FactoryDefault();
            }
        }
        return self::di;
    }

    /**
     * Sets the dependency injector
     * We cannot strict type the variable
     * @param DiInterface $di
     *
     * @return void
     */
    public static function setDi(<DiInterface> di)
    {
        let self::di = di;
    }

}