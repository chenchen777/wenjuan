<?php
/**
 * Created by PhpStorm.
 * User: lkp
 * Date: 18-5-26
 * Time: 下午2:35
 */
if (!function_exists('dd')) {
    //传递数据以易于阅读的样式格式化后输出
    function dd($data='')
    {
        // 定义样式
       echo '<pre style="display: block;padding: 9.5px;margin: 44px 0 0 0;font-size: 13px;line-height: 1.42857;color: #333;word-break: break-all;word-wrap: break-word;background-color: #F5F5F5;border: 1px solid #CCC;border-radius: 4px;">';
        // 如果是boolean或者null直接显示文字；否则print
        var_dump($data);
        exit();
    }
}


if (!function_exists('log_file')) {
    //传递数据以易于阅读的样式格式化后输出
    function log_file($content,$filename)
    {
        file_put_contents('/data/wwwroot/jdcha/logs/'.$filename,date('Y-m-d H:i:s',time()).PHP_EOL.$content.PHP_EOL,FILE_APPEND);
    }
}
