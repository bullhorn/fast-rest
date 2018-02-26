<?php
namespace Bullhorn\FastRest\Generator;

use Bullhorn\FastRest\DbCompare\Table;
use Bullhorn\FastRest\Generator\Database\Field;
use Bullhorn\FastRest\Generator\Database\Index;
use Bullhorn\FastRest\Generator\Database\Relationship;
use Phalcon\Db\Result\Pdo as ResultSet;

class ModelBuilder {
    /** @var  Configuration */
    private $configuration;
    /** @var  string */
    private $tableName;
    /** @var  Field[] */
    private $fields;
    /** @var  Relationship[] */
    private $relationships;
    /** @var  Object\Index */
    private $abstractClass;
    /** @var  Object\Index */
    private $testClass;
    /** @var Index[] */
    private $indexes;
    /** @var  string */
    private $comment;
    /** @var  Object\Index */
    private $validationClass;
    /** @var  Object\Index */
    private $validationChildClass;

    /**
     * Constructor
     * @param Configuration $configuration
     * @param string $tableName
     */
    public function __construct(Configuration $configuration, $tableName) {
        $this->setConfiguration($configuration);
        $this->setTableName($tableName);
        $this->setAbstractClass(new Object\Index($this->getConfiguration()));
        $this->setTestClass(new Object\Index($this->getConfiguration()));

        $this->buildFields();
        $this->buildRelationships();
        $this->buildIndexes();
        $this->buildTableComment();

        $this->build();
    }

    /**
     * Getter
     * @return Object\Index
     */
    public function getValidationClass() {
        return $this->validationClass;
    }

    /**
     * Setter
     * @param Object\Index $validationClass
     */
    public function setValidationClass(Object\Index $validationClass) {
        $this->validationClass = $validationClass;
    }

    /**
     * Getter
     * @return Object\Index
     */
    public function getValidationChildClass() {
        return $this->validationChildClass;
    }

    /**
     * Setter
     * @param Object\Index $validationChildClass
     */
    public function setValidationChildClass(Object\Index $validationChildClass) {
        $this->validationChildClass = $validationChildClass;
    }

    /**
     * Getter
     * @return string
     */
    private function getTableName() {
        return $this->tableName;
    }

    /**
     * Setter
     * @param string $tableName
     */
    private function setTableName($tableName) {
        $this->tableName = $tableName;
    }


    /**
     * Getter
     * @return Configuration
     */
    private function getConfiguration() {
        return $this->configuration;
    }

    /**
     * Setter
     * @param Configuration $configuration
     */
    private function setConfiguration(Configuration $configuration) {
        $this->configuration = $configuration;
    }


    /**
     * Builds the table comment
     * @return void
     */
    private function buildTableComment() {
        /** @var ResultSet $results */
        $results = $this->getConfiguration()->getConnection()->query('SHOW TABLE STATUS WHERE Name="' . $this->getTableName() . '"');
        $result = (object)$results->fetch();
        $this->setComment($result->Comment);
    }

    /**
     * Gets the table comment
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Sets the table comment
     * @param string $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }


    /**
     * Getter
     * @return Index[]
     */
    public function getIndexes() {
        return $this->indexes;
    }

    /**
     * Setter
     * @param Index[] $indexes
     */
    private function setIndexes(array $indexes) {
        $this->indexes = $indexes;
    }

    /**
     * Builds the indexes
     * @return void
     */
    private function buildIndexes() {
        $indexes = array();
        /** @var ResultSet $results */
        $results = $this->getConfiguration()->getConnection()->query('SHOW INDEX FROM `' . $this->getTableName() . '`');
        while($result = $results->fetch()) {
            $result = (object)$result;
            if(!array_key_exists($result->Key_name, $indexes)) {
                $indexes[$result->Key_name] = new Index();
            }
            /** @var Index $index */
            $index = $indexes[$result->Key_name];
            $index->setUnique(!$result->Non_unique);
            $index->addColumn($result->Column_name);
            $index->setPrimary($result->Key_name == 'PRIMARY');
        }
        $this->setIndexes($indexes);
    }

    /**
     * Getter
     * @return Object\Index
     */
    private function getTestClass() {
        return $this->testClass;
    }

    /**
     * Setter
     * @param Object\Index $testClass
     */
    private function setTestClass($testClass) {
        $this->testClass = $testClass;
    }


    /**
     * Getter
     * @return Object\Index
     */
    public function getAbstractClass() {
        return $this->abstractClass;
    }

    /**
     * Setter
     * @param Object\Index $abstractClass
     */
    private function setAbstractClass($abstractClass) {
        $this->abstractClass = $abstractClass;
    }

    /**
     * Writes all of the models
     *
     * @param Configuration $configuration
     * @param string $ignoredTablesRegex
     *
     * @return void
     */
    public static function writeAll(Configuration $configuration, $ignoredTablesRegex = null) {
        $results = $configuration->getConnection()->query('SHOW FULL TABLES WHERE Table_Type!="VIEW"');
        while($result = $results->fetch()) {
            $result = (object)$result;
            $table = null;
            foreach($result as $column) {
                $table = $column;
                break;
            }
            if(!is_null($ignoredTablesRegex) && preg_match($ignoredTablesRegex, $table)) {
                continue;
            }
            echo 'Writing: '.$table."\n";
            $index = new self($configuration, $table);
            $index->write();
        }
    }

    /**
     * Getter
     * @return Relationship[]
     */
    public function getRelationships() {
        return $this->relationships;
    }

    /**
     * Setter
     * @param Relationship[] $relationships
     */
    private function setRelationships($relationships) {
        $this->relationships = $relationships;
    }

    /**
     * Getter
     * @return Field[]
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Setter
     * @param Field[] $fields
     */
    private function setFields(array $fields) {
        $this->fields = $fields;
    }

    /**
     * getCurrentDatabaseName
     * @return string
     */
    private function getCurrentDatabaseName() {
        return $this->getConfiguration()->getConnectionDescriptor()['dbname'];
    }

    /**
     * {REPLACE_ME!}
     * @return string
     * @throws \Exception
     */
    public function buildFieldsTypes() {
        $this->getAbstractClass()->addUse('Bullhorn\FastRest\Api\Models\ApiInterface');

        $content = 'return [';
        foreach($this->getFields() as $key => $field) {
            if($key > 0) {
                $content .= ',';
            }

            $constantName = 'FIELD_TYPE_' . strtoupper(preg_replace('@([A-Z])@', '_\\0', lcfirst($field->getType())));
            $content .= "\n\t\t\t" . '\'' . $field->getShortName() . '\'=>ApiInterface::' . $constantName;
        }
        $content .= "\n\t\t" . '];';

        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('getFieldTypes');
        $method->setReturnType('string[]');
        $method->setContent($content);
        $this->getAbstractClass()->addMethod($method);

        return $content;
    }

