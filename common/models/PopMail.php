<?php

namespace common\models;
/**
 * Created by PhpStorm.
 * User: liuyaping
 * Date: 2019/5/23
 * Time: 2:44 PM
 */

class PopMail extends Base
{
    public static function mailGet($user_name,$password)
    {
        $host = "tls://pop.163.com"; //‘tls：//’为ssl协议加密，端口走加密端口
        $user = $user_name; //邮箱
        $pass = $password; //密码
        $rec = new \src\pop3\Pop3($host, 995, 3);
        //打开连接
        if (!$rec->open())
            die($rec->err_str);
//        echo "open ";
        //登录
        if (!$rec->login($user, $pass)) {
            var_dump($rec->err_str);
            die;
        }
//        echo "login";

        if (!$rec->stat())
            die($rec->err_str);
//        echo "You  have" . $rec->messages . "emails,total size:" . $rec->size . "<br>";
        if ($rec->messages > 0) {
            //读取邮件列表
            if (!$rec->listmail())
                die($rec->err_str);
//            echo "Your mail list：<br>";
            for ($i = 1; $i <= count($rec->mail_list); $i++) {
//                echo "mailId:" . $rec->mail_list[$i]['num'] . "Size：" . $rec->mail_list[$i]['size'] . "<BR>";
            }
            //获取一个邮件
            //read One email
            $rec->getmail(count($rec->mail_list),-1);
//            echo "getHeader：<br>";
            for ($i = 0; $i < count($rec->head); $i++) {
//                echo htmlspecialchars($rec->head[$i]) . "<br>\n";
            }
//            echo "\n";
//            echo "getContent：<BR>";
            $need_str = '';
            for ($i = 0; $i < count($rec->body); $i++) {
//                echo "111\n";
                $str = htmlspecialchars($rec->body[$i]);
                if (strpos($str,"=E6=82=A8=E7=9A=84=E6=B3=A8=E5=86=8C=E9=AA=8C=E8=AF=81=E7=A0=81=E4=B8=BA=")){
                    $need_str = htmlspecialchars($rec->body[$i + 1]);
                    break;
                }
//                echo htmlspecialchars($rec->body[$i])  . "<br>\n";
            }
            $code = preg_match('/([0-9]{6,6})/',$need_str,$a) ? $a[1] : 0;
            echo "验证码为:" . $code ."\n";
            if ($code){
                $rec->close();
                return $code;
            }

        }
        $rec->close();

        return false;

    }
}