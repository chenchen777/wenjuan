<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/25
 * Time: 上午10:40
 * 使用UCloud图片处理
 */

namespace common\component;

use UCloud\Proxy;

class UCloudImg
{
    private $host = '';
    private $key = '';
    private $bucket = 'maijiaxiu88';
    public function __construct($key='',$conf=[])
    {
        if(is_array($conf) and isset($config['host'])){
            $this->host = $conf['host'];
        }else{
            $this->host = Params::getParams('FILE_HOST','');
        }
        if(is_array($conf) and isset($config['bucket'])){
            $this->bucket = $conf['bucket'];
        }else{
            $this->bucket = Params::getParams('FILE_BUCKET','');
        }
        $this->key =  $key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }
    public function getPublicUrl()
    {
        return $this->getUrl(true);
    }
    public function getPrivateUrl()
    {
        return $this->getUrl(false);
    }

    protected function getUrl($public=true)
    {
        if(empty(trim($this->key))){
            return '';
        }
        $proxy = new Proxy();
        if($public)
        {
            return 'http://' . $proxy->UCloud_MakePublicUrl($this->bucket,$this->key,$this->host);
        }else{
            return 'http://' . $proxy->UCloud_MakePrivateUrl($this->bucket,$this->key,0,$this->host);
        }
    }
    public function resize($width,$height,$public = true)
    {
        $url = $this->getUrl($public);
        if(empty($url)){
            return '';
        }
        $cmd = [
            'iopcmd=thumbnail',
            'type=8',
            'height=' . $height,
            'width=' . $width,
        ];
        $query = implode('&',$cmd);
        if(false === strpos($url,'?')){
            $query = "?" .$query;
        } else {
            $query = "&" .$query;
        }
        return $url . $query;
    }

    public function resizeByWidth($width,$public=true)
    {
        $url = $this->getUrl($public);
        if(empty($url)){
            return '';
        }
        $cmd = [
            'iopcmd=thumbnail',
            'type=4',
            'width=' . $width,
        ];
        $query = implode('&',$cmd);
        if(false === strpos($url,'?')){
            $query = "?" .$query;
        } else {
            $query = "&" .$query;
        }
        return $url . $query;
    }
    public function resizeByHeight($height,$public=true)
    {
        $url = $this->getUrl($public);
        if(empty($url)){
            return '';
        }
        $cmd = [
            'iopcmd=thumbnail',
            'type=5',
            'height=' . $height,
        ];
        $query = implode('&',$cmd);
        if(false === strpos($url,'?')){
            $query = "?" .$query;
        } else {
            $query = "&" .$query;
        }
        return $url . $query;
    }
    

    /**
     * 全功能缩放
     * 方便前端自定义尺寸
     * 默认按宽度自适应高度
     * @param unknown $height
     * @param string $public
     */
    public function resizeByType($width,$height=0,$type=4,$scale = 1,$public=true)
    {
        $url = $this->getUrl($public);
        if(empty($url)){
            return '';
        }
        if(empty($type))
            return $url;
        $cmd = [
            'iopcmd=thumbnail',
            'type='.$type,
            'height=' . $height,
            'width='.$width,
            'scale=' . $scale,
        ];
        $query = implode('&',$cmd);
        if(false === strpos($url,'?')){
            $query = "?" .$query;
        } else {
            $query = "&" .$query;
        }
        return $url . $query;
    }
}