    /**
     * Builds the fields and types
     * @return void
     */
    private function buildFields() {
        $fields = array();
        /** @var ResultSet $results */
        $results = $this->getConfiguration()->getConnection()->query('SHOW COLUMNS FROM `' . $this->getTableName() . '`');
        $dbName = $this->getCurrentDatabaseName();
        while($result = $results->fetch()) {
            $result = (object)$result;
            $params = array($dbName, $this->getTableName(), $result->Field);
            /** @var ResultSet $informationResults */
            $informationResults = $this->getConfiguration()->getInformationSchemaConnection()->query(
                'SELECT * FROM `COLUMNS` WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?',
                $params
            );
            /** @var \stdClass $informationResult */
            $informationResult = (object)$informationResults->fetch();
            $field = new Field($result, $this->getTableName(), $informationResult);
            switch($field->getType()) {
                case 'Date':
                    $this->getAbstractClass()->addUse($this->getConfiguration()->getDateClassName());
                    $this->getTestClass()->addUse($this->getConfiguration()->getDateClassName());
                    break;
                case 'DateTime':
                    $this->getAbstractClass()->addUse($this->getConfiguration()->getDateTimeClassName());
                    $this->getTestClass()->addUse($this->getConfiguration()->getDateTimeClassName());
                    break;
            }
            $fields[] = $field;
        }
        usort(
            $fields,
            function (Field $fieldA, Field $fieldB) {
                return strcasecmp($fieldA->getShortName(), $fieldB->getShortName());
            }
        );
        $this->setFields($fields);
    }

