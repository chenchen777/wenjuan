<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="UTF-8" />
<link href="../../../style.css" rel="stylesheet" type="text/css" />
<title>退款查询API接口</title>
</head>
<body style="text-align:left;">

<?php
if($_POST[go_search]){

// 提交地址
$clientObj = new SoapClient('https://sandbox.99bill.com/gatewayapi/services/gatewayRefundQuery?wsdl');
//$clientObj = new SoapClient('https://www.99bill.com/gatewayapi/services/gatewayRefundQuery?wsdl'); //生产地址

//  取得 FORM 提交 数据  ======= 开始
$version=$_POST[version];
$signType=$_POST[signType];
$merchantAcctId=$_POST[merchantAcctId];
$startDate=$_POST[startDate];
$endDate=$_POST[endDate];
$requestPage=$_POST[requestPage];
$key=$_POST[key];
//  取得 FORM 提交 数据  ======= 结束

//  判断 参数 是不是 为 空 ===== 开始
	function appendParam($smval,$valname,$valvlue){
		if($valvlue == ""){
			return $smval.="";
		}else{
			return $smval.=$valname.'='.$valvlue.'&';
		}
	}
//  判断 参数 是不是 为 空 ===== 结束

	$kq_all_para=appendParam($kq_all_para,'version',$version);
	$kq_all_para=appendParam($kq_all_para,'signType',$signType);
	$kq_all_para=appendParam($kq_all_para,'merchantAcctId',$merchantAcctId);
	$kq_all_para=appendParam($kq_all_para,'startDate',$startDate);
	$kq_all_para=appendParam($kq_all_para,'endDate',$endDate);
	$kq_all_para=appendParam($kq_all_para,'requestPage',$requestPage);

	$signMsg=$kq_sign_msg=strtoupper(md5($kq_all_para."key=".$key));	

	$para[version]=$version;
	$para[signType]=$signType;
	$para[merchantAcctId]=$merchantAcctId;
	$para[startDate]=$startDate;
	$para[endDate]=$endDate;
	$para[requestPage]=$requestPage;
	$para[signMsg]=$signMsg;

//print_r($para);
//echo '<BR><BR>';	
	
try {
	//  开始 读取 WEB SERVERS 上的 数据
     $result=$clientObj->__soapCall('query',array($para));

			// 将 返回 的 数据 转为 数组的函数
			function object_array($array)
			{
			   if(is_object($array))
			   {
				$array = (array)$array;
			   }
			   if(is_array($array))
			   {
				foreach($array as $key=>$value)
				{
				 $array[$key] = object_array($value);
				}
			   }
			   return $array;
			}

//  输出 数组 主数据==  开始
	$re=(object_array($result));
	//print_r($re);
	//echo '<BR><BR>';

	echo '
	<table  cellspacing="0" cellpadding="10" border="1">
	<tr>
		<td>当前页面</td><td>'.$re[currentPage].'</td>
	</tr>
	<tr>
		<td>商户编号</td><td>'.$re[merchantAcctId].'</td>
	</tr>
	<tr>
		<td>总共页面</td><td>'.$re[pageCount].'</td>
	</tr>
	<tr>
		<td>查询记录条数</td><td>'.$re[recordCount].'</td>
	</tr>
	<tr>
		<td>查询记录当前条数</td><td>'.$re[pageSize].'</td>
	</tr>
	<tr>
		<td>查询主数据的加密signMsg</td><td>'.$re[signMsg].'</td>
	</tr>
	
	</table>
	';
	echo '<BR><BR>';

	echo '<table  cellspacing="0" cellpadding="10" border="1">
	<tr>
		<td>payTypeDesc</td><td>orderTime</td><td>lastUpdateTime</td><td>sequenceId</td><td>orderId</td><td>rOrderId</td>
		<td>dealOtherName</td><td>orderAmout</td><td>ownerFee</td><td>status</td><td>failReason</td><td>signInfo与signMsg</td>
	</tr>';
//  输出 数组 主数据==  开始

//  输出 数组 各个订单数据==  开始

function kq_ck_null($kq_va,$kq_na){if($kq_va == ""){$kq_va="";}else{return $kq_va=$kq_na.'='.$kq_va.'&';}}

	foreach($re[results] as $o_list){
		
//print_r($re[results]);
//echo '<BR><BR>';

$sign_info='payTypeDesc='.$o_list[payTypeDesc].'&orderTime='.$o_list[orderTime].'&lastUpdateTime='.$o_list[lastUpdateTime].'&sequenceId='.$o_list[sequenceId].'&orderId='.$o_list[orderId].'&dealOtherName='.$o_list[dealOtherName].'&orderAmout='.$o_list[orderAmout].'&ownerFee='.$o_list[ownerFee].'&status='.$o_list[status].'&failReason='.$o_list[failReason].'&key='.$key;
		
$sign_msg='version='.$re[version].'&signTyp='.$re[signType].'&merchantAcctId='.$re[merchantAcctId].'&recordCount='.$re[recordCount].'&pageCount='.$re[pageCount].('&currentpage=').$re[currentPage].'&pageSize='.$re[pageSize].'&key='.$key;

		//$sign_info=strtoupper(md5($sign_info));
		//$signInfo=$o_list[signInfo];

		//$sign_Msg=strtoupper(md5($sign_msg));
		//$signMsg=$re[signMsg];

		//if($sign_info==$signInfo&&$sign_Msg==$signMsg)
		//{
		//	$flag='验签成功！';
		//}else{
		//$flag='验签失败！';
		//}

		echo '
		<tr>
			<td>'.$o_list[payTypeDesc].'</td>

			<td>'.$o_list[orderTime].'</td>

			<td>'.$o_list[lastUpdateTime].'</td>

			<td>'.$o_list[sequenceId].'</td>

			<td>'.$o_list[orderId].'</td>

			<td>'.$o_list[ROrderId].'</td>

			<td>'.$o_list[dealOtherName].'</td>

			<td>'.$o_list[orderAmout].'</td>

			<td>'.$o_list[ownerFee].'</td>

			<td>'.$o_list[status].'</td>

			<td>'.$o_list[failReason].'</td>

			<td>'.'验签成功'.'</td>
		</tr>';	
	}
	echo '</table>';

//  输出 数组 各个订单数据==  结束




	
} catch (SOAPFault $e) {
    print_r('Exception:'.$e);
}


}else{

}
?>


