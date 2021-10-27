<?php
namespace UCloud\Utils;

/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/20
 * Time: 下午11:12
 */

abstract class ActionType
{
    const NONE      = -1;
    const PUTFILE   = 0;
    const POSTFILE  = 1;
    const MINIT     = 2;
    const MUPLOAD   = 3;
    const MFINISH   = 4;
    const MCANCEL   = 5;
    const DELETE    = 6;
    const UPLOADHIT = 7;
    const GETFILE   = 8;
}