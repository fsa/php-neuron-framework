<?php

namespace FSA\Neuron;

abstract class Entity
{

    const ID = 'id';

    private $pdo;

    public function setPDO(PostgreSQL $pdo)
    {
        $this->pdo = $pdo;
    }

    public function update()
    {
        $class = get_called_class();
        $id = $class::ID;
        return $this->pdo->update($class::TABLENAME, $this->getColumnValues(), $id);
    }

    public function insert()
    {
        $class = get_called_class();
        $values = $this->getColumnValues();
        $id = $class::ID;
        unset($values[$id]);
        $this->$id = $this->pdo->insert($class::TABLENAME, $values, $id);
        return $this->$id;
    }

    public function upsert()
    {
        $class = get_called_class();
        if (is_null($this->{$class::ID})) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    protected function getColumnValues(): array
    {
        return get_object_vars($this);
    }

    public function inputPostInteger($param)
    {
        $this->$param = filter_input(INPUT_POST, $param, FILTER_VALIDATE_INT);
    }

    public function inputPostString($param)
    {
        $this->$param = filter_input(INPUT_POST, $param);
    }

    public function inputPostTextarea($param)
    {
        $this->$param = filter_input(INPUT_POST, $param);
    }

    public function inputPostDate($param)
    {
        $this->$param = filter_input(INPUT_POST, $param);
        if (!$this->$param) {
            $this->$param = null;
        }
    }

    public function inputPostDatetime($param)
    {
        $this->$param = filter_input(INPUT_POST, $param);
    }

    public function inputPostCheckbox($param)
    {
        $this->$param = filter_input(INPUT_POST, $param) == 'on';
    }


    public function inputPostCheckboxArray($param)
    {
        $values = filter_input(INPUT_POST, $param, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $this->$param = is_array($values) ? array_keys($values) : null;
    }

    public static function getEntity(PostgreSQL $pdo, $param, $method = INPUT_POST)  #:static in php 8.0
    {
        $id = filter_input($method, $param);
        $class = get_called_class();
        if ($id) {
            return $class::fetch($pdo, $id);
        }
        $entity = new $class;
        $entity->setPDO($pdo);
    }

    public static function fetch(PostgreSQL $pdo, $id): ?self
    {
        $class = get_called_class();
        $s = $pdo->prepare('SELECT * FROM ' . $class::TABLENAME . ' WHERE ' . $class::ID . '=?');
        $s->execute([$id]);
        $result = $s->fetchObject($class);
        if ($result) {
            $result->setPDO($pdo);
            return $result;
        }
        return null;
    }
}
