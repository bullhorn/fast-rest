<?php
namespace Bullhorn\FastRest\Generator\Database;

use Bullhorn\FastRest\Generator\Configuration;
use Bullhorn\FastRest\Generator\PluralHelper;

class Relationship {
    /** @var  string */
    private $localColumn;
    /** @var string */
    private $remoteTable;
    /** @var string */
    private $remoteColumn;
    /** @var string */
    private $remoteModel;
    /** @var string */
    private $relationshipType;
    /** @var string */
    private $action;
    /** @var  string */
    private $localTable;
    /** @var  Relationship[] */
    private $relationships;
    /** @var  bool */
    private $nullable = false;

    /**
     * Constructor
     * @param Configuration $configuration
     * @param string $localTable
     * @param string $localColumn
     * @param string $remoteTable
     * @param string $remoteColumn
     * @param string $type
     * @param string $rawChanges
     */
    public function __construct(Configuration $configuration, $localTable, $localColumn, $remoteTable, $remoteColumn, $type, $rawChanges) {
        $this->setLocalTable($localTable);
        $this->setLocalColumn($localColumn);
        $this->setRemoteTable($remoteTable);
        $this->setRemoteColumn($remoteColumn);
        $this->setRemoteModel(
            $configuration->getRootNamespace()
            . '\Models\\' . $configuration->getModelSubNamespace()
            . '\\' . ucfirst($this->getRemoteTable())
        );
        $this->setRelationshipType($type);
        $this->setAction('NO_ACTION');
        if(preg_match_all('@ON (?P<actionType>(DELETE|UPDATE)+) (?P<action>(SET NULL|NO ACTION|CASCADE))@', $rawChanges, $subMatches)) {
            $length = sizeOf($subMatches[0]);
            $changes = array();
            for($i = 0; $i < $length; $i++) {
                $changes[$subMatches['actionType'][$i]] = $subMatches['action'][$i];
            }
            $action = 'NO_ACTION';
            if(isset($changes['DELETE']) && $changes['DELETE'] == 'SET NULL') {
                $this->setNullable(true);
                $action = 'NO_ACTION';
            } elseif(isset($changes['DELETE']) && $changes['DELETE'] == 'CASCADE') {
                $action = 'ACTION_CASCADE';
            }
            $this->setAction($action);
        }
    }

    /**
     * Getter
     * @return boolean
     */
    public function isNullable() {
        return $this->nullable;
    }

    /**
     * Setter
     * @param boolean $nullable
     */
    private function setNullable($nullable) {
        $this->nullable = $nullable;
    }


    /**
     * Getter
     * @return string
     */
    private function getLocalTable() {
        return $this->localTable;
    }

    /**
     * Setter
     * @param string $localTable
     */
    private function setLocalTable($localTable) {
        $this->localTable = $localTable;
    }

    /**
     * Getter
     * @return Relationship[]
     */
    private function getRelationships() {
        return $this->relationships;
    }

    /**
     * Setter
     * @param Relationship[] $relationships
     */
    public function setRelationships(array $relationships) {
        $this->relationships = $relationships;
    }


    /**
     * Gets the alias
     *
     * @return string
     */
    public function getAlias() {
        //Check and see if there is another relationship with the same name
        $multipleFound = false;
        foreach($this->getRelationships() as $relationship) {
            if($relationship !== $this && $relationship->getRemoteTable() == $this->getRemoteTable()) {
                $multipleFound = true;
            }
        }
        $returnVar = '';
        if($multipleFound) {
            if($this->isPlural()) {
                $name = $this->getRemoteColumn();
                //Strip out local table
                if(stripos($name, $this->getRemoteTable()) === 0) {
                    $name = substr($name, strlen($this->getRemoteTable()));
                }
                //Strip out remote table name, with id
                if(stripos(substr($name, strlen($this->getLocalTable() . 'Id') * -1), $this->getLocalTable() . 'Id') === 0) {
                    $name = substr($name, 0, strlen($this->getLocalTable() . 'Id') * -1);
                }
            } else {
                $name = $this->getLocalColumn();
                //Strip out local table
                if(stripos($name, $this->getLocalTable()) === 0) {
                    $name = substr($name, strlen($this->getLocalTable()));
                }
                //Strip out remote table name, with id
                if(stripos(substr($name, strlen($this->getRemoteTable() . 'Id') * -1), $this->getRemoteTable() . 'Id') === 0) {
                    $name = substr($name, 0, strlen($this->getRemoteTable() . 'Id') * -1);
                }
            }
            $returnVar .= $name;
        }
        $returnVar .= $this->getRemoteShortModel();
        if($this->isPlural()) {
            $pluralHelper = new PluralHelper();
            $returnVar = $pluralHelper->pluralize($returnVar);
        }
        return $returnVar;
    }

    /**
     * Getter
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Setter
     * @param string $action
     */
    private function setAction($action) {
        $this->action = $action;
    }


    /**
     * Getter
     * @return string
     */
    public function getLocalColumn() {
        return $this->localColumn;
    }

    /**
     * Setter
     * @param string $localColumn
     */
    private function setLocalColumn($localColumn) {
        $this->localColumn = $localColumn;
    }

    /**
     * Getter
     * @return string
     */
    public function getRemoteTable() {
        return $this->remoteTable;
    }

    /**
     * Gets if the relationshipType is plural
     * @return bool
     */
    public function isPlural() {
        return $this->getRelationshipType() == 'hasMany';
    }

    /**
     * Setter
     * @param string $remoteTable
     */
    private function setRemoteTable($remoteTable) {
        $this->remoteTable = $remoteTable;
    }

    /**
     * Gets the shortened version of a column
     * @return string
     */
    public function getRemoteShortColumn() {
        $name = $this->getRemoteColumn();
        //Strip out the table name
        if(substr($name, 0, strlen($this->getRemoteTable())) == $this->getRemoteTable() && lcfirst(substr($name, strlen($this->getRemoteTable()))) != 'source') {
            $name = lcfirst(substr($name, strlen($this->getRemoteTable())));
        }
        return $name;
    }

    /**
     * Getter
     * @return string
     */
    public function getRemoteColumn() {
        return $this->remoteColumn;
    }

    /**
     * Setter
     * @param string $remoteColumn
     */
    private function setRemoteColumn($remoteColumn) {
        $this->remoteColumn = $remoteColumn;
    }

    /**
     * Getter
     * @return string
     */
    public function getRemoteModel() {
        return $this->remoteModel;
    }

    /**
     * Gets just the shortened name, without the namespace
     * @return string
     */
    public function getRemoteShortModel() {
        return ucfirst($this->getRemoteTable());
    }

    /**
     * Setter
     * @param string $remoteModel
     */
    private function setRemoteModel($remoteModel) {
        $this->remoteModel = trim($remoteModel, '\\');
    }

    /**
     * Getter
     * @return string
     */
    public function getRelationshipType() {
        return $this->relationshipType;
    }

    /**
     * Setter
     * @param string $relationshipType
     */
    private function setRelationshipType($relationshipType) {
        $this->relationshipType = $relationshipType;
    }


}