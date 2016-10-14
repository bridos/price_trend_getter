
<?php
$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
$ch = curl_init("https://www.magiccardmarket.eu/Products/Singles/Eldritch+Moon/Imprisoned+in+the+Moon");
//$fp = fopen("data.txt", "w");
$ptrend="Price Trend";
$file='data1.txt';
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
$result=curl_exec($ch);
curl_close($ch);

$s_pos=strpos($result,$ptrend);
$rest= substr($result,$s_pos);

$s_pos=strpos($rest,'">');
$rest=substr($rest,$s_pos);

$e_pos=strpos($rest,'&');
$rest=substr($rest,0,$e_pos);
$rest=substr($rest,2);
file_put_contents($file,$rest);
echo $rest;
/*
$s_pos=strpos($rest,'">');
$rest=substr($rest,$s_pos);
$file='data1.txt';
//$e_pos=strpos($rest,'<');
//$value=substr($rest,$s_pos,3);
file_put_contents($file,$rest);
$e_pos=strpos($rest,';');
$value=substr($rest,$s_pos,$e_pos-$s_pos);
//fclose($fp);
//echo $result;
file_put_contents($file,$value);
echo $value;
*/
?>
