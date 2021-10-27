<?php
namespace common\component;

use yii;

/**
 * **
 * 公共函数
 * 
 * @author tang
 *        
 */
class PublicHelper {

    /**
     * *
     * 根据unix时间戳计算年龄 -周岁
     * 
     * @param unix时间戳 $unixTime            
     */
    public static function getAge($unixTime) {
        $year = date('Y', $unixTime);
        if (($month = (date('m') - date('m', $unixTime))) < 0) {
            $year ++;
        } else 
            if ($month == 0 && date('d') - date('d', $unixTime) < 0) {
                $year ++;
            }
        return date('Y') - $year;
    }

    public static function getHash($user_id)
    {
        return uniqid().sprintf("%03s", mt_rand(0, 100));
    }


    /**
     * @param string $date
     * @return false|string
     */
    public static function getNextMonthEndDate($date){
        $firstday = date('Y-m-01', strtotime($date));
        $lastday = date('Y-m-d', strtotime("$firstday +2 month -1 day"));
        return  $lastday;
    }

    /**
     * @param string $date
     * @return false|string
     */
    public static function nextMonthToday($date){
        //获取今天是一个月中的第多少天
        $current_month_t =  date("t", strtotime($date));
        $current_month_d= date("d", strtotime($date));
        $current_month_m= date("m", strtotime($date));

        //获取下个月最后一天及下个月的总天数
        $next_month_end=static::getNextMonthEndDate($date);
        $next_month_t =  date("t", strtotime($next_month_end));

        $returnDate='';
        if($current_month_d==$current_month_t){//月末
            //获取下个月的月末
            $returnDate=$next_month_end;
        }else{//非月末
            //获取下个月的今天
            if($current_month_d>$next_month_t){ //如 01-30，二月没有今天,直接返回2月最后一天
                $returnDate=$next_month_end;
            }else{
                $returnDate=date("Y-m", strtotime($next_month_end))."-".$current_month_d;
            }
        }
        return $returnDate;
    }
   
    //获取浏览器类型
    public static function getBrowser()
    {
        $agent = $_SERVER["HTTP_USER_AGENT"];
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) // ie11判断
            return "ie";
        else if (strpos($agent, 'Firefox') !== false)
            return "firefox";
        else if (strpos($agent, 'Chrome') !== false)
            return "chrome";
        else if (strpos($agent, 'Opera') !== false)
            return 'opera';
        else if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false)
            return 'safari';
        else
            return 'unknown';
    }
        // 获取浏览器版本
    public static function getBrowserVer()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) { // 当浏览器没有发送访问者的信息的时候
            return 'unknow';
        }
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs))
            return $regs[1];
        else
            return 'unknow';
    }
    
    public static function makeJdUrl($sku)
    {
        return "https://item.jd.com/$sku.html";
    }
    
    /**
     * 获取比例差值
     */
    public static function calculateDiff($data,  &$tmp1, &$tmp2, &$tmp3)
    {
        if ($data != ($tmp1 + $tmp2 + $tmp3)) {    //假如点击量和浏览时长总数不一致
            $diff = ($tmp1 + $tmp2 + $tmp3) - $data;
            do {
                if ($tmp1 >= $tmp2 && $tmp1 >= $tmp3) { //取最大的那个数减去差值
                    if ($tmp1 < $diff) {
                        $diff -=$tmp1;
                        $tmp1 = 0;
                        $flag = true;
                    } else {
                        $tmp1 -= $diff;
                        $flag = false;
                    }
                }elseif ($tmp2 >= $tmp1 && $tmp2 >= $tmp3) {
                    if ($tmp2 < $diff) {
                        $diff -=$tmp2;
                        $tmp2 = 0;
                        $flag = true;
                    } else {
                        $tmp2 -= $diff;
                        $flag = false;
                    }
                }elseif ($tmp3 >= $tmp2 && $tmp3 >= $tmp1) {
                    if ($tmp3 < $diff) {
                        $diff -=$tmp3;
                        $tmp3 = 0;
                        $flag = true;
                    } else {
                        $tmp3 -= $diff;
                        $flag = false;
                    }
                }
    
            } while($flag);
        }
    }
    
    /**
     * 获取ip对应城市
     * @param unknown $ip
     * @return boolean|Ambigous <mixed, string>
     */
    public static function getIpCity($ip)
    {
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
        if(empty($res)){ return false; }
        $jsonMatches = array();
        preg_match('#\{.+?\}#', $res, $jsonMatches);
        if(!isset($jsonMatches[0])){ return false; }
        $json = json_decode($jsonMatches[0], true);
        if(isset($json['ret']) && $json['ret'] == 1){
            $json['ip'] = $ip;
            unset($json['ret']);
        }else{
            return false;
        }
        return $json;
    }
}