    /**
     * Builds the validation class, returns what needs to be added to the initializer
     * @return string
     */
    private function buildValidation() {
        $validation = new Object\Index($this->getConfiguration());
        $validation->setNamespace(
            $this->getConfiguration()->getRootNamespace()
            . '\Services\Behavior\\' . $this->getConfiguration()->getModelSubNamespace()
            . '\Generated'
        );
        $validation->setName($this->getAbstractClass()->getName());
        $validation->setAbstract(true);
        $validation->setExtends('BehaviorBase');
        $validation->addUse(str_replace('\Generated', '', $this->getAbstractClass()->getNamespace()) . '\\' . $this->getAbstractClass()->getName() . ' as Model');
        $validation->addUse('Phalcon\Mvc\Model\Message');
        $validation->addUse('Bullhorn\FastRest\Api\Services\Behavior\BehaviorBase');


        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('getEntity');
        $method->setReturnType('Model');
        $method->setContent('return parent::getEntity();');
        $validation->addMethod($method);


        //Before Delete Method
        $content = '';
        foreach($this->getRelationships() as $relationship) {
            if($relationship->isPlural() && !$relationship->isNullable() && $relationship->getAction() != 'ACTION_CASCADE') {
                $content .= 'parent::beforeDelete();
        if($this->isUnitTestingChildren()) {
            return;
        }
        $entity = $this->getEntity();
        if ($entity->count' . ucfirst($relationship->getAlias()) . '()>0) {
			$entity->appendMessage(new Message(\'Multiple ' . ucfirst($relationship->getAlias()) . ' found\'));
		}';
            }
        }
        $method = new Object\Method();
        $method->setAccess('protected');
        $method->setDescription('Validates if the entity can be deleted');
        $method->setReturnType('void');
        $method->setName('beforeDelete');
        $method->setContent($content);
        $validation->addMethod($method);
        //End Before Delete Method


        //Validation Method
        $content = 'parent::validation();
        if($this->isUnitTestingChildren()) {
            return;
        }
        $entity = $this->getEntity();
        //Check to see if it is automatically updated
		$automaticAttributes = $entity->getModelsMetaData()->getAutomaticCreateAttributes($entity);
		$columnMap = $entity->getModelsMetaData()->getColumnMap($entity);
		$automaticFields = array_fill_keys($entity->getAutomaticallyUpdatedFields(), null);
		foreach($automaticAttributes as $fullName=>$null) {
			$automaticFields[$columnMap[$fullName]] = null;
		}
';
        foreach($this->getFields() as $field) {
            if(!is_null($field->getEnumOptions())) {
                $validation->addUse('Phalcon\Mvc\Model\Validator\Inclusionin as ValidateEnum');
                $subMethod = new Object\Method();
                $subMethod->setAccess('private');
                $subMethod->setDescription('Validates ' . $field->getShortName() . ' is in the enum list');
                $subMethod->setReturnType('void');
                $subMethod->setName('validate' . ucfirst($field->getShortName()) . 'Enum');
                $subMethod->setContent(
                    '$this->validate(
			new ValidateEnum(
				array(
					\'field\' => \'' . $field->getShortName() . '\',
					\'domain\' => [\'' . implode('\',\'', $field->getEnumOptions()) . '\']
				)
			)
		);'
                );
                $validation->addMethod($subMethod);
                $content .= '		$this->validate' . ucfirst($field->getShortName()) . 'Enum();
';
            }
            if(!$field->isNullable()) {
                $subMethod = new Object\Method();
                $subMethod->setAccess('private');
                $subMethod->setDescription('Validates ' . $field->getShortName() . ' is not null');
                $subMethod->setReturnType('void');
                $subMethod->setName('validate' . ucfirst($field->getShortName()) . 'NotNull');
                $parameter = new Object\Parameter();
                $parameter->setName('entity');
                $parameter->setType('Model');
                $parameter->setStrictType(true);
                $subMethod->addParameter($parameter);
                $parameter = new Object\Parameter();
                $parameter->setName('automaticFields');
                $parameter->setType('array');
                $parameter->setStrictType(true);
                $subMethod->addParameter($parameter);
                $subMethod->setContent(
                    'if(!array_key_exists(\'' . $field->getShortName() . '\', $automaticFields) && is_null($entity->readAttribute(\'' . $field->getShortName() . '\'))) {
			$entity->appendMessage(new Message(\'Null Error with the field: ' . $field->getShortname() . ', Expected Type: ' . $field->getType() . '\'));
		}'
                );
                $validation->addMethod($subMethod);
                $content .= '		$this->validate' . ucfirst($field->getShortName()) . 'NotNull($entity, $automaticFields);
';
            }
        }

        foreach($this->getIndexes() as $index) {
            if($index->isUnique() && !$index->isPrimary()) {
                $validation->addUse('Bullhorn\FastRest\Api\Services\Validator\Unique as ValidateUnique');
                $fields = array();
                foreach($index->getColumns() as $column) {
                    $fields[] = $this->getField($column)->getShortName();
                }
                $name = 'validate';
                foreach($fields as $field) {
                    $name .= ucfirst($field);
                }
                $name .= 'Unique';
                $subMethod = new Object\Method();
                $subMethod->setAccess('private');
                $subMethod->setDescription('Validates this combination of ' . implode(', ', $fields) . ' is unique in the database');
                $subMethod->setReturnType('void');
                $subMethod->setName($name);
                $subMethod->setContent(
                    '$this->validate(
			$validation = new ValidateUnique(
				[
					\'fields\'  => [\'' . implode('\',\'', $fields) . '\'],
					\'message\' => \'A unique constraint failed on: ' . implode(',', $fields) . '\'
				]
			)
		);'
                );
                $validation->addMethod($subMethod);
                $content .= '		$this->' . $name . '();
';
            }
        }

        foreach($this->getRelationships() as $relationship) {
            $field = $this->getField($relationship->getLocalColumn());
            if(!$relationship->isPlural()) {
                //Ex: Not Required relationship, make sure if the value isn't null, it is valid
                $subMethod = new Object\Method();
                $subMethod->setAccess('private');
                $subMethod->setDescription('Validates the ' . ucfirst($relationship->getAlias()) . ' Relationship');
                $subMethod->setReturnType('void');
                $subMethod->setName('validate' . ucfirst($relationship->getAlias()) . 'Relationship');
                $parameter = new Object\Parameter();
                $parameter->setName('entity');
                $parameter->setType('Model');
                $parameter->setStrictType(true);
                $subMethod->addParameter($parameter);
                $subMethod->setContent(
                    'if ($entity->get' . ucfirst($relationship->getAlias()) . '()===false '
                    . ($field->isNullable() ? '&& !is_null($entity->get' . ucfirst($field->getShortName()) . '())' : '') . ') {
			$entity->appendMessage(new Message(\'No ' . ucfirst($relationship->getAlias()) . ' found with the id \'.$entity->get' . ucfirst($field->getShortName()) . '()));
		}'
                );
                $validation->addMethod($subMethod);
                $content .= '		$this->validate' . ucfirst($relationship->getAlias()) . 'Relationship($entity);
';
            }
        }
        $method = new Object\Method();
        $method->setAccess('protected');
        $method->setDescription('Validates if the entity can be updated or inserted');
        $method->setReturnType('void');
        $method->setName('validation');
        $method->setContent($content);
        $validation->addMethod($method);
        //End Validation Method

        $this->setValidationClass($validation);

        $childValidation = new Object\Index($this->getConfiguration());
        $childValidation->setNamespace(
            $this->getConfiguration()->getRootNamespace()
            . '\Services\Behavior\\' . $this->getConfiguration()->getModelSubNamespace()
        );
        $childValidation->setName($this->getAbstractClass()->getName());
        $childValidation->setExtends('Generated\\' . $this->getAbstractClass()->getName());
        $childValidation->addUse(str_replace('\Generated', '', $this->getAbstractClass()->getNamespace()) . '\\' . $this->getAbstractClass()->getName() . ' as Model');

        $this->setValidationChildClass($childValidation);
        $this->getAbstractClass()->addUse($childValidation->getNamespace() . '\\' . $childValidation->getName() . ' as ModelValidator');
        return '		$this->addBehavior(new ModelValidator());' . "\n";
    }

    /**
     * Builds the relationships
     * @return void
     */
    private function buildRelationships() {
        /** @var Relationship[] $relationships */
        $relationships = array();
        $dbCompareTable = new Table($this->getConfiguration()->getConnection(), $this->getTableName());
        foreach($dbCompareTable->getConstraints() as $constraint) {
            $relationship = new Relationship(
                $this->getConfiguration(),
                $this->getTableName(),
                implode(', ', $constraint->getLocalColumns()),
                $constraint->getRemoteTable(),
                implode(', ', $constraint->getRemoteColumns()),
                'belongsTo',
                'ON DELETE ' . $constraint->getDeleteAction() . ' ON UPDATE ' . $constraint->getUpdateAction()
            );
            $this->getAbstractClass()->addUse('Phalcon\Mvc\Model\Relation');
            $this->getAbstractClass()->addUse($relationship->getRemoteModel() . ' as Child' . $relationship->getRemoteShortModel());
            $relationships[] = $relationship;
        }

        //Get One to Many relationships

        $database = $this->getCurrentDatabaseName();
        $sql = 'SELECT DISTINCT(TABLE_NAME) as tableName FROM information_schema.`KEY_COLUMN_USAGE` WHERE REFERENCED_TABLE_NAME="' . $this->getTableName() . '" AND REFERENCED_TABLE_SCHEMA="' . $database . '"';
        /** @var ResultSet $results */
        $foreignKeys = $this->getConfiguration()->getConnection()->query($sql);
        while($foreignKey = $foreignKeys->fetch()) {
            $foreignKey = (object)$foreignKey;
            $tableName = $foreignKey->tableName;

            $dbCompareTable = new Table($this->getConfiguration()->getConnection(), $tableName);
            foreach($dbCompareTable->getConstraints() as $constraint) {
                if($constraint->getRemoteTable() == $this->getTableName()) {
                    if(implode(', ', $constraint->getRemoteColumns())==='venueId`, `userID') {
                        exit;
                    }
                    $relationship = new Relationship(
                        $this->getConfiguration(),
                        $this->getTableName(),
                        implode(', ', $constraint->getRemoteColumns()),
                        $tableName,
                        implode(', ', $constraint->getLocalColumns()),
                        'hasMany',
                        'ON DELETE ' . $constraint->getDeleteAction() . ' ON UPDATE ' . $constraint->getUpdateAction()
                    );
                    $this->getAbstractClass()->addUse('Phalcon\Mvc\Model\Relation');
                    $this->getAbstractClass()->addUse($relationship->getRemoteModel() . ' as Child' . $relationship->getRemoteShortModel());
                    $this->getAbstractClass()->addUse('Phalcon\Mvc\Model\Relation');
                    $this->getAbstractClass()->addUse('Phalcon\Mvc\Model\Resultset\Simple as ResultSet');
                    $this->getAbstractClass()->addUse($relationship->getRemoteModel() . ' as Child' . $relationship->getRemoteShortModel());
                    $relationships[] = $relationship;
                }
            }

        }
        foreach($relationships as $relationship) {
            $relationship->setRelationships($relationships);
        }
        $this->setRelationships($relationships);
    }

    /**
     * Builds the relationship buffer
     * @return string
     */
    private function buildRelationshipsBuffer() {
        $buffer = '';
        foreach($this->getRelationships() as $relationship) {
            $buffer .= '		$this->' . $relationship->getRelationshipType() . '(
			\'' . $this->getField($relationship->getLocalColumn())->getShortName() . '\',
			\'' . $relationship->getRemoteModel() . '\',
			\'' . $relationship->getRemoteShortColumn() . '\',
			array(
				\'alias\'      => \'' . $relationship->getAlias() . '\',
				\'foreignKey\' => array(
					\'message\' => \'The ' . $this->getTableName() . ' cannot be deleted because other ' . $relationship->getRemoteTable() . '(s) are attached\',
					\'action\'  => Relation::' . $relationship->getAction() . '
				),
				\'reusable\' => true
			)
		);' . "\n";
        }
        return $buffer;
    }

