<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="UTF-8" />
<link href="../../../style.css" rel="stylesheet" type="text/css" />
<title>退款API接口</title>
</head>

<?PHP

// ======================= 传送参数设置  开始  =====================================
//* 表示 必填写项目.  ( )里的表示字符长度

//退款网关提交地址
$kq_target="https://sandbox.99bill.com/webapp/receiveDrawbackAction.do";
//$kq_target="https://www.99bill.com/webapp/receiveDrawbackAction.do"; //生产地址

//*商家用户编号		(30)
$kq_merchant_id = "10012921171";
//*密钥
$kq_key			= "XRY84HD234DHS3SR"; 
//*固定值: bill_drawback_api_1	(20)
$kq_version 	= "bill_drawback_api_1";
//*固定值: 001	表示下订单请求退款 (3)
$kq_command_type	    = "001";
//*退款流水号 只允许使用字母、数字、- 、_, 必须是数字字母开头 必须在商家自身账户交易中唯一(50)
$kq_txOrder			= KQ.date(YmdHis);
//*退款金额 可以是2位小数 ，人民币 元为单位(10)
$kq_amount	    = "0.01";
//*退款提交时间 格式 20071117020101 共14位(14)
$kq_postdate		= date(YmdHis);
//*原商户的订单号	(5)
$kq_orderid		= "KQ20190220134835";


// ======================= 传送参数设置  结束  =====================================

// ======================= 快钱 封装代码 ! ! 勿随便更改 开始  =====================================

$kq_all_para_o='merchant_id='.$kq_merchant_id.'version='.$kq_version.'command_type='.$kq_command_type.'orderid='.$kq_orderid.'amount='.$kq_amount.'postdate='.$kq_postdate.'txOrder='.$kq_txOrder;
$kq_mac=strtoupper(md5($kq_all_para_o."merchant_key=".$kq_key));  // 加密字符串
$kq_all_para='merchant_id='.$kq_merchant_id.'&version='.$kq_version.'&command_type='.$kq_command_type.'&orderid='.$kq_orderid.'&amount='.$kq_amount.'&postdate='.$kq_postdate.'&txOrder='.$kq_txOrder;

$kq_get_url=$kq_target.'?'.$kq_all_para.'&mac='.$kq_mac;
echo $kq_get_url;
?>

<body style="text-align:center;">
<form method=post action="" name="">
	<input type="hidden" name="tar_url" value="<?PHP echo $kq_get_url; ?>">
	<input type="submit" value="Pay Back"  style="font-size:32px;padding:10px;font-weight:bold;font-family:arail">
</form>


<h1>结果:</h1>
<?php
if($_POST[tar_url]){
//目标站
$url = $_POST[tar_url];
$fcontents = file_get_contents($url);
echo '==========='.$fcontents;
eregi("<MERCHANT>(.*)<\/MERCHANT>", $fcontents, $merchant_id);
eregi("<ORDERID>(.*)<\/ORDERID>", $fcontents, $orderid_id);
eregi("<TXORDER>(.*)<\/TXORDER>", $fcontents, $txorder_id);
eregi("<AMOUNT>(.*)<\/AMOUNT>", $fcontents, $amount);
eregi("<RESULT>(.*)<\/RESULT>", $fcontents, $judge_re);
eregi("<CODE>(.*)<\/CODE>", $fcontents, $error_code);
echo '<BR>商家编号: '.$merchant_id[1];
echo '<BR>退款流水号: '.$txorder_id[1];
echo '<BR>原商户的订单号: '.$orderid_id[1];
echo '<BR>退款金额: '.$amount[1];
echo '<BR>退款结果: ';
if($judge_re[1] == "Y"){
	echo $judge_re[1] = "退款申请成功";
}else{
	echo  $judge_re[1] = "退款申请失败";
}
echo '<BR>错误编号: '.$error_code[1];
}else{
	echo '请点击 "Pay Back： 才可以返回结果';
}

?>
</body>
</html>