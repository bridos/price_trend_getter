<?php
include 'setarray.php';
$cardarray = array();
$curlarray=array();
$patharray=array();
$responsearray=array();
function array_from_file()
{

	$row = 1;
	$price_array=array();
	if (($handle = fopen("cards.csv", "r")) !== FALSE)
	{
    	while (($data = fgetcsv($handle, 1000, ";")) !== FALSE)
    	{
        	$num = count($data);
        	$name='';
        	$set='';
       		unset($price_array);
       		$price_array=array();
        	//echo "<p> $num fields in line $row: <br /></p>\n";
        	$row++;
        	for ($c=0; $c < $num; $c++)
        	{
        	    if ($c==0)
        	    {	
        	    	$name=$data[$c];
        	    	//echo $name."<br>";
        	    }
        	    else if($c==2)
        	    {	
            		$tok=strtok($data[$c],',');
            		while($tok!==false)
            		{
            			$price_array[]=$tok;
            			//echo $tok."<br>";
            			$tok=strtok(',');

            		}
            	}
            	else
           		{	
           			$set=$data[$c];
           			//echo $data[$c] . "<br />\n";
           		}
            } 
            $GLOBALS['cardarray'][$name]=array("name"=>$name,"set"=>$set,"values"=>$price_array);
            //$GLOBALS['cardarray'][$name]=array("name"=>$name,"set"=>$set,"values"=>$price_array);
        }
    }
    fclose($handle);
    //echo '<pre>'.print_r($cardarray,TRUE) . '</pre>';
}
function file_from_array()
{
	$j=0;
	$handle=fopen('cards.csv','w');
	foreach($GLOBALS['cardarray'] as $key=>$value)
	{	$str='';
		$i=0;
		$len_cardarray=count($GLOBALS['cardarray']);
		foreach($value as $field=>$val)
		{	
			if($field=='values')
			{	$len=count($val);
				foreach ($val as $index => $price)
				{	
					if($i==$len-1)
					$str.=$price;
					else
						$str.=$price.',';
					//echo $price.'<br>';
					$i++;
				}
			}	
			else
				$str.=$val.';';
				//echo $val.'<br>';
		}
		if($j==$len_cardarray-1)
			fwrite($handle, $str);
		else
			fwrite($handle, $str."\n");
		$j++;
	}
	fclose($handle);
}
function add_entry($name,$set,$values)
{	$price_arr=array();
	foreach($values as $index=>$price)
	{	$price=str_replace(',','.',$price);
		array_push($price_arr,$price);
	}
	$GLOBALS['cardarray'][$name]=array("name"=>$name,"set"=>$set,"values"=>$price_arr);
	$price_arr=[];
}
function add_value($in_str,$value)
{
	array_push($GLOBALS['cardarray'][$in_str]['values'],$value);
	//echo '<pre>'.print_r($GLOBALS['cardarray'][$in_str],TRUE) . '</pre>';

}
function generate_path($in_str,$mode,$name,$set)
{	//mode==1 is for data from array other mode is for data from db
	$ret="https://www.magiccardmarket.eu/Products/Singles/";
	if ($mode==1)
	{	
		$ret.=$GLOBALS['cardarray'][$in_str]['set']."/".$GLOBALS['cardarray'][$in_str]['name'];
		$ret=str_replace(' ','+',$ret);
		$ret=str_replace(',','%2C',$ret);
	}
	else
	{
		
		$ret.=$set."/".replace_all($name);
	}	
	$ret=str_replace(' ','+',$ret);
	$ret=str_replace(',','%2C',$ret);
	return $ret;
}
function replace_all($str)
{
	$str=str_replace(' / ','+%2F+',$str);
	return $str;
}
function improve_curl()
{
	//create connection
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	//create query
	$sql="SELECT * FROM active_selling_cards";
	//execute
	$result=mysqli_query($conn,$sql);
	$ret="https://www.magiccardmarket.eu/Products/Singles/";
	//create xml
	$dom = new DOMDocument("1.0","ISO-8859-1");
	//start xml population
	$node=$dom->createElement("data");
	$parnode = $dom->appendChild($node);
	header("Content-type: text/xml");
	//iterate through response
	while($row=mysqli_fetch_assoc($result))
	{	//setup the path
		$tmp_str=$ret.$row['cset'].'/'.replace_all($row['name']);
		$tmp_str=str_replace(' ','+',$tmp_str);
		$tmp_str=str_replace(',','%2C',$tmp_str);
		//array to hold all paths
		array_push($GLOBALS['patharray'],curl_init($tmp_str));
		//individual nodes for cards
		//$node = $dom->createElement("card");
		//$newnode = $parnode->appendChild($node);
		//individual node population
		//$newnode -> setAttribute("name",$row['name']);
		//$newnode -> setAttribute("set",$row['cset']);
		//$newnode -> setAttribute("quantity",$row['quantity']);
		//$newnode -> setAttribute("price",$row['price']);
	}
	improved_curl_execution();
	$result=mysqli_query($conn,$sql);
	$index=0;
	while($row=mysqli_fetch_assoc($result))
	{
		//individual nodes for cards
		$node = $dom->createElement("card");
		$newnode = $parnode->appendChild($node);
		//individual node population
		$newnode -> setAttribute("name",$row['name']);
		$newnode -> setAttribute("set",$row['cset']);
		$newnode -> setAttribute("quantity",$row['quantity']);
		$newnode -> setAttribute("price",$row['price']);
		$newnode -> setAttribute('current',get_value_from_HTML($GLOBALS['responsearray'][$index]));
		//$newnode -> setAttribute('current',1);
		$index++;
	}
	mysqli_close($conn);
	echo $dom->saveXML();
}
function improved_curl_execution()
{
	//hack for MCM to respond
	$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
	//iterate through path array/individual curl init
	foreach($GLOBALS['patharray'] as &$value)
	{	
		curl_setopt($value,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($value, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($value, CURLOPT_USERAGENT, $ua);
	}
	$mh=curl_multi_init();
	foreach($GLOBALS['patharray'] as &$value)
		curl_multi_add_handle($mh, $value);
	$running = null;
	do
	{
		curl_multi_exec($mh,$running);
	}while($running);
	foreach($GLOBALS['patharray'] as &$value)
		curl_multi_remove_handle($mh, $value);
	curl_multi_close($mh);
	foreach($GLOBALS['patharray'] as &$value)
		array_push($GLOBALS['responsearray'],curl_multi_getcontent($value));
}
function get_value_from_html($result)
{
	$s_pos=strpos($result,"Price Trend");
	$rest= substr($result,$s_pos);

	$s_pos=strpos($rest,'">');
	$rest=substr($rest,$s_pos);

	$e_pos=strpos($rest,'&');
	$rest=substr($rest,0,$e_pos);
	$rest=substr($rest,2);
	return $rest;
}
function find_price($addr)
{
	$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
	$ch = curl_init($addr);
	$ptrend="Price Trend";
	//$file='data1.txt';
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
	//file_put_contents($file,$rest);
	return $rest;
}
function daily_update()
{
	foreach($GLOBALS['cardarray']as $index=>$array)
		update_single_card($index);
}

function update_single_card($index)
{
	$path=generate_path($index,1,0,0);	//arguments are name, mode==1 for default handling and name&&set==0 (they are used for mode==2)
	$value=find_price($path);
	$value=str_replace(',','.',$value);
	add_value($index,$value);
}
function generateXML()
{	$dom = new DOMDocument("1.0","ISO-8859-1");
	$node=$dom->createElement("data");
	$parnode = $dom->appendChild($node);
	header("Content-type: text/xml");
	foreach($GLOBALS['cardarray'] as $cardname=>$infoarray)
	{
		$node = $dom->createElement("card");
		$newnode = $parnode->appendChild($node);
		$newnode -> setAttribute("cardname",$cardname);
		foreach($infoarray as $info=>$valuearr)
		{	if($info=='values')
			{
				foreach($valuearr as $key=>$val)
				{
					$node1=$dom->createElement('value');
					$newnode1=$newnode->appendChild($node1);
					$newnode1->setAttribute("value",$val);
				}
			}
		}
	}
	echo $dom->saveXML();
}
function get_hint2($q)
{	$hint='';
	//echo ('in func');
	if ($q !== "")
	{	//echo ('in if');
    	$q = strtolower($q);
    	$len=strlen($q);
    	$dom = new DOMDocument("1.0","ISO-8859-1");
		$node=$dom->createElement("data");
		$parnode = $dom->appendChild($node);
		header("Content-type: text/xml");
    	foreach($GLOBALS['a'] as $name)
    	{
        	if (stristr($q, substr($name, 0, $len)))
        	{	//echo ('in second if');
            	$node = $dom->createElement("card");
				$newnode = $parnode->appendChild($node);
				$newnode -> setAttribute("name",$name);
        	}
    	}
    	//echo('exit');
    	echo $dom->saveXML();
	}
	//echo $hint === "" ? "no suggestion" : $hint;
}
function get_hint($q)
{
	$hint='';
	if ($q !== "")
	{	
    	$q = strtolower($q);
    	$len=strlen($q);
    	foreach($GLOBALS['a'] as $name)
        	if (stristr($q, substr($name, 0, $len)))        	
            	if ($hint === "")
                	$hint = $name;
            	else
            		$hint .= ", $name";
	}
}
function get_cards()
{
	$dom = new DOMDocument("1.0","ISO-8859-1");
	$node=$dom->createElement("data");
	$parnode = $dom->appendChild($node);
	header("Content-type: text/xml");
	foreach($GLOBALS['cardarray'] as $cardname=>$infoarray)
	{
		$node = $dom->createElement("card");
		$newnode = $parnode->appendChild($node);
		$newnode -> setAttribute("cardname",$cardname);
		$newnode -> setAttribute("set",$infoarray['set']);
	}
	echo $dom->saveXML();
}
function bought_cards_XML()
{
	$dom = new DOMDocument("1.0","ISO-8859-1");
	$node=$dom->createElement("data");
	$parnode = $dom->appendChild($node);
	header("Content-type: text/xml");
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql="SELECT * FROM bought_cards";
	$result=mysqli_query($conn,$sql);
	while($row=mysqli_fetch_assoc($result))
	{
		$node=$dom->createElement('card');
		$newnode=$parnode->appendChild($node);
		$newnode -> setAttribute('name',$row['name']);
		$newnode -> setAttribute('set',$row['cset']);
		$newnode -> setAttribute('price',$row['price']);
		$newnode -> setAttribute('copies',$row['quantity']);
		$newnode -> setAttribute('action',$row['action']);
		$newnode -> setAttribute('id',$row['id']);
	}
	mysqli_close($conn);
	echo $dom->saveXML();
}
function add_bought_card($name,$set,$copies,$value,$action)
{
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "INSERT INTO bought_cards(id,action,name,cset,quantity,price) VALUES(NULL,'".$action."','".$name."','".$set."',".$copies.",".$value.")";
	mysqli_query($conn,$sql);
	mysqli_close($conn);
}
function delete_a_card($index)
{
	unset($GLOBALS['cardarray'][$index]);
}
function delete_bought_card($name)
{
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "DELETE FROM bought_cards WHERE id='".$name."'";
	mysqli_query($conn,$sql);
	mysqli_close($conn);
}
function selling_cards_XML()
{
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "SELECT * FROM active_selling_cards";
	$dom = new DOMDocument("1.0","ISO-8859-1");
	$node=$dom->createElement("data");
	$parnode = $dom->appendChild($node);
	header("Content-type: text/xml");
	$result=mysqli_query($conn,$sql);
	while($row=mysqli_fetch_assoc($result))
	{
		$node=$dom->createElement('card');
		$newnode=$parnode->appendChild($node);
		$newnode -> setAttribute('id',$row['id']);
		$newnode -> setAttribute('name',$row['name']);
		$newnode -> setAttribute('set',$row['cset']);
		$newnode -> setAttribute('price',$row['price']);
		$newnode -> setAttribute('copies',$row['quantity']);
		//$newnode -> setAttribute('current',current_price($row['name'],$row['cset']));
	}
	mysqli_close($conn);
	echo $dom->saveXML();
}
function current_price($name,$set)
{
	$path=generate_path('',2,$name,$set);
	//echo $path;
	$value=find_price($path);
	$value=str_replace(',','.',$value);
	return $value;
}
function add_offer($name,$set,$copies,$price)
{
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "INSERT INTO active_selling_cards(id,name,cset,quantity,price) VALUES(NULL,'".$name."','".$set."',".$copies.",".$price.")";
	mysqli_query($conn,$sql);
	mysqli_close($conn);
}
function remove_offer($id,$update)
{
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	if($update)
	{
		
		$sql="SELECT * FROM active_selling_cards WHERE id=".$id;
		$result=mysqli_query($conn, $sql);
		$row=mysqli_fetch_assoc($result);
		$sql="INSERT INTO bought_cards(id,action,name,cset,quantity,price)";
		$sql.="VALUES(NULL,'sell','".$row['name']."','".$row['cset']."',".$row['quantity'].",".$row['price'].")";
		echo $sql."<br>";
		if(mysqli_query($conn,$sql))
			echo "ok copy";
		else
		{	echo mysqli_error($conn);
			
		}
	}
	$sql = "DELETE FROM active_selling_cards WHERE id=".$id;
	mysqli_query($conn, $sql);

	mysqli_close($conn);
}
function update($name,$set,$price,$copies,$id)
{
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "UPDATE active_selling_cards SET name='".$name."', cset='".$set."',quantity=".$copies.",price=".$price."WHERE id=".$id;
	mysqli_query($conn, $sql);
	mysqli_close($conn);
}
function erase_last_entries()
{	
	foreach($GLOBALS['cardarray'] as $key=>$value)
		foreach($value as $info=>$valuearr)	
			if($info=='values')
				unset($GLOBALS['cardarray'][$key][$info][count($valuearr)-1]);
	//echo "<pre>".print_r($GLOBALS['cardarray'])."</pre>";
	//echo "blah";
}

	$mode=$_GET['mode'];
	if(isset($_GET['name']))
		$name=$_GET['name'];
	else
		$name='';
	if(isset($_GET['set']))
		$set=$_GET['set'];
	else
		$set='';
	if(isset($_GET['q']))
		$char=$_GET['q'];
	else
		$char='';
	if(isset($_GET['prices']))
	{
		$arr=json_decode($_GET['prices']);
	}
	else
		$arr='';
	if(isset($_GET['num']))
		$index=$_GET['num'];
	else
		$index='';
	if(isset($_GET['copies']))
		$copies=$_GET['copies'];
	else
		$copies='';
	if(isset($_GET['price']))
		$price=$_GET['price'];
	else
		$price='';
	if(isset($_GET['action']))
		$action=$_GET['action'];
	else
		$action='';
	if(isset($_GET['id']))
		$id=$_GET['id'];
	else
		$id='';
	if(isset($_GET['update']))
		$update=$_GET['update'];
	else
		$update='';
	//$mode=7;
	/*
	$name='Eldritch Evolution';
	$set='Eldritch Moon';
	$arr=[4.0,5.0,6.1];
	var_dump($arr);
	$arr=json_encode($arr);
	var_dump($arr);
	$arr=json_decode($arr);
	var_dump($arr);
	*/
	switch($mode)
	{	case 1:
			array_from_file();
			generateXML();
			break;
		case 2:
			array_from_file();
			daily_update();
			file_from_array();
			generateXML();
			break;
		case 3:
			array_from_file();
			add_entry($name,$set,$arr);
			update_single_card($name);
			file_from_array();
			//generateXML();
			break;
		case 4:
			get_hint2($char);
			break;
		case 5:
			array_from_file();
			get_cards();
			break;
		case 6:
			array_from_file();
			delete_a_card($index);
			file_from_array();
			break;
		case 7:
			bought_cards_XML();
			break;
		case 8:
			add_bought_card($name,$set,$copies,$price,$action);
			break;
		case 9:
			delete_bought_card($name);
			break;
		case 10:
			selling_cards_XML();
			break;
		case 11:
			improve_curl();
			break;
		case 12:
			add_offer($name,$set,$copies,$price);
			break;
		case 13:
			remove_offer($id,$update);
			break;
		case 14:
			update($name,$set,$price,$copies,$id);
			break;
		case 15:
			array_from_file();
			erase_last_entries();
			file_from_array();
			break;
		default:

	}
?>