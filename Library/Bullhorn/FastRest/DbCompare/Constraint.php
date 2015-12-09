<?php
namespace Bullhorn\FastRest\DbCompare;

use Bullhorn\FastRest\Base;

class Constraint extends Base {
    /** @var  string */
    private $name;
    /** @var  string[] */
    private $localColumns;
    /** @var  string */
    private $remoteTable;
    /** @var  string[] */
    private $remoteColumns;
    /** @var  string */
    private $deleteAction = 'RESTRICT';
    /** @var  string */
    private $updateAction = 'RESTRICT';

    /**
     * Constructor
     * @param string $rawString
     */
    public function __construct($rawString) {
        $this->build($rawString);
    }

    /**
     * Compares two keys, and returns any errors of non matches
     *
     * @param Constraint $constraint
     * @param string $prefix
     *
     * @return string[]
     * @throws \Exception
     */
    public function equals(Constraint $constraint, $prefix) {
        $prefix .= ': Constraint (' . $this->getName() . '):';
        $errors = [];
        if($constraint->getName() !== $this->getName()) {
            throw new \Exception('Comparing Incomparable Constraints');
        }
        if($constraint->getRemoteTable() !== $this->getRemoteTable()) {
            $errors[] = $prefix . 'Remote Table Does Not Match:' . "\n" . $constraint->getRemoteTable() . "\n" . $this->getRemoteTable();
        }
        $diff = array_diff($constraint->getLocalColumns(), $this->getLocalColumns());
        if(!empty($diff)) {
            $errors[] = $prefix . 'Extra Local Columns: ' . "\n" . implode("\n", $diff);
        }
        $diff = array_diff($this->getLocalColumns(), $constraint->getLocalColumns());
        if(!empty($diff)) {
            $errors[] = $prefix . 'Missing Local Columns: ' . "\n" . implode("\n", $diff);
        }
        $diff = array_diff($constraint->getRemoteColumns(), $this->getRemoteColumns());
        if(!empty($diff)) {
            $errors[] = $prefix . 'Extra Remote Columns: ' . "\n" . implode("\n", $diff);
        }
        $diff = array_diff($this->getRemoteColumns(), $constraint->getRemoteColumns());
        if(!empty($diff)) {
            $errors[] = $prefix . 'Missing Remote Columns: ' . "\n" . implode("\n", $diff);
        }
        if($constraint->getUpdateAction() !== $this->getUpdateAction()) {
            $errors[] = $prefix . 'Update Action Does Not Match:' . "\n" . $constraint->getUpdateAction() . "\n" . $this->getUpdateAction();
        }
        if($constraint->getDeleteAction() !== $this->getDeleteAction()) {
            $errors[] = $prefix . 'Delete Action Does Not Match:' . "\n" . $constraint->getDeleteAction() . "\n" . $this->getDeleteAction();
        }
        return $errors;
    }

    /**
     * parses the raw string and builds the parts
     *
     * @param string $rawString
     *
     * @return void
     * @throws \Exception
     */
    private function build($rawString) {
        $onDelete = '( ON DELETE (?P<onDelete>NO ACTION|RESTRICT|CASCADE|SET NULL))?';
        $onUpdate = '( ON UPDATE (?P<onUpdate>NO ACTION|RESTRICT|CASCADE|SET NULL))?';
        if(preg_match(
            '@^CONSTRAINT `(?P<name>[^`]+)` FOREIGN KEY \((?P<localColumns>[^\)]+)\) REFERENCES `(?P<remoteTable>[^`]+)` \((?P<remoteColumns>[^\)]+)\)' . $onDelete . $onUpdate . '$@',
            $rawString,
            $matches
        )) {
            $this->setName($matches['name']);
            $this->setLocalColumns(explode('`,`', trim($matches['localColumns'], '`')));
            $this->setRemoteTable($matches['remoteTable']);
            $this->setRemoteColumns(explode('`,`', trim($matches['remoteColumns'], '`')));
            if(isset($matches['onDelete'])) {
                $this->setDeleteAction($matches['onDelete']);
            }
            if(isset($matches['onUpdate'])) {
                $this->setUpdateAction($matches['onUpdate']);
            }
        }
    }

    /**
     * Getter
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Setter
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Getter
     * @return \string[]
     */
    public function getLocalColumns() {
        return $this->localColumns;
    }

    /**
     * Setter
     * @param \string[] $localColumns
     */
    public function setLocalColumns(array $localColumns) {
        $this->localColumns = $localColumns;
    }

    /**
     * Getter
     * @return string
     */
    public function getRemoteTable() {
        return $this->remoteTable;
    }

    /**
     * Setter
     * @param string $remoteTable
     */
    public function setRemoteTable($remoteTable) {
        $this->remoteTable = $remoteTable;
    }

    /**
     * Getter
     * @return \string[]
     */
    public function getRemoteColumns() {
        return $this->remoteColumns;
    }

    /**
     * Setter
     * @param \string[] $remoteColumns
     */
    public function setRemoteColumns(array $remoteColumns) {
        $this->remoteColumns = $remoteColumns;
    }

    /**
     * Getter
     * @return string
     */
    public function getDeleteAction() {
        return $this->deleteAction;
    }

    /**
     * Setter
     * @param string $deleteAction
     */
    public function setDeleteAction($deleteAction) {
        $this->deleteAction = $deleteAction;
    }

    /**
     * Getter
     * @return string
     */
    public function getUpdateAction() {
        return $this->updateAction;
    }

    /**
     * Setter
     * @param string $updateAction
     */
    public function setUpdateAction($updateAction) {
        $this->updateAction = $updateAction;
    }


}