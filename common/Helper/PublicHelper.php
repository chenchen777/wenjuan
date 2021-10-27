<?php
namespace common\Helper;

use yii;
use common\component\UCloudFile;
use yii\web\UploadedFile;
use yii\base\BaseObject;

use common\component\UCloudImg;
use common\models\UploadImageForm;
use common\Helper\OssHelper;

use common\alipay\AlipayTradeRefundContentBuilder;
use common\alipay\AlipayTradeService;
use common\models\WxModel;

/**
 * **
 * 公共函数
 *
 * @author tang
 *
 */
class PublicHelper {


    /**
     * 上传图片
     */
    public static function uploadImg($formName = 'image')
    {
        $file_type = 'COMMENT';
        $key = '';
        $localname = '';
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        if (Yii::$app->request->isPost) {
            $upload = new UCloudFile();
            $file = UploadedFile::getInstanceByName($formName);
            if ($file) {
                $model = new UploadImageForm();
                $localname = $file->name;
                $localname_a = substr($localname,0,strrpos($localname,"."));
                $key = $model->generateKey($file_type, $file->extension, $file->tempName,$localname_a);
                try {
                    $result = $upload->upload($file->tempName, $key, $localname);
                }catch (\Exception $e){
                    Yii::error($e);
                    return ['status' => 0, 'msg' => '上传失败'];
                }
                if (!empty($key)) {
                    return ['status' => 1, 'data' => ['url' => OssHelper::getImgUrl($key,'',850,'','',$formName), 'key' => $key], 'msg' => ''];
                } else {
                    return ['status' => 0, 'msg' => '上传失败'];
                }
            } else {
                if (empty($file)) {
                    return ['status' => 0, 'msg' => '上传失败,文件不存在'];
                }
                return ['status' => 0, 'msg' => '上传失败'];
            }
        } else {
            return ['status' => 0, 'msg' => '上传失败'];
        }
    }

    /**
     * 添加图片后缀展示图片缩略图
     */
    public static function resizePic($url, $width, $height=0, $type=5, $scale = 1)
    {
        if (empty($url)) {
            return '';
        }
        if(empty($type)) {
            return $url;
        }
        $newUrl = [];
        foreach (explode(',', $url) as $value) {
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
            $newUrl[] = $value . $query;
        }
        return implode(',', $newUrl);
    }


    /**
     * 从远处下载图片到本地
     * @param unknown $url
     * @param string $save_dir
     * @param string $filename
     * @param number $type
     */
    public static function actionImage($url,$save_dir='',$filename='',$type=0)
    {
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(0!==strrpos($save_dir,'/')){
            $save_dir.='/';
        }
        $fileNameArr = [];
        foreach ($url as $value) {
            //             if(trim($filename)==''){//保存文件名
            $ext=strrchr($value,'.');
//             if($ext!='.gif'&&$ext!='.jpg'&&$ext!='.PNG'&&$ext!='.png'){
//                 return array('file_name'=>'','save_path'=>'','error'=>3);
//             }
            $tmp = explode('?', $ext);
            $filename=time().rand(10000,99999).$tmp[0];
            //             }
            $fileNameArr[] = $filename;
            //创建保存目录
            if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
                return array('file_name'=>'','save_path'=>'','error'=>5);
            }
            //获取远程文件所采用的方法
            if($type){
                $ch=curl_init();
                $timeout=5;
                curl_setopt($ch,CURLOPT_URL,$value);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $img=curl_exec($ch);
                curl_close($ch);
            }else{
                ob_start();
                @readfile($value);
                $img=ob_get_contents();
                ob_end_clean();
            }
            //$size=strlen($img);
            //文件大小
            $fp2=@fopen($save_dir.$filename,'a');
            fwrite($fp2,$img);
            fclose($fp2);
            unset($img);
        }
        return $fileNameArr;
    }
    /**
     * aes解密
     * @param unknown $url
     * @param string $save_dir
     * @param string $filename
     * @param number $type
     */
    public static function decrypt($sStr, $sKey){
        $decrypted = openssl_decrypt($sStr, 'AES-128-CBC', $sKey, OPENSSL_RAW_DATA, $sKey);
        return $decrypted;
    }

    /**
     * 解密
     * @param unknown $url
     * @param string $save_dir
     * @param string $filename
     * @param number $type
     */
    public static function decryptByPrivateKey($password, $private_key,$formerly=''){
        if($formerly == 1){
            $decode = base64_decode($password);
            return urldecode($decode);
        }else{
            openssl_private_decrypt($password, $decryptData, $private_key);
            return $decryptData;
        }
    }
}