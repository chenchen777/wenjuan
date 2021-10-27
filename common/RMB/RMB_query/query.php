<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="UTF-8" />
<link href="../../../style.css" rel="stylesheet" type="text/css" />
<title>商户查询接口</title>
</head>
<body style="text-align:left;">

<?php
if($_POST[go_search]){

// 提交地址
$clientObj = new SoapClient('https://sandbox.99bill.com/apipay/services/gatewayOrderQuery?wsdl');
//$clientObj = new SoapClient('https://www.99bill.com/apipay/services/gatewayOrderQuery?wsdl');  //生产地址
 
//  取得 FORM 提交 数据  ======= 开始  
$inputCharset=$_POST[inputCharset];  
$version=$_POST[version];
$signType=$_POST[signType];
$merchantAcctId=$_POST[merchantAcctId];
$queryType=$_POST[queryType];
$queryMode=$_POST[queryMode];
$startTime=$_POST[startTime];
$endTime=$_POST[endTime];
$requestPage=$_POST[requestPage];
$orderId=$_POST[orderId];
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

	$kq_all_para=appendParam($kq_all_para,'inputCharset',$inputCharset);
	$kq_all_para=appendParam($kq_all_para,'version',$version);
	$kq_all_para=appendParam($kq_all_para,'signType',$signType);
	$kq_all_para=appendParam($kq_all_para,'merchantAcctId',$merchantAcctId);
	$kq_all_para=appendParam($kq_all_para,'queryType',$queryType);
	$kq_all_para=appendParam($kq_all_para,'queryMode',$queryMode);
	$kq_all_para=appendParam($kq_all_para,'startTime',$startTime);
	$kq_all_para=appendParam($kq_all_para,'endTime',$endTime);
	$kq_all_para=appendParam($kq_all_para,'requestPage',$requestPage);
	$kq_all_para=appendParam($kq_all_para,'orderId',$orderId);

echo $kq_all_para."key=".$key.'<br>';

	$signMsg=$kq_sign_msg=strtoupper(md5($kq_all_para."key=".$key));	

	$para[inputCharset]=$inputCharset;
	$para[version]=$version;
	$para[signType]=$signType;
	$para[merchantAcctId]=$merchantAcctId;
	$para[queryType]=$queryType;
	$para[queryMode]=$queryMode;
	$para[startTime]=$startTime;
	$para[endTime]=$endTime;
	$para[requestPage]=$requestPage;
	$para[orderId]=$orderId;
	$para[signMsg]=$signMsg;

	
	
try {
	//  开始 读取 WEB SERVERS 上的 数据
     $result=$clientObj->__soapCall('gatewayOrderQuery',array($para));

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
		<td>查询总数据签名</td><td>'.$re[signMsg].'</td>
	</tr>
	</table>
	';
	echo '<BR><BR>';
	echo '<table  cellspacing="0" cellpadding="10" border="1">
	<tr>
		<td>dealId</td><td>dealTime</td><td>fee</td><td>orderAmount</td><td>orderId</td><td>orderTime</td>
		<td>payAmount</td><td>payResult</td><td>payType</td><td>signInfo</td>
	</tr>';

	//  输出 数组 各个订单数据==  开始
	foreach($re[orders] as $o_list){
		

		$sign_info='orderId='.$o_list[orderId].'&orderAmount='.$o_list[orderAmount].'&orderTime='.$o_list[orderTime].'&dealTime='.$o_list[dealTime].'&payResult='.$o_list[payResult].'&payType='.$o_list[payType].'&payAmount='.$o_list[payAmount].'&fee='.$o_list[fee].'&dealId='.$o_list[dealId].'&key='.$key;

		$sign_info=strtoupper(md5($sign_info));

		echo '
		<tr>
			<td>'.$o_list[dealId].'</td>

			<td>'.$o_list[dealTime].'</td>

			<td>'.$o_list[fee].'</td>

			<td>'.$o_list[orderAmount].'</td>

			<td>'.$o_list[orderId].'</td>

			<td>'.$o_list[orderTime].'</td>

			<td>'.$o_list[payAmount].'</td>

			<td>'.$o_list[payResult].'</td>

			<td>'.$o_list[payType].'</td>

			<td>'.$o_list[signInfo].'<BR>'.$sign_info.'</td>
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
			<td style="width:300px">字符集</td>
			<td><input type="text" name="inputCharset" value="1"></td>
			<td>固定值：1    代表UTF-8</td>
		</tr>
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
			<td>商家 KEY * </td>
			<td><input type="text" name="key" value="D4TSSG89AX2A596A"></td>
			<td>数字串</td>
		</tr>
		<tr>
			<td>查询方式 *</td>
			<td><input type="text" name="queryType" value="1"></td>
			<td>固定选择值：0、1 <BR>0 按商户订单号单笔查询（只返回成功订单）<BR>1 按交易结束时间批量查询（只返回成功订单）</td>
		</tr>
		<tr>
			<td>查询模式</td>
			<td><input type="text" name="queryMode" value="1"></td>
			<td>固定值：1 ---- 1 代表简单查询（返回基本订单信息）</td>
		</tr>
		<tr>
			<td>交易开始时间</td>
			<td><input type="text" name="startTime" value="20190220000000"></td>
			<td>数字串，一共14 位 格式为：年[4 位]月[2 位]日[2 位]时[2 位]分[2 位]秒[2位]，例如：20071117020101</td>
		</tr>
		<tr>
			<td>交易结束时间</td>
			<td><input type="text" name="endTime" value="20190220235959"></td>
			<td>数字串，一共14 位 格式为：年[4 位]月[2 位]日[2 位]时[2 位]分[2 位]秒[2位]，例如：20071117020101</td>
		</tr>
		<tr>
			<td>请求记录集页码</td>
			<td><input type="text" name="requestPage" value="1"></td>
			<td>数字串<BR>在查询结果数据总量很大时，快钱会将支付结果分多次返回。本参数表示商户需要得到的记录集页码。
默认为1，表示第1 页。</td>
		</tr>
		<tr>
			<td>商户订单号</td>
			<td><input type="text" name="orderId" value=""></td>
			<td>字符串 只允许使用字母、数字、- 、_,并以字母或数字开头</td>
		</tr>

	</table>
	

	<input type="submit" value="查看查询结果" name="go_search" style="font-size:32px;padding:10px;font-weight:bold;font-family:arail">
</form>

</body>