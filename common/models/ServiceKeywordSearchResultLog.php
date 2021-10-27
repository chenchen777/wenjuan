<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "service_keyword_search_result_log".
 *
 * @property int $id
 * @property int $sericve_keyword_search_id
 * @property int $user_id
 * @property string $keyword 关键词
 * @property string $sku
 * @property int $weight 权重分
 * @property int $title_weight 标题权重分
 * @property int $page 排名页数
 * @property int $page_position 位数
 * @property int $page_order 总排名
 * @property string $comment 评论数
 * @property int $is_ad 是否广告 1表示广告 0表示非广告
 * @property string $price 价格
 * @property int $type 1自营 2非自营
 * @property int $search_type 1查排名 2查权重
 * @property string $api_result_json 接口返回的文本内容
 * @property string $result_json 处理后的数据文本
 * @property string $good_title 商品标题
 * @property string $good_img_url 商品图片的url
 * @property string $promotion_logo
 * @property string $good_url 商品的URL
 * @property int $is_double 是否是双11商品
 * @property string $double_price 双11价格（改为价格）
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 * @property string $specification 规格
 * @property int $page_start
 * @property int $page_end
 */
class ServiceKeywordSearchResultLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_keyword_search_result_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sericve_keyword_search_id' => 'Sericve Keyword Search ID',
            'user_id' => 'User ID',
            'keyword' => 'Keyword',
            'sku' => 'Sku',
            'weight' => 'Weight',
            'title_weight' => 'Title Weight',
            'page' => 'Page',
            'page_position' => 'Page Position',
            'page_order' => 'Page Order',
            'comment' => 'Comment',
            'is_ad' => 'Is Ad',
            'price' => 'Price',
            'type' => 'Type',
            'search_type' => 'Search Type',
            'api_result_json' => 'Api Result Json',
            'result_json' => 'Result Json',
            'good_title' => 'Good Title',
            'good_img_url' => 'Good Img Url',
            'promotion_logo' => 'Promotion Logo',
            'good_url' => 'Good Url',
            'is_double' => 'Is Double',
            'double_price' => 'Double Price',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'specification' => 'Specification',
            'page_start' => 'Page Start',
            'page_end' => 'Page End',
        ];
    }
}
