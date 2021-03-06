<meta http-equiv=Content-Type content="text/html;charset=utf-8">
<?PHP

	//人民币网关账号，该账号为11位人民币网关商户编号+01,该参数必填。
	$merchantAcctId = "1001292117101";
	//编码方式，1代表 UTF-8; 2 代表 GBK; 3代表 GB2312 默认为1,该参数必填。
	$inputCharset = "1";
	//接收支付结果的页面地址，该参数一般置为空即可。
	$pageUrl = "";
	//服务器接收支付结果的后台地址，该参数务必填写，不能为空。
	$bgUrl = "http://219.233.173.50:8802/rmb_demo/recieve.php";
	//网关版本，固定值：mobile1.0,该参数必填。
	$version =  "mobile1.0";
	//移动网关版本 phone代表手机版移动网关，pad代表平板移动网关，默认为phone
	$mobileGateway = "phone";
	//语言种类，1代表中文显示，2代表英文显示。默认为1,该参数必填。
	$language =  "1";
	//签名类型,该值为4，代表PKI加密方式,该参数必填。
	$signType =  "4";
	//支付人姓名,可以为空。
	$payerName= "张三"; 
	//支付人联系类型，1 代表电子邮件方式；2 代表手机联系方式。可以为空。
	$payerContactType =  "1";	//支付人联系方式，与payerContactType设置对应，payerContactType为1，则填写邮箱地址；payerContactType为2，则填写手机号码。可以为空。
	$payerContact =  "123456@qq.com";
	//指定付款人，可以为空
	$payerIdType = "3";
	//付款人标识，可以为空
	$payerId = "KQ33151000";
	//付款人IP，可以为空
	$payerIP = "192.168.1.1";
	//商户订单号，以下采用时间来定义订单号，商户可以根据自己订单号的定义规则来定义该值，不能为空。
	$orderId = KQ.date("YmdHis");
	//订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试。该参数必填。
	$orderAmount = "1";
	//订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101，不能为空。
	$orderTime = date("YmdHis");
	//快钱时间戳，格式：yyyyMMddHHmmss，如：20071117020101， 可以为空
	$orderTimestamp = date("YmdHis");
	//商品名称，可以为空。
	$productName= "Apple"; 
	//商品数量，可以为空。
	$productNum = "1";
	//商品代码，可以为空。
	$productId = "10000";
	//商品描述，可以为空。
	$productDesc = "Apple";
	//扩展字段1，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
	$ext1 = "扩展1";
	//扩展自段2，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
	$ext2 = "扩展2";
	//支付方式，一般为00，代表所有的支付方式。27-3代表支付宝H5直连，必填。
	$payType = "27-3";
	//银行代码，如果payType为00，该值可以为空；如果payType为10-1或10-2，该值必须填写，具体请参考银行列表。
	$bankId = "";
    //同一订单禁止重复提交标志，实物购物车填1，虚拟产品用0。1代表只能提交一次，0代表在支付不成功情况下可以再提交。可为空。
	$redoFlag = "0";
	//快钱合作伙伴的帐户号，即商户编号，可为空。
	$pid = "";
	// signMsg 签名字符串 不可空，生成加密签名串

	function kq_ck_null($kq_va,$kq_na){if($kq_va == ""){$kq_va="";}else{return $kq_va=$kq_na.'='.$kq_va.'&';}}


	$kq_all_para=kq_ck_null($inputCharset,'inputCharset');
	$kq_all_para.=kq_ck_null($pageUrl,"pageUrl");
	$kq_all_para.=kq_ck_null($bgUrl,'bgUrl');
	$kq_all_para.=kq_ck_null($version,'version');
	$kq_all_para.=kq_ck_null($language,'language');
	$kq_all_para.=kq_ck_null($signType,'signType');
	$kq_all_para.=kq_ck_null($merchantAcctId,'merchantAcctId');
	$kq_all_para.=kq_ck_null($payerName,'payerName');
	$kq_all_para.=kq_ck_null($payerContactType,'payerContactType');
	$kq_all_para.=kq_ck_null($payerContact,'payerContact');
	$kq_all_para.=kq_ck_null($payerIdType,'payerIdType');
	$kq_all_para.=kq_ck_null($payerId,'payerId');
	$kq_all_para.=kq_ck_null($payerIP,'payerIP');
	$kq_all_para.=kq_ck_null($orderId,'orderId');
	$kq_all_para.=kq_ck_null($orderAmount,'orderAmount');
	$kq_all_para.=kq_ck_null($orderTime,'orderTime');
	$kq_all_para.=kq_ck_null($orderTimestamp,'orderTimestamp');
	$kq_all_para.=kq_ck_null($productName,'productName');
	$kq_all_para.=kq_ck_null($productNum,'productNum');
	$kq_all_para.=kq_ck_null($productId,'productId');
	$kq_all_para.=kq_ck_null($productDesc,'productDesc');
	$kq_all_para.=kq_ck_null($ext1,'ext1');
	$kq_all_para.=kq_ck_null($ext2,'ext2');
	$kq_all_para.=kq_ck_null($payType,'payType');
	$kq_all_para.=kq_ck_null($bankId,'bankId');
	$kq_all_para.=kq_ck_null($redoFlag,'redoFlag');
	$kq_all_para.=kq_ck_null($pid,'pid');
	$kq_all_para.=kq_ck_null($mobileGateway,'mobileGateway');
	

	$kq_all_para=substr($kq_all_para,0,strlen($kq_all_para)-1);


	
	/////////////  RSA 签名计算 ///////// 开始 //
	$fp = fopen("./20190801.3300000002925831.pem", "r");
	$priv_key = fread($fp, 123456);
	fclose($fp);
	$pkeyid = openssl_get_privatekey($priv_key);

	// compute signature
	openssl_sign($kq_all_para, $signMsg, $pkeyid,OPENSSL_ALGO_SHA256);

	// free the key from memory
	openssl_free_key($pkeyid);

	 $signMsg = base64_encode($signMsg);
	/////////////  RSA 签名计算 ///////// 结束 //


