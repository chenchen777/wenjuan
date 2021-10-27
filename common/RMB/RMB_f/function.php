<?php

header("content-type:text/html;charset=utf-8"); 

$pubkey_path = 'C:/wamp/www/FO/batch_fo/CFCA_sandbox.cer';//快钱公钥地址
$pfx_path = 'C:/wamp/www/FO/batch_fo/20190801.3300000002933483.pfx';//商户PFX证书地址
$key_password = '123456';//证书密码

//私钥加密RSAwithSHA256
function crypto_seal_private($original_data){
	global $pfx_path,$key_password;
    $sealed_data=array();
    $pfx_file='file://'.$pfx_path;
    $pfx=file_get_contents($pfx_file);
    $certs=array();
    openssl_pkcs12_read($pfx,$certs,$key_password);
    $privkey=$certs['pkey'];
    openssl_sign($original_data,$signature,$privkey,OPENSSL_ALGO_SHA256);
  return base64_encode($signature);
}

//公钥加密OPENSSL_PKCS1_PADDING
function crypto_seal_pubilc($original_data){
 	 global $pubkey_path;
	 $sealed_data=array();
     $pubkey_file=$pubkey_path;
     $pubkey=file_get_contents("file://".$pubkey_file);
     openssl_public_encrypt($original_data,$signature,$pubkey,OPENSSL_PKCS1_PADDING);
  return base64_encode($signature);
}

//私钥解密OPENSSL_PKCS1_PADDING
function crypto_unseal_private($digitalEnvelope){
	global $pfx_path,$key_password;
    $pfx_file='file://'.$pfx_path;
    $pfx=file_get_contents($pfx_file);
    $certs=array();
    openssl_pkcs12_read($pfx,$certs,$key_password);   
    $privkey=$certs['pkey'];
	$digitalEnvelope = base64_decode($digitalEnvelope);
	$aaa= openssl_private_decrypt($digitalEnvelope,$receivekey,$privkey,OPENSSL_PKCS1_PADDING);
	return $receivekey;
}

//AES加密
function encrypt_aes($encrypt,$key){
	$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
	$encrypt = pkcs5Pad($encrypt,$size);
	$iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	echo 'key++++='.$key;
	$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $encrypt, MCRYPT_MODE_CBC,$iv);
	
	$encode = base64_encode($passcrypt);
	return $encode;
}



//pkcs5加密
function pkcs5Pad($text,$blocksize){
	$pad = $blocksize-(strlen($text)%$blocksize);
	return $text.str_repeat(chr($pad),$pad);
}


//AES解密
function decrypt_aes($str,$key) {
	$str =  base64_decode($str);
	$iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	echo 'key====='.$key;
	$or_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_CBC,$iv);
	$str = pkcs5Unpad($or_data);  
	return $str;  
}  


//pcks5解密
function pkcs5Unpad($text) {  
	$pad = ord($text{strlen($text)-1});  
	if ($pad>strlen($text))  
		return false;  
	if (strspn($text,chr($pad),strlen($text)-$pad)!=$pad)  
		return false; 
	return substr($text,0,-1*$pad);  
}  

//公钥验签
function crypto_unseal_pubilc($signedData,$receiveData){
	global $pubkey_path;
	$MAC = base64_decode($signedData);
	$fp = fopen($pubkey_path, "r"); 
	$cert = fread($fp, 8192); 
	fclose($fp); 
	$pubkeyid = openssl_get_publickey($cert); 
	$ok = openssl_verify($receiveData, $MAC, $pubkeyid, OPENSSL_ALGO_SHA256); 
	return $ok;
}

	if (! function_exists('gzdecode')) {
    //将gzdecode函数包括进来
 
//gzip解密
function gzdecode($data)
{

    $flags = ord(substr($data, 3, 1));
    $headerlen = 10;
    $extralen = 0;
    $filenamelen = 0;
    if ($flags & 4) 
    {
        $extralen = unpack('v' ,substr($data, 10, 2));
        $extralen = $extralen[1];
        $headerlen += 2 + $extralen;
    }
    if ($flags & 8)
    $headerlen = strpos($data, chr(0), $headerlen) + 1;
    if ($flags & 16)
    $headerlen = strpos($data, chr(0), $headerlen) + 1;
    if ($flags & 2)
    $headerlen += 2;
    $unpacked = @gzinflate(substr($data, $headerlen));
    if ($unpacked === FALSE)
    $unpacked = $data;
    return $unpacked;
}
}
?>
