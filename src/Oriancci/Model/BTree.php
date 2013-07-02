<?php

namespace Oriancci\Model;

use Oriancci\Model;

class BTree extends Model
{

    public static $fieldLeft = 'lft';
    public static $fieldRight = 'rgt';

    /* Fields */

    public static function leftName()
    {
        return static::$fieldLeft;
    }

    public function left()
    {
        return $this->{static::leftName()};
    }

    public static function rightName()
    {
        return static::$fieldRight;
    }

    public function right()
    {
        return $this->{static::rightName()};
    }

    /* Insert */

    public static function root()
    {
        return static::select([WHERE => static::leftName() . ' = 1'])->one();
    }

    public static function deleteAll($sqlParameters, $sqlData = [])
    {
        throw new \Exception('BTREE must delete each model individually to maintain data integrity.');
    }

    public function insert()
    {
        throw new \Exception('BTREE must use appendTo, insertBefore or insertAfter; not insert.');
    }

    public function insertRoot()
    {
        if (!$this->eventDispatch('beforeInsertRoot', true)) {
            return false;
        }

        if (self::count()->value() != 0) {
            throw new \Exception('You can only insert a root if you have an empty table.');
        }

        $left = static::leftName();
        $right = static::rightName();

        $this->{$left} = 1;
        $this->{$right} = 2;

        $result = parent::insert();

        $this->eventDispatch('afterInsertRoot');

        return $result;
    }

    public function insertBefore($sibling)
    {
        if (!$this->eventDispatch('beforeInsertBefore', true)) {
            return false;
        }

        $left = static::leftName();
        $right = static::rightName();

        if ($sibling->$left == 1) {
            throw new \Exception('Cannot insert before root node');
        }

        $countDescendants = $this->countDescendants();

        $this->$left = $sibling->$left;
        $this->$right = $sibling->$left + ($countDescendants * 2) + 1;

        $sql = static::table()->update(
            [
                SET   => [$right => $right . ' + :diff'],
                WHERE => $right . ' >= :siblingLeft'
            ]
        );
        
        $sql->execute(
            [
                ':diff' => $this->$right - $this->$left + 1,
                ':siblingLeft' => $sibling->$left
            ]
        );

        $sql = static::table()->update(
            [
                SET   => [$left => $left . ' + :diff'],
                WHERE => $left . ' >= :siblingLeft'
            ]
        );

        $sql->execute(
            [
                ':diff' => $this->$right - $this->$left + 1,
                ':siblingLeft' => $sibling->$left
            ]
        );

        $result = parent::insert();

        $this->eventDispatch('afterInsertBefore');

        return $result;
    }

    public function insertAfter($sibling)
    {
        if (!$this->eventDispatch('beforeInsertAfter', true)) {
            return false;
        }

        $left = static::leftName();
        $right = static::rightName();

        if ($sibling->$left == 1) {
            throw new \Exception('Cannot insert after root node');
        }

        $countDescendants = $this->countDescendants();

        $this->$left = $sibling->$right + 1;
        $this->$right = $sibling->$right + ($countDescendants * 2) + 2;

        $sql = static::table()->update(
            [
                SET   => [$right => $right . ' + :diff'],
                WHERE => $right . ' > :siblingRight'
            ]
        );

        $sql->execute(
            [
                ':diff' => $this->right() - $this->left() + 1,
                ':siblingRight' => $sibling->right()
            ]
        );

        $sql = static::table()->update(
            [
                SET   => [$left => $left . ' + :diff'],
                WHERE => $left . ' > :siblingRight'
            ]
        );

        $sql->execute(
            [
                ':diff' => $this->right() - $this->left() + 1,
                ':siblingRight' => $sibling->right()
            ]
        );

        $result = parent::insert();

        $this->eventDispatch('afterInsertAfter');

        return $result;
    }

    public function appendTo($parent)
    {
        if (!$this->eventDispatch('beforeAppendTo', true)) {
            return false;
        }

        $left = static::leftName();
        $right = static::rightName();

        $countDescendants = $this->countDescendants();

        $this->{$left} = $parent->right();
        $this->{$right} = $parent->right() + ($countDescendants * 2) + 1;

        $sql = static::table()->update(
            [
                SET   => [$right => $right . ' + :diff'],
                WHERE => $right . ' >= :parentRight'
            ]
        );

        $sql->execute(
            [
                ':diff'        => $this->right() - $this->left() + 1,
                ':parentRight' => $parent->right()
            ]
        );

        $sql = static::table()->update(
            [
                SET   => [$left => $left . ' + :diff'],
                WHERE => $left . ' >= :parentRight'
            ]
        );

        $sql->execute(
            [
                ':diff' => $this->right() - $this->left() + 1,
                ':parentRight' => $parent->right()
            ]
        );

        $result = parent::insert();

        $this->eventDispatch('afterAppendTo');

        return $result;
    }

    public function delete()
    {
        $countDescendants = $this->countDescendants();

        if ($countDescendants != 0) {
            throw new \Exception('You cannot delete an item which has descendants.');
        }

        $left = static::leftName();
        $right = static::rightName();

        $sql = static::table()->update(
            [
                SET   => [$right => $right . ' - 2'],
                WHERE => $right . ' >= :selfRight'
            ]
        );

        $sql->execute(
            [
                ':selfRight' => $this->$right
            ]
        );

        $sql = static::table()->update(
            [
                SET   => [$left => $left . ' - 2'],
                WHERE => $left . ' >= :selfLeft'
            ]
        );

        $sql->execute(
            [
                ':selfLeft' => $this->$right
            ]
        );

        return parent::delete();
    }

    public function parents()
    {
        $left = static::leftName();
        $right = static::rightName();

        $sql = static::select(
            [
                WHERE    => $left . ' < :' . $left . ' AND '   . $right . ' > :' . $right,
                ORDER_BY => [$left => ASC]
            ]
        );

        return $sql->select(
            [
                ':' . $left  => $this->$left,
                ':' . $right => $this->$right
            ]
        );
    }

    public function parent()
    {
        $left = static::leftName();
        $right = static::rightName();

        return static::select(
            [
                WHERE    => $left . ' < :' . $left . ' AND '   . $right . ' > :' . $right,
                ORDER_BY => [$left => DESC],
                LIMIT    => 1
            ]
        )->one(
            [
                ':' . $left  => $this->$left,
                ':' . $right => $this->$right
            ]
        );
    }

    public function countDescendants()
    {
        $left = static::leftName();
        $right = static::rightName();

        if (is_null($this->$left) || is_null($this->$right)) {
            return 0;
        }

        return ($this->$right - $this->$left - 1) / 2;
    }

    public function descendants()
    {
        $left = static::leftName();
        $right = static::rightName();

        $sql = static::select(
            [
                WHERE    => $left . ' > :' . $left . ' AND '   . $right . ' < :' . $right,
                ORDER_BY => [$left => ASC],
            ]
        );

        return $sql->select(
            [
                ':' . $left  => $this->$left,
                ':' . $right => $this->$right
            ]
        );
    }
}
