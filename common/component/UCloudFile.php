<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/24
 * Time: 下午5:47
 */

namespace common\component;


use UCloud\Conf;
use UCloud\Proxy;
use UCloud\Utils\Error;
use yii\base\Exception;
use yii;

class UCloudFile
{
    private $bucket = 'jdmohe';
    private $conf = [];

    public function __construct(array $conf = [])
    {
        if (isset($conf['bucket'])) {
            $this->bucket = $conf['bucket'];
        }
        $this->conf =Yii::$app->params['UCloud_API'];
        if(isset($conf['private_key'])){
            $this->conf['private_key'] = $conf['private_key'];
        }
        if(isset($conf['public_key'])){
            $this->conf['public_key'] = $conf['public_key'];
        }
        if(isset($conf['timeout'])){
            $this->conf['timeout'] = $conf['timeout'];
            set_time_limit($conf['timeout']);
        }
        if(isset($conf['suffix'])){
            $this->conf['suffix'] = $conf['suffix'];
        }
    }

    public function upload($tmp_file, $key, $filename)
    {
        $proxy = new Proxy(new Conf($this->conf));
        /** @var $err Error */
//         list($data, $err) = $proxy->UCloud_UploadHit($this->bucket, $key, $tmp_file);
//         if (!empty($err)) {
        $fsize = filesize($tmp_file);
        if ($fsize > 10 * 1024 * 1024) {
            return $this->MUpload($tmp_file, $key, $filename);
        }
        list($data, $err) = $proxy->UCloud_PutFile($this->bucket, $key, $tmp_file, $filename);
//         }
        if ($err) {
            throw new Exception($err->ErrMsg, $err->Code);
        }

        return $data;
    }

    public function meta($key){
        $proxy = new Proxy(new Conf($this->conf));
        list($data, $err) = $proxy->meta($this->bucket, $key);
        if ($err) {
            throw new Exception($err->ErrMsg, $err->Code);
        }
        return $data;
    }

    protected function MUpload($tmp_file, $key, $filename)
    {
        $proxy = new Proxy(new Conf($this->conf));
        list($data, $err) = $proxy->UCloud_MInit($this->bucket, $key);
        if ($err) {
            throw new Exception($err->ErrMsg, $err->Code);
        }
        /**{
         * "UploadId": "0f188eb2-5e19-49c3-94c9-36fb5a0ff72a",
         * "BlkSize": 4194304,
         * "Bucket": "demobucket",
         * "Key": "demokey"
         * }
         * **/
        list($ETagList, $err) = $proxy->UCloud_MUpload($this->bucket, $key, $tmp_file, $data['UploadId'], $data['BlkSize'], 0, $filename);
        if ($err) {
            $proxy->UCloud_MCancel($this->bucket, $key, $data['UploadId']);
            throw new Exception($err->ErrMsg, $err->Code);
        }
        list($data, $err) = $proxy->UCloud_MFinish($this->bucket, $key, $data['UploadId'], $ETagList);
        if ($err) {
            throw new Exception($err->ErrMsg, $err->Code);
        }
        return $data;
    }

}