?>

<style type="text/css">
	td{text-align:center}
</style>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title></title>
	</head>
	<body>
		<div align="center">
			<h2 align="center">提交到快钱页面</h2>
			<font color="#ff0000">（请在移动端非微信浏览器内提交）</font>
    		<table width="500" border="1" style="border-collapse: collapse" bordercolor="green" align="center">
				<tr>
					<td id="orderId">
						订单号
					</td>
					<td>
						<?PHP echo $orderId; ?>
					</td>
				</tr>
				<tr>
					<td id="orderAmount">
						订单金额
					</td>
					<td>
						<?PHP echo $orderAmount; ?>
					</td>
				</tr>
				<tr>
					<td id="orderTime">
						下单时间
					</td>
					<td>
						<?PHP echo $orderTime; ?>
					</td>
				</tr>
				<tr>
					<td id="productName">
						商品名称
					</td>
					<td>
						<?PHP echo $productName; ?>
					</td>
				</tr>
				<tr>
					<td id="productNum">
						商品数量
					</td>
					<td>
						<?PHP echo $productNum; ?>
					</td>
				</tr>
			</table>
		</div>
		<div align="center" style="font-weight: bold;">
			<form name="kqPay" action="https://sandbox.99bill.com/mobilegateway/recvMerchantInfoAction.htm" method="post">
				<input type="hidden" name="inputCharset" value="<?PHP echo $inputCharset; ?>" />
				<input type="hidden" name="pageUrl" value="<?PHP echo $pageUrl; ?>" />
				<input type="hidden" name="bgUrl" value="<?PHP echo $bgUrl; ?>" />
				<input type="hidden" name="version" value="<?PHP echo $version; ?>" />
				<input type="hidden" name="language" value="<?PHP echo $language; ?>" />
				<input type="hidden" name="signType" value="<?PHP echo $signType; ?>" />
				<input type="hidden" name="signMsg" value="<?PHP echo $signMsg; ?>" />
				<input type="hidden" name="merchantAcctId" value="<?PHP echo $merchantAcctId; ?>" />
				<input type="hidden" name="payerName" value="<?PHP echo $payerName; ?>" />
				<input type="hidden" name="payerContactType" value="<?PHP echo $payerContactType; ?>" />
				<input type="hidden" name="payerContact" value="<?PHP echo $payerContact; ?>" />
				<input type="hidden" name="payerIdType" value="<?PHP echo $payerIdType; ?>" />
				<input type="hidden" name="payerId" value="<?PHP echo $payerId; ?>" />
				<input type="hidden" name="payerIP" value="<?PHP echo $payerIP; ?>" />
				<input type="hidden" name="orderId" value="<?PHP echo $orderId; ?>" />
				<input type="hidden" name="orderAmount" value="<?PHP echo $orderAmount; ?>" />
				<input type="hidden" name="orderTime" value="<?PHP echo $orderTime; ?>" />
				<input type="hidden" name="orderTimestamp" value="<?PHP echo $orderTimestamp; ?>" />
				<input type="hidden" name="productName" value="<?PHP echo $productName; ?>" />
				<input type="hidden" name="productNum" value="<?PHP echo $productNum; ?>" />
				<input type="hidden" name="productId" value="<?PHP echo $productId; ?>" />
				<input type="hidden" name="productDesc" value="<?PHP echo $productDesc; ?>" />
				<input type="hidden" name="ext1" value="<?PHP echo $ext1; ?>" />
				<input type="hidden" name="ext2" value="<?PHP echo $ext2; ?>" />
				<input type="hidden" name="payType" value="<?PHP echo $payType; ?>" />
				<input type="hidden" name="bankId" value="<?PHP echo $bankId; ?>" />
				<input type="hidden" name="redoFlag" value="<?PHP echo $redoFlag; ?>" />
				<input type="hidden" name="pid" value="<?PHP echo $pid; ?>" />
				<input type="hidden" name="mobileGateway" value="<?PHP echo $mobileGateway; ?>" />
				<input type="submit" name="submit" value="提交到快钱">
			</form>
		</div>
	</body>
</html>