    /**
     * Gets a field by it's original name
     *
     * @param string $columnName
     *
     * @return Field|bool
     */
    private function getField($columnName) {
        foreach($this->getFields() as $field) {
            if($field->getName() == $columnName) {
                return $field;
            }
        }
        echo 'Could Not Find Field '.$this->getTableName().'.'.$columnName."\n";
        return false;
    }


    /**
     * Builds the relationship getters
     * @return string
     */
    private function buildRelationshipGetters() {
        $buffer = '';
        foreach($this->getRelationships() as $relationship) {
            $field = $this->getField($relationship->getLocalColumn());
            if($relationship->isPlural()) {
                $method = new Object\Method();
                $method->setAccess('public');
                $method->setDescription('Gets the related ' . $relationship->getAlias());
                $method->setName('get' . ucfirst($relationship->getAlias()));
                $method->setReturnType('ResultSet|Child' . $relationship->getRemoteShortModel() . '[]');
                $parameter = new Object\Parameter();
                $parameter->setDescription('');
                $parameter->setName('parameters');
                $parameter->setDefaultValue('null');
                $parameter->setType('array');
                $method->addParameter($parameter);
                $method->setContent('return $this->getRelated(\'' . $relationship->getAlias() . '\', $parameters);');
                $this->getAbstractClass()->addMethod($method);

                $method = new Object\Method();
                $method->setAccess('public');
                $method->setDescription('Gets the count of related ' . $relationship->getAlias());
                $method->setName('count' . ucfirst($relationship->getAlias()));
                $method->setReturnType('int');
                $parameter = new Object\Parameter();
                $parameter->setDescription('');
                $parameter->setName('parameters');
                $parameter->setDefaultValue('null');
                $parameter->setType('array');
                $method->addParameter($parameter);
                $method->setContent('return $this->get' . ucfirst($relationship->getAlias()) . '($parameters)->count();');
                $this->getAbstractClass()->addMethod($method);
            } else {

                $method = new Object\Method();
                $method->setAccess('public');
                $method->setDescription('Gets the related ' . $relationship->getAlias());
                $method->setName('get' . ucfirst($relationship->getAlias()));
                if($this->getAbstractClass()->hasMethod($method->getName())) {
                    $method->setName('getRelated' . ucfirst($relationship->getAlias()));
                }
                $method->setReturnType('Child' . $relationship->getRemoteShortModel() . '|false');
                $method->setContent('return $this->getRelated(\'' . $relationship->getAlias() . '\');');
                $this->getAbstractClass()->addMethod($method);

                if($field->isNullable()) {
                    $method = new Object\Method();
                    $method->setAccess('public');
                    $method->setDescription('Checks if a relationship exists');
                    $method->setName('has' . ucfirst($relationship->getAlias()));
                    $method->setReturnType('bool');
                    $method->setContent('return !is_null($this->get' . ucfirst($relationship->getAlias()) . '());');
                    $this->getAbstractClass()->addMethod($method);
                }
            }
        }
        return $buffer;
    }

    /**
     * Builds the variable definitions
     * @return void
     */
    private function buildVariableDefinitions() {
        foreach($this->getFields() as $field) {
            $variable = new Object\Variable();
            $variable->setName($field->getShortName());
            $variable->setAccess('protected');
            $variable->setAutoIncrementing($field->isAutoIncrementing());
            $variable->setDescription($field->getDescription());
            $variable->setLength($field->getLength());
            $variable->setNullable($field->isNullable());
            $variable->setPrimary($field->isPrimary());
            $variable->setType($field->getType());
            $this->getAbstractClass()->addVariable($variable);
        }
    }