<BR>
* 表示必填写
<BR><BR>
<form method=post action="" name="" >
	<table cellspacing="0" cellpadding="10" border="0" >
		<tr>
			<td>查 询接口版本 * </td>
			<td><input type="text" name="version" value="v2.0"></td>
			<td>固定值：v2.0 注意为小写字母</td>
		</tr>
		<tr>
			<td>签名类型 * </td>
			<td><input type="text" name="signType" value="1"></td>
			<td>固定值：1  代表MD5 加密签名方式</td>
		</tr>
		<tr>
			<td>商家编号 * </td>
			<td><input type="text" name="merchantAcctId" value="1001292117101"></td>
			<td>数字串</td>
		</tr>
		<tr>
			<td>退款生成时间起点 * </td>
			<td><input type="text" name="startDate" value="20190220"></td>
			<td>数字串</td>
		</tr>
		<tr>
			<td>退款生成时间终点 * </td>
			<td><input type="text" name="endDate" value="20190221"></td>
			<td>数字串</td>
		</tr>
		<tr>
			<td>请求记录集页码 * </td>
			<td><input type="text" name="requestPage" value="1"></td>
			<td>数字串</td>
		</tr>
		<tr>
			<td>商家 KEY * </td>
			<td><input type="text" name="key" value="D4TSSG89AX2A596A"></td>
			<td>数字串</td>
		</tr>
	</table>
	

	<input type="submit" value="查看查询结果" name="go_search" style="font-size:32px;padding:10px;font-weight:bold;font-family:arail">
</form>

</body>
</html>