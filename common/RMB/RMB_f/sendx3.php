<?php
//批次查询
header("content-type:text/html;charset=utf-8"); 
date_default_timezone_set(PRC);
require_once 'function.php';

$membercode = "10012138842";//商户号
$time1 = date('YmdHis');//时间
$batchno= "batchNo_20190130153412";//批次号

//request-header
$rheader = '<tns:batchid-query-request xmlns:ns0="http://www.99bill.com/schema/commons" xmlns:ns1="http://www.99bill.com/schema/fo/commons" xmlns:tns="http://www.99bill.com/schema/fo/settlement">
  <tns:request-header>
    <tns:version xmlns:tns="http://www.99bill.com/schema/fo/commons">
      <ns0:version>1.0.1</ns0:version>
      <ns0:service>fo.batch.settlement.batchidquery</ns0:service>
    </tns:version>
    <ns1:time>'.$time1.'</ns1:time>
  </tns:request-header>';



//request-body

$rbody = '
<tns:request-body>
    <tns:batch-no>'.$batchno.'</tns:batch-no>
    <tns:page>1</tns:page>
    <tns:page-size>20</tns:page-size>
    <tns:list-flag>0</tns:list-flag>
  </tns:request-body>
</tns:batchid-query-request>';



$originalData = $rheader.$rbody;//原始报文
echo "<br/>请求明文：<br/>";
echo "<textarea rows=\"30\" cols=\"100\">".$originalData."</textarea>";

$autokey = rand(10000000,99999999).rand(10000000,99999999); //随机KEY
$originalData = gzencode($originalData);//GZIP压缩报文
$signeddata = crypto_seal_private($originalData);//私钥加密（验签/OPENSSL_ALGO_SHA1）
$digitalenvelope = crypto_seal_pubilc($autokey);//公钥加密（数字信封/OPENSSL_PKCS1_PADDING）
$encrypteddata = encrypt_aes($originalData,$autokey);//数据加密（AES/CBC/PKCS5Padding）

//提交地址
$url = 'https://sandbox.99bill.com/fo-batch-settlement/services'; //测试地址
//$url = 'https://www.99bill.com/fo-batch-settlement/services'; //生产地址

//提交报文
$str= '<?xml version=\'1.0\' encoding=\'UTF-8\'?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Body><tns:settlement-pki-api-request xmlns:ns0="http://www.99bill.com/schema/commons" xmlns:ns1="http://www.99bill.com/schema/fo/commons" xmlns:tns="http://www.99bill.com/schema/fo/settlement">
  <tns:request-header>
    <tns:version xmlns:tns="http://www.99bill.com/schema/fo/commons">
      <ns0:version>1.0.1</ns0:version>
      <ns0:service>fo.batch.settlement.batchidquery</ns0:service>
    </tns:version>
    <ns1:time>'.$time1.'</ns1:time>
  </tns:request-header>
  <tns:request-body>
    <tns:member-code>'.$membercode.'</tns:member-code>
    <tns:data>
      <ns1:original-data/>
      <ns1:signed-data>'.$signeddata.'</ns1:signed-data>
      <ns1:encrypted-data>'.$encrypteddata.'</ns1:encrypted-data>
      <ns1:digital-envelope>'.$digitalenvelope.'</ns1:digital-envelope>
    </tns:data>
  </tns:request-body>
</tns:settlement-pki-api-request></soapenv:Body></soapenv:Envelope>';
echo "<br/>请求密文：<br/>";
echo "<textarea rows=\"30\" cols=\"100\">".$str."</textarea>";
$header[] = "Content-type: text/xml;charset=utf-8";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
$output = curl_exec($ch);

$dom = new DOMDocument();
$dom -> loadXML($output);
echo "<br/>快钱返回密文：<br/>";
echo "<textarea rows=\"30\" cols=\"100\">".$output."</textarea>";

$receive = array(
'membercode' => $dom -> getElementsByTagName('member-code')->item(0)->nodeValue,//商户号
'status' => $dom -> getElementsByTagName('status')->item(0)->nodeValue,//状态
'errorCode' => $dom -> getElementsByTagName('error-code')->item(0)->nodeValue,//错误编号
'errorMsg' => $dom -> getElementsByTagName('error-msg')->item(0)->nodeValue,//错误代码
'signedData' => $dom -> getElementsByTagName('signed-data')->item(0)->nodeValue,//验签
'encryptedData' => $dom -> getElementsByTagName('encrypted-data')->item(0)->nodeValue,//加密报文
'digitalEnvelope' => $dom -> getElementsByTagName('digital-envelope')->item(0)->nodeValue//数字信封
);

echo "付款商户号：".$receive['membercode'];//商户号
echo "<br/>批次号：".$batchno;//批次号
echo "<br/>应答状态：".$receive['status'];//批次状态
echo "<br/>错误编号：".$receive['errorCode'];//错误编号
echo "<br/>错误代码：".$receive['errorMsg'];//错误代码

$receivekey = crypto_unseal_private($receive['digitalEnvelope']);

$receiveData2 = decrypt_aes($receive['encryptedData'],$receivekey);

$receiveData = gzdecode($receiveData2);

echo "<br/>结果明细：<br/>";//数据结果
echo "<textarea rows=\"30\" cols=\"100\">".$receiveData."</textarea>";

$ok = crypto_unseal_pubilc($receive['signedData'],$receiveData2); 

if($ok==1) echo "<br/>验签成功！";
else echo "<br/>验签失败！";

?>
