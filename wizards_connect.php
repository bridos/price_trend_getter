<?php
$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
$ch = curl_init("http://magic.wizards.com/en/events/coverage/ptemn/top-8-decklists-2016-08-06");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
$result=curl_exec($ch);
curl_close($ch);
$times=substr_count($result,'"form-item form-type-select form-item-deck-list-sort-by"');
echo $times;
echo $result;
//$s_pos=strpos($result,'"form-item form-type-select form-item-deck-list-sort-by"');
//$result1=substr($result,$s_pos);
//$e_pos=strpos($result1,"toggle-samplehand toggle-subnav");
//$result2=substr($result1,$e_pos-$s_pos);
//echo $result2;
//echo $result;
