<?php
include 'setarray.php';
$cardarray = array();	//this will hold all the monitored cards
$curlarray=array();		//this is used for the parallel curl request
$patharray=array();		//this will hold the paths for the parallel curl request
$responsearray=array();	//this will hold the responces of each parallel curl request
function array_from_file()
{
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	//create query
	$sql="SELECT * FROM monitored_cards";
	$rez=mysqli_query($conn,$sql);
	$price_array=array();
	while($row=mysqli_fetch_assoc($rez))
	{
		unset($price_array);	
       	$price_array=array();
       	$name=$row['card_name'];
       	$set=$row['set_name'];
       	$prices=$row['prices'];
       	$price_array=tokenize_prices($prices);
       	$GLOBALS['cardarray'][$name]=array("name"=>$name,"set"=>$set,"values"=>$price_array);
	}
	mysqli_close($conn);
	//echo '<pre>';
	//echo print_r($GLOBALS['cardarray']);
	//echo '</pre>';
}
function file_from_array()
{
	//echo '<pre>';
	//echo print_r($GLOBALS['cardarray']);
	//echo '</pre>';
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	//create query
	$sql="TRUNCATE monitored_cards";
	mysqli_query($conn,$sql);
	$str=create_query($GLOBALS['cardarray']);
	//echo $str;
	mysqli_multi_query($conn, $str);
}
function create_query($arr)
{	echo "<pre>";
	//print_r($arr);
	echo "</pre>";
	$ret='';
	foreach($arr as $key=>$value)
		foreach($value as $index=>$target)
			if($index=='name')
				$ret.="INSERT INTO monitored_cards(id,card_name,set_name,prices)VALUES(NULL,'".$target."','";
			else if($index=='set')
				$ret.=$target."','";
			else if($index=='values')
			{	foreach($target as $place=>$price)
					$ret.=$price.",";
				$ret=substr($ret,0,-1);
				$ret.="');";
			}

	$ret=substr($ret,0,-1);
	//echo "ret".$ret;
	return $ret;
}
function tokenize_prices($str)
{
	$tmp_arr=[];
	$tok=strtok($str,',');
	while($tok!==false)
	{	array_push($tmp_arr,$tok);
		$tok=strtok(',');
	}
	return $tmp_arr;

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
{	//we add the price value to the previous prices
	array_push($GLOBALS['cardarray'][$in_str]['values'],$value);
	array_shift($GLOBALS['cardarray'][$in_str]['values']);
	//echo '<pre>'.print_r($GLOBALS['cardarray'][$in_str],TRUE) . '</pre>';

}
function generate_path($in_str,$mode,$name,$set)
{	//mode==1 is for data from array other mode is for data from db
	//we construct the url from the card name and card set
	//modifications must be made for special characters
	$ret="https://www.magiccardmarket.eu/Products/Singles/";
	if ($mode==1)
	{	
		$ret.=$GLOBALS['cardarray'][$in_str]['set']."/".$GLOBALS['cardarray'][$in_str]['name'];
		$ret=str_replace(' ','+',$ret);
		$ret=str_replace(',','%2C',$ret);
	}
	else
		$ret.=$set."/".replace_all($name);
		
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
{	//to get the latest price for all the cards we have on offer
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
{	//parallel curl execution happens here
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
{	//since we have the path we can now initialize curl and get the html page with the data
	//then we must read it to find the price we need
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
{	//for every card we go and obtain the latest price value
	foreach($GLOBALS['cardarray']as $index=>$array)
		update_single_card($index);
}

function update_single_card($index)
{	//we generate the path for the card we want
	$path=generate_path($index,1,0,0);	//arguments are name, mode==1 for default handling and name&&set==0 (they are used for mode==2)
	//read the html that was returned to find the price trend
	$value=find_price($path);
	//we replace ',' with '.' in the price
	$value=str_replace(',','.',$value);
	//we add the value to the previous prices
	add_value($index,$value);
}
function generateXML()
{	//we will setup an XML to hold all the cards and the price values to send back to the server
	$dom = new DOMDocument("1.0","ISO-8859-1");
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
{	//we setup an xml with basic information for our monitored cards, no prices.
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
{	//we will generate the XML for the cards we have bought and sold
	//it has information for the name,set,price,copies and action(buy,sell)
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
{	//we update the database with a card 
	//$name is the card name
	//$set is the set name
	//$copies holds the number of cards in the transaction
	//$value is the price of the cards (single card price)
	//$action (buy,sell) we hold the kind of the transaction, cards purchased / cards sold
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "INSERT INTO bought_cards(id,action,name,cset,quantity,price) VALUES(NULL,'".$action."','".$name."','".$set."',".$copies.",".$value.")";
	mysqli_query($conn,$sql);
	mysqli_close($conn);
}
function delete_a_card($index)
{	//in case we need to delete a monitored card from the cardarray we call this function with $index 
	//the index of the card that needs to be deleted
	unset($GLOBALS['cardarray'][$index]);
}
function delete_bought_card($name)//FIX?!?!?!?!?
{	//to delete a transaction we call this function
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "DELETE FROM bought_cards WHERE id='".$name."'";
	mysqli_query($conn,$sql);
	mysqli_close($conn);
}
function selling_cards_XML()
{	//this function returns data from the db for our offers
	//we return the name,set,price and quantity of cards we are selling
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
{	//if we want to store a new offer we call this function with
	//$name: the name of the card
	//$set the set of the card
	//$copies the number of cards we have on offer
	//$price the price of a single card
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "INSERT INTO active_selling_cards(id,name,cset,quantity,price) VALUES(NULL,'".$name."','".$set."',".$copies.",".$price.")";
	mysqli_query($conn,$sql);
	mysqli_close($conn);
}
function remove_offer($id,$update)
{	//we remove an offer with id==$id
	//$update is used if we want to move the card from offers to the sold cards table
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	if($update)
	{
		$sql="SELECT * FROM active_selling_cards WHERE id=".$id;
		$result=mysqli_query($conn, $sql);
		$row=mysqli_fetch_assoc($result);
		$sql="INSERT INTO bought_cards(id,action,name,cset,quantity,price)";
		$sql.="VALUES(NULL,'sell','".$row['name']."','".$row['cset']."',".$row['quantity'].",".$row['price'].")";
	}
	$sql = "DELETE FROM active_selling_cards WHERE id=".$id;
	mysqli_query($conn, $sql);

	mysqli_close($conn);
}
function update($name,$set,$price,$copies,$id)
{	//when we want to update a card we call this function
	//with $name the name of the card
	//$set the set of the card
	//$copies the number of cards we are selling
	//$price the price of an individual card
	$conn = mysqli_connect('localhost','root','','price_trend_getter_db');
	$sql = "UPDATE active_selling_cards SET name='".$name."', cset='".$set."',quantity=".$copies.",price=".$price."WHERE id=".$id;
	mysqli_query($conn, $sql);
	mysqli_close($conn);
}
function erase_last_entries()
{	//in case of an error or a duplicate update we can erase the latest 
	//values for our monitored cards
	foreach($GLOBALS['cardarray'] as $key=>$value)
		foreach($value as $info=>$valuearr)	
			if($info=='values')
				unset($GLOBALS['cardarray'][$key][$info][count($valuearr)-1]);
	//echo "<pre>".print_r($GLOBALS['cardarray'])."</pre>";
	//echo "blah";
}
	//we use a single file for all server scripts 
	//we must handle all GET requests with an if to obtain the value of the appropriate variables
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
	//depending on the request we execute the appropriate functions
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