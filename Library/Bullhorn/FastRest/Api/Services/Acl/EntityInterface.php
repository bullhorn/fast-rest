<?php
namespace Bullhorn\FastRest\Api\Services\Acl;

interface EntityInterface {

    /**
     * Fires an event, implicitly calls behaviors and listeners in the events manager are notified This method stops if one of the callbacks/listeners returns boolean false
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function fireEventCancel($eventName);

    /**
     * Returns all the validation messages
     *
     *<code>
     *    $robot = new Robots();
     *    $robot->type = 'mechanical';
     *    $robot->name = 'Astro Boy';
     *    $robot->year = 1952;
     *    if ($robot->save() == false) {
     *    foreach ($robot->getMessages() as $message) {
     *            echo $message;
     *        }
     *    } else {
     *    echo "Great, a new robot was saved successfully!";
     *    }
     * </code>
     * @return \Phalcon\Mvc\Model\MessageInterface[]
     */
    public function getMessages();
}