    /**
     * Builds the getters and setters
     * @return void
     */
    private function buildGettersAndSetters() {
        foreach($this->getFields() as $field) {
            $this->getAbstractClass()->addConstant('DB_COLUMN_'.strtoupper(preg_replace('@[A-Z]@', '_\\0', $field->getShortName())), $field->getShortName());
            switch($field->getType()) {
                case 'bool':
                    $rawTypes = array('TRUE', 'FALSE', 'NULL');
                    break;
                case 'int':
                    $rawTypes = array('0', '1', '-1');
                    break;
                case 'double':
                    $rawTypes = array('0.0', '0.5', '-0.5');
                    break;
                case 'string':
                    if(!is_null($field->getEnumOptions())) {
                        $rawTypes = $field->getEnumOptions();
                        foreach($rawTypes as $key => $value) {
                            $rawTypes[$key] = '\'' . $value . '\'';
                        }
                    } else {
                        $rawTypes = array('\'test\'', '\'!@#$\'', '\'\'');
                    }
                    break;
                case 'Date':
                    $date = 1422998909;
                    $rawTypes = array('\'' . date('Y-m-d', $date) . '\'', $date);
                    break;
                case 'DateTime':
                    $date = 1422998909;
                    $rawTypes = array('\'' . date('Y-m-d H:i:s', $date) . '\'', $date);
                    break;
                default:
                    throw new \Exception('Unexpected Type: ' . $field->getType());
                    break;
            }

            $content = 'if(is_null($this->' . $field->getShortName() . ')) {
			return null;
		}
';
            switch($field->getType()) {
                case 'Date':
                case 'DateTime':
                    $content .= '		return new ' . $field->getType() . '($this->' . $field->getShortName() . ');';
                    break;
                case 'bool':
                    $content .= '		return (bool)$this->' . $field->getShortName() . ';';
                    break;
                case 'int':
                    $content .= '		return (int)$this->' . $field->getShortName() . ';';
                    break;
                case 'double':
                    $content .= '		return (double)$this->' . $field->getShortName() . ';';
                    break;
                default:
                    $content .= '		return $this->' . $field->getShortName() . ';';
                    break;

            }
            $method = new Object\Method();
            $method->setAccess('public');
            $method->setDescription('Getter');
            $method->setName('get' . ucfirst($field->getShortName()));
            $method->setReturnType($field->getType() . ($field->isNullable() ? '|null' : ''));
            $method->setContent($content);
            $this->getAbstractClass()->addMethod($method);


            $content = '$reflectionClass = new \ReflectionClass($this->getModel());
		$reflectionProperty = $reflectionClass->getProperty(\'' . $field->getShortName() . '\');
		$reflectionProperty->setAccessible(TRUE);
';
            foreach($rawTypes as $rawType) {
                if(in_array($field->getType(), array('Date', 'DateTime'))) {
                    $content .= '		$expectedValue = new ' . $field->getType() . '(' . $rawType . ');
';
                } else {
                    $content .= '		$expectedValue = ' . $rawType . ';
';
                }
                $content .= '		$reflectionProperty->setValue($this->getModel(), ' . $rawType . ');
		$actualValue = $this->getModel()->get' . ucfirst($field->getShortName()) . '();
		$this->assertEquals($expectedValue, $actualValue);
';
            }
            $method = new Object\Method();
            $method->setAccess('public');
            $method->setName('testGet' . ucfirst($field->getShortName()));
            $method->setContent($content);
            $this->getTestClass()->addMethod($method);

            $method = new Object\Method();
            $method->setDescription('Setter');
            $parameter = new Object\Parameter();
            $parameter->setName($field->getShortName());
            $parameter->setDescription('');
            $parameter->setType((in_array($field->getType(), array('Date', 'DateTime')) ? 'int|string|' : '') . $field->getType());
            $parameter->setClass(in_array($field->getType(), array('Date', 'DateTime')));
            $method->addParameter($parameter);
            $method->setAccess('public');
            $method->setName('set' . ucfirst($field->getShortName()));
            $method->setReturnType('ChildModel');
            $content = 'if(is_object($' . $field->getShortName() . ')) {
			if(method_exists($' . $field->getShortName() . ', \'__toString\')) {
				$' . $field->getShortName() . ' = $' . $field->getShortName() . '->__toString();
			} else {
				throw new \InvalidArgumentException(\'An Object without __toString is not a valid parameter: \'.get_class($' . $field->getShortName() . '));
			}
		} elseif(is_array($' . $field->getShortName() . ')) {
			throw new \InvalidArgumentException(\'An Array is not a valid parameter: \'.print_r($' . $field->getShortName() . ', true));
		}
';

            switch($field->getType()) {
                case 'int':
                    if($field->isNullable()) {
                        $content .= '		$' . $field->getShortName() . ' = $this->getFilter()->sanitize($' . $field->getShortName() . ', \'nullify\');
';
                    }
                    $content .= '		$' . $field->getShortName() . ' = $this->getFilter()->sanitize($' . $field->getShortName() . ', \'int\');
';
                    break;
                case 'bool':
                    $content = $this->buildSetterBoolContent($field, $content);
                    break;
                case 'double':
                    if($field->isNullable()) {
                        $content .= '		$' . $field->getShortName() . ' = $this->getFilter()->sanitize($' . $field->getShortName() . ', \'nullify\');
';
                    }
                    $content .= '		$' . $field->getShortName() . ' = $this->getFilter()->sanitize($' . $field->getShortName() . ', \'float\');
';
                    break;
                case 'Date':
                    $content .= '		if(!is_string($' . $field->getShortName() . ') || !preg_match(\'@^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$@\', $' . $field->getShortName() . ')) {
			$date = new ' . $field->getType() . '($' . $field->getShortName() . ');
			$' . $field->getShortName() . ' = $date->__toString();
		}
';
                    break;
                case 'DateTime':
                    $content .= '		if(!is_string($' . $field->getShortName() . ') || !preg_match(\'@^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$@\', $' . $field->getShortName() . ')) {
			$date = new ' . $field->getType() . '($' . $field->getShortName() . ');
			$' . $field->getShortName() . ' = $date->__toString();
		}
';
                    break;
            }
            if(in_array($field->getType(), ['int', 'double']) && $field->isNullable()) {
                $content .= '		if($' . $field->getShortName() . '==\'\' || strtolower($' . $field->getShortName() . ')==\'null\') {
			$' . $field->getShortName() . ' = null;
		}
';
            }
            $content .= '		$this->' . $field->getShortName() . ' = $' . $field->getShortName() . ';
		return $this;';
            $method->setContent($content);
            $this->getAbstractClass()->addMethod($method);


            $content = '$reflectionClass = new \ReflectionClass($this->getModel());
		$reflectionProperty = $reflectionClass->getProperty(\'' . $field->getShortName() . '\');
		$reflectionProperty->setAccessible(TRUE);
';
            foreach($rawTypes as $rawType) {
                if(in_array($field->getType(), array('Date', 'DateTime'))) {
                    $content .= '		$expectedValue = new ' . $field->getType() . '(' . $rawType . ');
		$expectedValue = $expectedValue->__toString();
';
                } else {
                    $content .= '		$expectedValue = ' . $rawType . ';
';
                }
                $content .= '		$returnValue = $this->getModel()->set' . ucfirst($field->getShortName()) . '(' . $rawType . ');
		$this->assertSame($returnValue, $this->getModel());
		$actualValue = $reflectionProperty->getValue($this->getModel());
		$this->assertEquals($expectedValue, $actualValue);
';
            }
            $method = new Object\Method();
            $method->setAccess('public');
            $method->setName('testSet' . ucfirst($field->getShortName()));
            $method->setContent($content);
            $this->getTestClass()->addMethod($method);

        }
    }

    /**
     * Builds the column maps
     * @return void
     */
    private function buildColumnMap() {
        $content = '		return $this->columnMapMissingColumnsFix(array(' . "\n";
        foreach($this->getFields() as $field) {
            $content .= '			\'' . $field->getName() . '\' => \'' . $field->getShortName() . '\',' . "\n";
        }
        $content = substr($content, 0, -2) . "\n"; //Remove trailing comma
        $content .= '		));';
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setDescription('Updates so we can use the shortened names, without changing the database');
        $method->setName('columnMap');
        $method->setReturnType('string[] keys are the real names, values are the names in the application');
        $method->setContent($content);
        $this->getAbstractClass()->addMethod($method);

        $this->getAbstractClass()->addUse('Phalcon\Db\Column');
        $content = '		return [';
        foreach($this->getFields() as $field) {
            $content .= '			\'' . $field->getShortName() . '\' => Column::' . $field->getPhalconColumnType() . ',' . "\n";
        }
        $content .= '       ];';
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setDescription('Used for determining the database type');
        $method->setName('getDatabaseTypes');
        $method->setReturnType('string[] keys are the column names, values are the DB\Column');
        $method->setContent($content);
        $this->getAbstractClass()->addMethod($method);
    }

