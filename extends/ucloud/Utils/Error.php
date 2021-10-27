<?php
namespace UCloud\Utils;

/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:13
 */
class Error
{
    public $Code;        // int
    public $ErrRet;	     // int
    public $ErrMsg;	     // string
    public $SessionId;	 // string

    public function __construct($code, $errRet, $errMsg)
    {
        $this->Code   = $code;
        $this->ErrRet = $errRet;
        $this->ErrMsg = $errMsg;
    }
}