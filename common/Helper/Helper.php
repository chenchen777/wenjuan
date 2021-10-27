<?php
namespace common\Helper;

use yii\helpers\StringHelper;
use Yii;
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/3/22
 * Time: 16:35
 */
class Helper
{
    /**
     * 获取uploads目录下文件http地址
     * @param string $key uploads起始文件路径
     * @return string
     */
    public static function getFullPath($key)
    {
        if (!$key) {
            return '';
        }
        $img_host = Yii::$app->params['fileDomain'];
        return $img_host . '/' . $key;
    }

    /**
     * 随机获取浏览器
     * @return array
     */
    public static function getBrowser()
    {
        $browsers = ['chrome', 'firefox'];
        $index = 0;//rand(0, 1);

        $user_agents = [
            'chrome' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',//win10 chrome 56.0
            'firefox' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:51.0) Gecko/20100101 Firefox/51.0', //win10 firefox 51.0
        ];

        $browser = $browsers[$index];

        return [
            'browser' => $browser,
            'user_agent' => $user_agents[$browser],
        ];
    }
    public static function getBankLogo($key)
    {
        return '/res/' . $key;
    }

    //从地区获取城市名
    public static function getCity($area)
    {
        //$area like xx省,xx市; xx省xx市;xx,xx市;xx市;xx市,xx;xx;
        if(mb_strpos($area ,',')){

            $arr = explode(',',$area);
            if(in_array($arr[0], ['北京', '上海', '重庆', '天津','北京市', '上海市', '重庆市', '天津市'])){
                $arr = [0,$arr[0]];
            }else if(mb_strpos($arr[0] ,'市')){
                $arr = [0,$arr[0]];
            }
        }elseif (mb_strpos($area ,'省')){
            $arr = explode('省',$area);
        }else{
            $arr = [0,$area];
        }

        if(isset($arr[1])) {
            $city = $arr[1];
            unset($arr);
            if(StringHelper::endsWith($city,'市')){
                $city = mb_substr($city,0,mb_strlen($city)-1);
            }
        }else{
            $city = '';
        }

        return $city;
    }

    //Remove UTF8 Bom
    public static function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    //查询needle是否模糊匹配array中到某一项
    public static function arrayLike($array, $needle)
    {
        foreach ($array as $name) {
            if (mb_strpos($needle, $name) !== false) {
                return $name;
            }
            if (mb_strpos($name, $needle) !== false) {
                return $name;
            }
        }
        return '';
    }

    /**
     * @param $url
     * @return mixed
     */
    public static function curlGet($url){
        $headerArray =array("Content-type:application/json;","Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($curl);
        $code = curl_getinfo($curl,CURLINFO_HTTP_CODE); //请求状态码
        curl_close($curl);
        $output = json_decode($output,true);
        return $output;
    }


    /**
     * @param $url
     * @param array $params post参数(数组格式)
     * @param boolean $toJson 参数是否需要转json格式
     * @return mixed
     */
    public static function curlPost($url, $params, $toJson = true){
        $curl = curl_init();
        if ($toJson){
            $params  = json_encode($params);
            $headerArray = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
            curl_setopt($curl,CURLOPT_HTTPHEADER, $headerArray);
        }

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);      // 取消验证ssl证书
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);       // 取消验证ssl证书
//        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);         // 连接超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);                // 超时时间
        curl_setopt($curl, CURLOPT_POSTFIELDS, $toJson ? $params : http_build_query($params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
//        if (curl_errno($curl)){
//            return curl_error($curl);
//        }
//        $code = curl_getinfo($curl,CURLINFO_HTTP_CODE); //请求状态码
        curl_close($curl);
        $output = json_decode($output,true);
        return $output;
    }

    /**
     * 格式化【秒】到【时-分-秒】格式
     * @param $seconds
     * @return string
     */
    public static function formatTime($seconds){
        if (empty($seconds)){
            return "0秒";
        }
        $minute = 60;
        $hour = $minute * 60;

        $hourNum = floor($seconds / $hour);     // 向下取整
        $lastSeconds = $seconds - $hourNum * $hour;
        $minuteNum = floor($lastSeconds / $minute);
        $secondNum = $seconds % $minute;
        $str = $hourNum ? $hourNum."小时" : "";
        $str .= $minuteNum ? $minuteNum."分" : "";
        $str .= $secondNum ? $secondNum."秒" : "";
        return $str;
    }
}