    /**
     * Builds the relationships list methods
     * @return void
     */
    private function buildRelationshipsList() {
        $content = '		return array(' . "\n";
        $added = false;
        foreach($this->getRelationships() as $relationship) {
            if(!$relationship->isPlural()) {
                $added = true;
                $content .= '			\'' . $relationship->getAlias() . '\',' . "\n";
            }
        }
        if($added) {
            $content = substr($content, 0, -2) . "\n"; //Remove trailing comma
        }
        $content .= '		);';
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setDescription('Returns a list of all parent relationships, these will all return a Base Model instance');
        $method->setName('getParentRelationships');
        $method->setReturnType('string[] values are the alias of the relationship');
        $method->setContent($content);
        $this->getAbstractClass()->addMethod($method);

        $content = '		return array(' . "\n";
        $added = false;
        foreach($this->getRelationships() as $relationship) {
            if($relationship->isPlural()) {
                $added = true;
                $content .= '			\'' . $relationship->getAlias() . '\',' . "\n";
            }
        }
        if($added) {
            $content = substr($content, 0, -2) . "\n"; //Remove trailing comma
        }
        $content .= '		);';
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setDescription('Returns a list of all children relationships, these will all return a ResultSet');
        $method->setName('getChildrenRelationships');
        $method->setReturnType('string[] values are the alias of the relationship');
        $method->setContent($content);
        $this->getAbstractClass()->addMethod($method);

    }

    /**
     * filterConstant
     * @param string $name
     * @return string
     */
    private function filterConstant($name) {
        $name = str_replace(' ', '_', $name);
        $name = preg_replace_callback(
            '@[A-Z][a-z]@',
            function ($matches) {
                return '_' . $matches[0];
            },
            $name
        );


        $name = str_replace(
            array(
                '/',
                '-',
                ',',
                '|',
                "\t"
            ),
            array(
                '_SLASH_',
                '_DASH_',
                '_COMMA_',
                '_PIPE_',
                '_TAB_'
            ),
            $name
        );
        $name = trim($name, '_');
        $name = preg_replace('@_+@', '_', $name);
        $name = preg_replace('@[^a-zA-Z0-9_]@', '', $name); //Strip out bad characters
        $name = strtoupper($name);
        if(is_numeric($name)) {
            $name = 'VALUE_'.$name;
        }
        return $name;
    }

    /**
     * Builds the enum constants
     * @return void
     * @throws \Exception
     */
    private function buildEnumConstants() {
        foreach($this->getFields() as $field) {
            $options = $field->getEnumOptions();


            if(!is_null($options)) {
                $enumClass = new Object\Index($this->getConfiguration());
                $enumClass->setNamespace($this->getAbstractClass()->getNamespace().'\\'.$this->getAbstractClass()->getName());
                $enumClass->setName(ucfirst($field->getShortName()));
                $enumClass->addUse('Bullhorn\FastRest\Api\Services\SplEnum');
                $enumClass->setExtends('SplEnum');
                foreach($options as $option) {
                    $optionName = lcfirst($option);
                    if($optionName == '') {
                        $optionName = 'EMPTY';
                    }
                    $this->getAbstractClass()->addConstant($this->filterConstant($field->getShortName() . '_' . $optionName), $option);
                    $enumClass->addConstant($this->filterConstant($optionName), $option);
                }
                $enumClass->write();
            }
        }
    }

