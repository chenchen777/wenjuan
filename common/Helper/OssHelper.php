<?php 

namespace common\Helper;

use common\component\UCloudFile;
use common\component\UCloudImg;
use phpDocumentor\Reflection\Types\Static_;

/***
 * oss相关
 * @author tang
 *
 */

class OssHelper
{
    Const IS_PUBLIC = true;
    Const WEB_WIDTH = 800; //web查看大图默认宽度
    Const WEB_HEIGHT = 600; //web查看大图默认宽度
    
    /***
     * 根据前台需要自由缩放图片，默认按高度自适应宽度
     * 查看大图使用默认设置的分辨率
     * 1按比例缩放  2宽度不变,高度按百分比缩放  3高度不变,宽度按百分比缩放 4指定宽度 ,高度等比缩放 5指定高度,宽度等比缩放 
     * 6widthXheight,限定长边，短边自适应缩放 7 widthXheight,限定短边，长边自适应缩放 
     * 8 widthXheight,指定高和宽的最小尺寸,等比缩放,如只指定高或宽,则未指定边按照指定数值进行裁剪.但是超出指定矩形会被居中裁剪
     *
     * @param string $path
     * @param number $resizeType
     * @param number $width
     * @param number $height
     * @param number $scale 缩放百分比 最大10000
     */
    public static  function getImgUrl($path,$resizeType = 5,$height = 0,$width = 0,$scale=1,$formName='image')
    {
        if(empty($path))
            return '';
        
       if($resizeType>0 && $width==0 && $height==0) {
           $width = self::WEB_WIDTH;
           $height = self::WEB_HEIGHT;
       }
        $img = new UCloudImg(trim($path));
        if($formName =='imgFile'){
            $url = @$img->resizeByType($width, $height,0 ,$scale ,self::IS_PUBLIC);

        }else{
           $url = @$img->resizeByType($width, $height,$resizeType ,$scale ,self::IS_PUBLIC);

       }
        return $url;
    }
    
    /**
     * 获取非图片文件
     * @param 文件路径 $path
     * @return string
     */
    public static function getFileUrl($path)
    {
        if(empty($path))
            return '';
        
        $file = new UCloudImg(trim($path));
        return self::IS_PUBLIC ? @$file->getPublicUrl() : @$file->getPrivateUrl();
    }
}