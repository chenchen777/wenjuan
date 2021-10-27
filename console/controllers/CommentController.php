<?php

/**
 * Created by PhpStorm.
 * User: liuyaping
 * Date: 17/9/6
 * Time: 下午10:01
 */
namespace console\controllers;


use common\models\IpList;
use common\models\MonitorComment;
use common\models\ServiceKeywordSearchResult;
use yii\console\Controller;

class CommentController extends Controller
{

    //  释放空间
    public function actionDeleteLog(){
        $index = 22751141;
        $index_end = 20000000;
        do{
            $count = ServiceKeywordSearchResult::find()->andFilterWhere(['BETWEEN','id',$index_end,$index])->count();
            if ($count==0){
                echo "更新完成" .date('Y-m-d H:i',time()) . "\n";
                return;
            }
            ServiceKeywordSearchResult::updateAll(['result_json'=>''],ServiceKeywordSearchResult::find()->andFilterWhere(['BETWEEN','id',$index-500,$index])->where);
            echo "更新完成------".$index."更新完成" .date('Y-m-d H:i',time()) . "\n";
            $index -= 500;
        }while($count>0);
    }
    public function actionStatusUpdate()
    {
        $dat  = date('H:i',time());
        // 评价监控零点更新查询状态
        MonitorComment::updateAll(['is_update'=>0],MonitorComment::find()->where(['date'=>$dat,'deleted'=>0,'status'=>1])->where);
        echo date('Y-m-d H:i',time()) . "评价监控更新状态成功\n";
    }

    //  评论
    public function actionSearch()
    {
//        ini_set ('memory_limit', '512M');
        do{
            $count =0;
            try{
                $count = MonitorComment::find()->where(['deleted'=>0,'status'=>1,'is_update'=>0])->count();
                if ($count==0){
                    echo "更新完成" .date('Y-m-d H:i',time()) . "\n";
                    \Yii::$app->db->close();
                    return;
                }
                $procs = array();
                $model = MonitorComment::find()->where(['deleted'=>0,'status'=>1,'is_update'=>0]);
                $querys = $model->limit(20)->all();

                \Yii::$app->db->close();
                $cmds = [];
                if (!$querys){
                    echo "查询完毕" . date('Y-m-d H:i',time()) . "\n";
                    return;
                }
                $i = 0;
                foreach ($querys as $query){
                    $cmds[$i][] = $query->id;
                    $i++;
                    if ($i==8){
                        $i=0;
                    }
                }
                foreach ($cmds as $cmd) {
                    $pid = pcntl_fork();
                    if ($pid == - 1) { // 进程创建失败
                        die('fork child process failure!');
                    } elseif ($pid) { // 父进程处理逻辑
                        $procs[] = $pid;
                    } else {
                        // 子进程处理逻辑
                        foreach ($cmd as $line) {
                            system('cd /data/wwwroot/jdcha && ' . 'php ' . './yii comment/monitor-new ' . $line);
                            echo $line . " done!\n";
                        }
                        exit(0);
                    }
                }
                foreach ($procs as $proc) {
                    pcntl_waitpid($proc, $status);
                }
                unset($pid);
                $pid = NULL;
                unset($procs);
                $procs = NULL;
            }catch (\Exception $e){
                echo $e->getMessage() . "11" . "\n";
            }
        }while($count>0);
    }

    public function actionMonitorNew()
    {
        echo "更新时间" . date('Y-m-d H:i',time()) . "\n";
        $keyword_id = $_SERVER['argv'][2]?$_SERVER['argv'][2]:"";
//        $keyword_id = 681;
        if (!$keyword_id){
            return;
        }
        echo 'keyword_id:' . $keyword_id . "\n";
        $keyword = MonitorComment::find()->where(['id'=>$keyword_id])->one();
        if (!$keyword){
            $keyword->is_update = 1;
            $keyword->save();
            return;
        }

        if ($keyword->is_update != 1){
            $oneIp  = \Yii::$app->db->createCommand("SELECT id,ip,port FROM ip_list WHERE is_use=0  order by rand() limit 1")->queryOne();

//            $oneIp = IpList::find()->select('id,ip,port')->where(['is_use'=>0])->asArray()->one();
            //  创建时间  结束
            $start_at = strtotime($keyword->date) - 86400;
            $end_at = strtotime($keyword->date);
            $ipId = $oneIp['id'];

            \Yii::$app->db->createCommand("UPDATE `ip_list` SET is_use=1 WHERE id=$ipId")->execute();
            $paramaDelete = [
                'sku'        => $keyword->sku,
                'ip'        => $oneIp['ip'],
                'port'        => $oneIp['port'],
                'time_interval_start'        => $start_at,
                'time_interval_end'        => $end_at,
            ];

            $at1 = date("Y-m-d",strtotime("-1 day"));
            $at2 = date('Y-m-d',$keyword->create_at);
            if($at1 == $at2){
                $paramaDelete['time_interval_start'] = strtotime($at1);
                $paramaDelete['time_interval_end'] = (int)$keyword->create_at;
//                MonitorComment::deleteComment($paramaDelete,$keyword->id,$keyword->user_id);
            }else{
                MonitorComment::deleteComment($paramaDelete,$keyword->id,$keyword->user_id);
            }

            $parama = [
                'sku'        => $keyword->sku,
                'ip'        => $oneIp['ip'],
                'port'        => $oneIp['port'],
                'time_interval_start'        => $start_at,
                'time_interval_end'        => $end_at,
            ];

            //  评论
            MonitorComment::addComment($parama,$keyword->id,'script',$keyword->user_id);
            //  折叠评论
            MonitorComment::addCommentOmit($parama,$keyword->id,'script',$keyword->user_id);
        }
        $keyword->is_update = 1;
        if (!$keyword->save()){
            echo $keyword->getErrors() ."22" . "\n";
        };

    }
}