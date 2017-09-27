<?php

/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */
class RequestCheckUtil
{
    /**
     * 校验字段 fieldName 的值$value非空
     *
     **/
    static public function checkNotNull($value, $fieldName)
    {
        if (self::checkEmpty($value)) {
            throw new Exception('client-check-error:Missing Required Arguments: ' . $fieldName, 40);
        }

    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     *
     *
     **/
    static public function checkEmpty($value)
    {
        if (!isset($value)) {
            return true;
        }


        if ($value === NULL) {
            return true;
        }


        if (is_array($value) && (count($value) == 0)) {
            return true;
        }


        if (is_string($value) && (trim($value) === '')) {
            return true;
        }


        return false;
    }

    /**
     * 检验字段fieldName的值value 的长度
     *
     **/
    static public function checkMaxLength($value, $maxLength, $fieldName)
    {
        if (!self::checkEmpty($value) && ($maxLength < mb_strlen($value, 'UTF-8'))) {
            throw new Exception('client-check-error:Invalid Arguments:the length of ' . $fieldName . ' can not be larger than ' . $maxLength . '.', 41);
        }

    }

    /**
     * 检验字段fieldName的值value的最大列表长度
     *
     **/
    static public function checkMaxListSize($value, $maxSize, $fieldName)
    {
        if (self::checkEmpty($value)) {
            return NULL;
        }


        $list = preg_split('/,/', $value);

        if ($maxSize < count($list)) {
            throw new Exception('client-check-error:Invalid Arguments:the listsize(the string split by ",") of ' . $fieldName . ' must be less than ' . $maxSize . ' .', 41);
        }

    }

    /**
     * 检验字段fieldName的值value 的最大值
     *
     **/
    static public function checkMaxValue($value, $maxValue, $fieldName)
    {
        if (self::checkEmpty($value)) {
            return NULL;
        }


        self::checkNumeric($value, $fieldName);

        if ($maxValue < $value) {
            throw new Exception('client-check-error:Invalid Arguments:the value of ' . $fieldName . ' can not be larger than ' . $maxValue . ' .', 41);
        }

    }

    /**
     * 检验字段fieldName的值value是否是number
     *
     **/
    static protected function checkNumeric($value, $fieldName)
    {
        if (!is_numeric($value)) {
            throw new Exception('client-check-error:Invalid Arguments:the value of ' . $fieldName . ' is not number : ' . $value . ' .', 41);
        }

    }

    /**
     * 检验字段fieldName的值value 的最小值
     *
     **/
    static public function checkMinValue($value, $minValue, $fieldName)
    {
        if (self::checkEmpty($value)) {
            return NULL;
        }


        self::checkNumeric($value, $fieldName);

        if ($value < $minValue) {
            throw new Exception('client-check-error:Invalid Arguments:the value of ' . $fieldName . ' can not be less than ' . $minValue . ' .', 41);
        }

    }
}


?>