    private function buildUpdateSkips() {
        $content = '';

        foreach($this->getIndexes() as $index) {
            if($index->isPrimary()) {
                $fields = array();
                foreach($index->getColumns() as $column) {
                    $fields[] = $this->getField($column)->getShortName();
                }
                $content .= '		//Do not update primary keys
		$this->skipAttributes([\'' . implode('\',\'', $fields) . '\']);
';
            }
        }
        return $content;
    }

    /**
     * Builds the on construct method
     * @return void
     * @throws \Exception
     */
    private function buildOnConstruct() {
        $content = '';
        foreach($this->getFields() as $field) {
            if(in_array($field->getType(), array('Date', 'DateTime'))) {
                $content .= '		$this->set' . ucfirst($field->getShortName()) . '(new ' . $field->getType() . '($this->get' . ucfirst($field->getShortName()) . '()));
';
            }
        }
        $content .= '		parent::onConstruct();';
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setDescription('Used to Construct an instance');
        $method->setName('onConstruct');
        $method->setReturnType('void');
        $method->setContent($content);
        $this->getAbstractClass()->addMethod($method);
    }

    /**
     * Builds the initial setup of the test class
     * @return void
     * @throws \Exception
     */
    private function buildTestSetup() {
        $this->getTestClass()->addUse('Bullhorn\FastRest\UnitTestHelper\MockDbAdapter');
        $variable = new Object\Variable();
        $variable->setName('model');
        $variable->setType($this->getAbstractClass()->getName());
        $this->getTestClass()->addVariable($variable);

        $method = new Object\Method();
        $method->setName('getModel');
        $method->setReturnType($this->getAbstractClass()->getName());
        $method->setContent('return $this->model;');
        $this->getTestClass()->addMethod($method);

        $method = new Object\Method();
        $method->setName('getDbMock');
        $method->setReturnType('MockDbAdapter');
        $method->setContent('return $this->getDi()->get($this->getConnectionService());');
        $this->getTestClass()->addMethod($method);

        $method = new Object\Method();
        $method->setName('setUp');
        $content = '$this->setModelSubNamespace(\'' . $this->getConfiguration()->getModelSubNamespace() . '\');
		$this->setConnectionService(\'' . $this->getConfiguration()->getConnectionService() . '\');
		$this->setPhalconHelperNamespace(\'' . $this->getConfiguration()->getRootNamespace() . '\PhalconHelper\');
		parent::setUp();
		$this->model = $this->getMockForAbstractClass(\'' . $this->getAbstractClass()->getNamespace() . '\\' . $this->getAbstractClass()->getName() . '\', [$this->getDi()]);';
        $method->setContent($content);
        $this->getTestClass()->addMethod($method);
    }

    /**
     * Initialize Method
     * @return void
     * @throws \Exception
     */
    private function buildInitialize() {
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setDescription('Initializer, only called once per page load');
        $method->setName('initialize');
        $method->setReturnType('void');
        $method->setContent(
            '$this->setConnectionService(\'' . $this->getConfiguration()->getConnectionService() . '\');
		parent::initialize();
' . $this->buildValidation() . '
' . $this->buildRelationshipsBuffer() . '
' . $this->buildUpdateSkips()
        );
        $this->getAbstractClass()->addMethod($method);
    }

    /**
     * Builds the getSource
     * @return void
     * @throws \Exception
     */
    private function buildGetSource() {
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setDescription('This returns the table name');
        $method->setName('getSource');
        $method->setReturnType('string');
        $method->setContent('return \'' . $this->getTableName() . '\';');
        $this->getAbstractClass()->addMethod($method);

        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('testGetSource');
        $method->setContent('$this->assertSame($this->getModel()->getSource(), \'' . $this->getTableName() . '\');');
        $this->getTestClass()->addMethod($method);
    }

    /**
     * Builds the addJoin method
     * @return void
     * @throws \Exception
     */
    private function buildAddJoin() {
        $aliases = array();
        foreach($this->getRelationships() as $relationship) {
            $aliases[] = $relationship->getAlias();
        }
        $content = 'switch($alias) {
';
        foreach($this->getRelationships() as $relationship) {
            $method = new Object\Method();
            $method->setAccess('private');
            $method->setName('addJoin' . $relationship->getAlias());
            $method->setDescription('This adds a join for the specific relationship');
            $method->setReturnType('string - The name of the model we just joined on');
            $method->setContent(
                '//Check if join already added
				$joins = $criteriaHelper->getJoins();
				foreach($joins as $join) {
					if($join[2]==\'' . $relationship->getAlias() . '\') {
						return \'' . $relationship->getRemoteModel() . '\';
					}
				}
				$criteriaHelper->getCriteria()->leftJoin(
					\'' . $relationship->getRemoteModel() . '\',
					$currentModelAlias.\'.' . $this->getField($relationship->getLocalColumn())->getShortName()
                . '=' . $relationship->getAlias()
                . '.' . $relationship->getRemoteShortColumn() . '\',
					\'' . $relationship->getAlias() . '\'
				);
				return \'' . $relationship->getRemoteModel() . '\';'
            );
            $parameter = new Object\Parameter();
            $parameter->setName('criteriaHelper');
            $parameter->setDescription('The criteria we are adding the join on to');
            $parameter->setType('CriteriaHelper');
            $parameter->setStrictType(true);
            $method->addParameter($parameter);
            $parameter = new Object\Parameter();
            $parameter->setName('currentModelAlias');
            $parameter->setDescription('The current model\'s alias');
            $parameter->setType('string');
            $method->addParameter($parameter);
            $this->getAbstractClass()->addMethod($method);

            $content .= '			case \'' . $relationship->getAlias() . '\':
				return $this->addJoin' . $relationship->getAlias() . '($criteriaHelper, $currentModelAlias);
				break;
';
        }
        $content .= '		}
		return null;';

        $method = new Object\Method();
        $method->setAccess('private');
        $method->setName('addJoinFromAlias');
        $method->setDescription('This adds a join based off of the aliases to an existing criteria, you can do nested joins, using a ., such as User.BranchSharing');
        $method->setReturnType('string - The name of the model we just joined on');
        $method->setContent($content);
        $parameter = new Object\Parameter();
        $parameter->setName('criteriaHelper');
        $parameter->setDescription('The criteria we are adding the join on to');
        $parameter->setType('CriteriaHelper');
        $parameter->setStrictType(true);
        $method->addParameter($parameter);
        $parameter = new Object\Parameter();
        $parameter->setName('alias');
        $parameter->setDescription('The alias of the relationship');
        $parameter->setType('string');
        $method->addParameter($parameter);
        $parameter = new Object\Parameter();
        $parameter->setName('currentModelAlias');
        $parameter->setDescription('The current model\'s alias');
        $parameter->setType('string');
        $method->addParameter($parameter);

        $this->getAbstractClass()->addMethod($method);

        $this->getAbstractClass()->addUse('Bullhorn\FastRest\Api\Services\Database\CriteriaHelper');
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('addJoin');
        $method->setDescription('This adds a join based off of the aliases to an existing criteria, you can do nested joins, using a ., such as User.BranchSharing');
        $method->setReturnType('string - The name of the model we just joined on');
        $method->setContent(
            'if(is_null($currentModelAlias)) {
			$currentModelAlias = \'' . str_replace('\Generated', '', $this->getAbstractClass()->getNamespace()) . '\\' . $this->getAbstractClass()->getName() . '\';
		}
		if(strpos($alias, \'.\')!==false) {
			$parts = explode(\'.\', $alias);
			$part = array_shift($parts);
			$modelName = $this->addJoin($criteriaHelper, $part, $currentModelAlias);
			/** @type GeneratedInterface $model */
			$model = new $modelName();
			return $model->addJoin($criteriaHelper, implode(\'.\', $parts), $part);
		}
		$allowedAliases = [
			\'' . implode("',\n\t\t\t'", $aliases) . '\'
		];
		if(!in_array($alias, $allowedAliases)) {
			throw new \InvalidArgumentException(\'The alias "\'.$alias.\'" is not in the allowed list of: \'.implode(\', \', $allowedAliases));
		}
		return $this->addJoinFromAlias($criteriaHelper, $alias, $currentModelAlias);
'
        );
        $parameter = new Object\Parameter();
        $parameter->setName('criteriaHelper');
        $parameter->setDescription('The criteria we are adding the join on to');
        $parameter->setType('CriteriaHelper');
        $parameter->setStrictType(true);
        $method->addParameter($parameter);

        $parameter = new Object\Parameter();
        $parameter->setName('alias');
        $parameter->setDescription('The alias of the relationship');
        $parameter->setType('string');
        $method->addParameter($parameter);
        $parameter = new Object\Parameter();
        $parameter->setName('currentModelAlias');
        $parameter->setDescription('The current model\'s alias');
        $parameter->setType('string');
        $parameter->setDefaultValue('null');
        $method->addParameter($parameter);

        $this->getAbstractClass()->addMethod($method);
    }


    private function buildIdField() {
        foreach($this->getFields() as $field) {
            if($field->isPrimary() && $field->getShortName() != 'id') {
                $method = new Object\Method();
                $method->setAccess('protected');
                $method->setName('getIdField');
                $method->setReturnType('string');
                $method->setContent('return \'' . $field->getShortName() . '\';');
                $this->getAbstractClass()->addMethod($method);
            }
        }
    }

    /**
     * Builds the buffer
     * @return void
     */
    private function build() {
        $this->getAbstractClass()->setDocumentation($this->getComment());
        $this->getAbstractClass()->setName(ucfirst($this->getTableName()));
        $this->getAbstractClass()->addUse(
            $this->getConfiguration()->getRootNamespace()
            . '\Models\\' . $this->getConfiguration()->getModelSubNamespace()
            . '\Base'
        );
        $this->getAbstractClass()->addUse('Bullhorn\FastRest\Api\Models\GeneratedInterface');
        $this->getAbstractClass()->setImplements(['GeneratedInterface']);
        $this->getAbstractClass()->setNamespace(
            $this->getConfiguration()->getRootNamespace()
            . '\Models\\' . $this->getConfiguration()->getModelSubNamespace()
            . '\Generated'
        );
        $this->getAbstractClass()->addUse(
            $this->getConfiguration()->getRootNamespace()
            . '\Models\\' . $this->getConfiguration()->getModelSubNamespace()
            . '\\' . ucfirst($this->getTableName()) . ' as ChildModel'
        );
        $this->getAbstractClass()->setExtends('Base');
        $this->getAbstractClass()->setAbstract(true);
        $this->buildIdField();

        $this->initTestClass();
        $this->getTestClass()->setNamespace($this->getAbstractClass()->getNamespace());
        $this->getTestClass()->setName($this->getAbstractClass()->getName() . 'Test');
        $this->buildTestSetup();

        $this->buildEnumConstants();

        $this->buildInitialize();
        $this->buildFieldsTypes();
        $this->buildVariableDefinitions();
        $this->buildOnConstruct();

        $this->buildGetSource();
        $this->buildAddJoin();

        $this->buildFindFirst();
        $this->buildFind();

        $this->buildGettersAndSetters();
        $this->buildRelationshipGetters();
        $this->buildColumnMap();
        $this->buildRelationshipsList();
    }

    /**
     * initTestClass
     * @return void
     * @throws \Exception
     */
    private function initTestClass() {
        $this->getTestClass()->setExtends('BaseTest');
        $this->getTestClass()->addUse('Bullhorn\FastRest\UnitTestHelper\Base as BaseTest');
    }

    /**
     * buildFindFirst
     * @return void
     * @throws \Exception
     */
    private function buildFindFirst() {
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('findFirst');
        $method->setDescription('Override parent so we have completion');
        $method->setStatic(true);
        $method->setReturnType('ChildModel|false');
        $parameter = new Object\Parameter();
        $parameter->setName('parameters');
        $parameter->setDefaultValue('null');
        $parameter->setDescription("Array of conditions or primary key.");
        $parameter->setType('array|int');
        $method->addParameter($parameter);
        $method->setContent('return parent::findFirst($parameters);');
        $this->getAbstractClass()->addMethod($method);

        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('findFirstInstance');
        $method->setDescription('This calls the findFirst method and is used for unit testing so that it is not a static method');
        $method->setReturnType('ChildModel|false');
        $parameter = new Object\Parameter();
        $parameter->setName('parameters');
        $parameter->setDefaultValue('null');
        $parameter->setDescription("Array of conditions or primary key.");
        $parameter->setType('array|int');
        $method->addParameter($parameter);
        $method->setContent('return $this->findFirst($parameters);');
        $this->getAbstractClass()->addMethod($method);
    }

    /**
     * buildFind
     * @return void
     * @throws \Exception
     */
    private function buildFind() {
        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('find');
        $method->setDescription('Override parent so we have completion');
        $method->setStatic(true);
        $method->setReturnType('ChildModel[]|ResultSet');
        $parameter = new Object\Parameter();
        $parameter->setName('parameters');
        $parameter->setDefaultValue('null');
        $parameter->setType('array');
        $method->addParameter($parameter);
        $method->setContent(
            'if(is_array($parameters) && empty($parameters)) {
			$model = new ChildModel();
			return new ResultSet($model->columnMap(), $model, null);
		}
		return parent::find($parameters);'
        );
        $this->getAbstractClass()->addUse('Phalcon\Mvc\Model\Resultset\Simple as ResultSet');
        $this->getAbstractClass()->addMethod($method);

        $method = new Object\Method();
        $method->setAccess('public');
        $method->setName('findInstance');
        $method->setDescription('This calls the find method and is used for unit testing so that it is not a static method');
        $method->setReturnType('ChildModel[]|ResultSet');
        $parameter = new Object\Parameter();
        $parameter->setName('parameters');
        $parameter->setDefaultValue('null');
        $parameter->setType('array');
        $method->addParameter($parameter);
        $method->setContent(
            'return $this->find($parameters);'
        );
        $this->getAbstractClass()->addUse('Phalcon\Mvc\Model\Resultset\Simple as ResultSet');
        $this->getAbstractClass()->addMethod($method);
    }

    /**
     * Builds the child buffer
     * @return Object\Index
     */
    private function buildChild() {
        $class = new Object\Index($this->getConfiguration());
        $class->setName(ucfirst($this->getTableName()));
        $class->setAbstract(false);
        $class->setExtends('GeneratedParent');

        $class->setNamespace(
            $this->getConfiguration()->getRootNamespace()
            . '\Models\\' . $this->getConfiguration()->getModelSubNamespace()
        );
        $class->addUse(
            $this->getConfiguration()->getRootNamespace()
            . '\Models\\' . $this->getConfiguration()->getModelSubNamespace()
            . '\Generated\\' . ucfirst($this->getTableName()) . ' as GeneratedParent'
        );
        $method = new Object\Method();
        $method->setDescription('This should set any defaults to the current object');
        $method->setName('loadDefaults');
        $method->setContent('//TODO');
        $method->setAccess('public');
        $method->setReturnType('void');
        $class->addMethod($method);

        return $class;
    }

    /**
     * Builds the mocked table object
     * @return Object\Index
     */
    private function buildTestTable() {
        $class = new Object\Index($this->getConfiguration());
        $class->setNamespace(
            $this->getConfiguration()->getRootNamespace()
            . '\PhalconHelper\Database\Tables\\' . $this->getConfiguration()->getModelSubNamespace()
        );
        $class->setName(ucfirst($this->getTableName()) . 'Test');
        $class->addUse('Bullhorn\FastRest\UnitTestHelper\MockTable');
        $class->addUse('Phalcon\Db\Column');
        $class->setExtends('MockTable');

        $method = new Object\Method();
        $method->setName('__construct');
        $content = '$columns = array();
';
        foreach($this->getFields() as $field) {
            $content .=
                '		$columns[] = new Column(
			\'' . $field->getName() . '\',
			[
				\'type\' => Column::'.$field->getPhalconColumnType().',
				\'primary\' => ' . ($field->isPrimary() ? 'TRUE' : 'FALSE') . ',
                \'autoIncrement\' => '.($field->isAutoIncrementing() ? 'TRUE' : 'FALSE').'
			]
		);
';
        }
        $content .= '		$this->setColumns($columns);';
        $method->setContent($content);
        $class->addMethod($method);

        return $class;
    }

    /**
     * Writes to a file
     *
     * @return void
     */
    public function write() {
        $this->getAbstractClass()->write();
        $child = $this->buildChild();
        if(!file_exists($child->getFileName())) {
            $child->write();
        }
        $this->getTestClass()->write();
        $this->buildTestTable()->write();
        $this->getValidationClass()->write();
        if(!file_exists($this->getValidationChildClass()->getFileName())) {
            $this->getValidationChildClass()->write();
        }
    }

    /**
     * buildSetterBoolContent
     * @param Field  $field
     * @param string $content
     * @return string
     */
    protected function buildSetterBoolContent(Field $field, $content) {
        $content .= '		$preFilterValue = $' . $field->getShortName() . ';
        if(!is_null($preFilterValue)) {
            $' . $field->getShortName() . ' = $this->getFilter()->sanitize($' . $field->getShortName() . ', \'boolean\');
            if(is_null($' . $field->getShortName() . ')) {
                throw new \InvalidArgumentException(\'Expected Type of boolean (1, true, on, yes, 0, false, off, no, ""), Invalid Value: \'.$preFilterValue);
            }
        } else {
            $' . $field->getShortName() . ' = $preFilterValue;
        }
';
        return $content;
    }
}