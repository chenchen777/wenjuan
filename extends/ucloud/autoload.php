<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/24
 * Time: 下午2:26
 */
define("NO_AUTH_CHECK", 0);
define("HEAD_FIELD_CHECK", 1);
define("QUERY_STRING_CHECK", 2);
function ucloud_autoload($class)
{
    if (false !== strpos($class, '\\')) {
        $name = strstr($class, '\\', true);
        if ($name == 'UCloud') {
            // Library目录下面的命名空间自动定位
            $path = __DIR__;
            $class = ltrim($class,'UCloud');
            $filename = $path . str_replace('\\', '/', $class) . ".php";
            if (is_file($filename)) {
                // Win环境下面严格区分大小写
                //if (IS_WIN && false === strpos(str_replace('/', '\\', realpath($filename)), $class . EXT)) {
                //    return;
                //}
                include $filename;
            }
        }
    }
}

spl_autoload_register('ucloud_autoload', true);