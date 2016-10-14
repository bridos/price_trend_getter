<?php
	//include 'functions.php';
	//array_from_file();
	//echo '<pre>'.print_r($GLOBALS['cardarray'],TRUE) . '</pre>';
	
	//add_value('Bedlam Reveler',0);
	//add_value('Spell Queller',0);
	//add_value('Bedlam Reveler',find_price(generate_path('Bedlam Reveler')));
	//daily_update();
	//add_entry('Eldtritch Evolution','Eldritch Moon',[7.0,7.50,7.59]);
	//echo '<pre>'.print_r($GLOBALS['cardarray'],TRUE) . '</pre>';
	
	//generateXML();
	//file_from_array();
	/*
	$server='localhost';
	$user='root';
	$pass='';
	$conn = mysqli_connect($server,$user,$pass,'price_trend_getter_db');
	if (!$conn) 
    	die("Connection failed: " . mysqli_connect_error());

	else
		echo "Connected successfully<br>";
	function query_generator($name,$set,$quantity,$price,$conn)
	{
		$str= "INSERT INTO active_selling_cards(id,name,cset,quantity,price) VALUES(NULL,'".$name."','".$set."',".$quantity.",".$price.");";
		mysqli_query($conn,$str);
		//echo $str."<br>";
		//return $str;
	}
	
	
	$sql="CREATE DATABASE price_trend_getter_db";
	if (mysqli_query($conn,$sql))
    	echo "Database created successfully<br>";
 	else
    	echo "Error creating database: " . mysqli_error($conn);
    */
    //mysqli_query($conn,'DROP TABLE IF EXISTS bought_cards') or die(mysqli_error($conn));
    /*
    $sql='CREATE TABLE bought_cards
    (
     id INT(6) AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(15),
     cset VARCHAR(20),
     quantity INT(6),
     price double
     )';
    if (mysqli_query($conn, $sql))
    	echo "Table bought_cards created successfully<br>";
    else
    	echo "Error creating table: " . mysqli_error($conn);
	
    $sql="INSERT INTO bought_cards(id,name,cset,quantity,price) VALUES(NULL,'Bedlam Reveler','Eldritch Moon',8,2.35)";
    if (mysqli_query($conn, $sql))
    	echo "New record created successfully";
	else
    	echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    
    $sql = 'CREATE TABLE active_selling_cards
    (
     id INT(6) AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(15),
     cset VARCHAR(20),
     quantity INT(6),
     price double
    )';
    if (mysqli_query($conn, $sql))
    	echo "Table a_s_cards created successfully<br>";
    else
    	echo "Error creating table: " . mysqli_error($conn);
    
    	$sql='';
    	$sql=query_generator('Anafenza, Kin-Tree Spirit','Dragons of Tarkir',1,1.3,$conn);
    	$sql=query_generator('Battlefield Forge','Magic Origins',1,1,$conn);
    	$sql=query_generator('Blight Herder','Battle for Zendikar',1,0.15,$conn);
    	$sql=query_generator('Caves of Kolios','Magic Origins',1,1.2,$conn);
    	$sql=query_generator('Collective Brutality','Eldritch Moon',1,2.5,$conn);
    	$sql=query_generator('Commune with Lava','Dragons of Tarkir',1,0.2,$conn);
    	$sql=query_generator('Cryptbreaker','Eldritch Moon',1,1,$conn);
    	$sql=query_generator("Dromoka's Command",'Dragons of Tarkir',1,2.5,$conn);
    	$sql=query_generator('Duskwatch Recruiter','Shadows over Innistrad',1,0.8,$conn);
    	$sql=query_generator('Fetid Heath','Zendikar Expeditions',1,35,$conn);
    	$sql=query_generator('Goblin Dark-Dwellers','Oath of the Gatewatch',2,4,$conn);
    	$sql=query_generator('Hissing Quagmire','Oath of the Gatewatch',1,2.9,$conn);
    	$sql=query_generator('Ishkanah Grafwidow','Eldritch Moon',1,6.5,$conn);
    	$sql=query_generator('Lightning Axe','Shadows over Innistrad',1,1,$conn);
    	$sql=query_generator('Liliana, the Last Hope','Eldritch Moon',1,30,$conn);
    	$sql=query_generator('Mox Opal','Moders Masters 2015',1,32,$conn);
    	$sql=query_generator('Omnath,Locus of Rage','Battle for Zendikar',1,1,$conn);
    	$sql=query_generator('Purphoros, God of the Forge','Theros',1,5.5,$conn);
    	$sql=query_generator('Realuty Smasher','Oath of the Gatewatch',1,3.5,$conn);
    	$sql=query_generator('Ruinous Path','Battle for Zendikar',1,0.5,$conn);
    	$sql=query_generator('Siege Rhino','Khans of Tarkir',1,1.2,$conn);
    	$sql=query_generator('Spell Queller','Eldritch Moon',1,8,$conn);
    	$sql=query_generator('Transgress the Mind','Battle for Zendikar',1,1,$conn);
    	*/
    	//$sql=query_generator('Ulrich of the Krallenhorde','Eldritch Moon',1,1.3,$conn);
    	
    	
    	$url='https://www.magiccardmarket.eu/';
    	
    	$data=array('username'=>'bridos',"userPassword"=>'p4n4thin4ikos');
    	$ch=curl_init();
    	//$dir=DOC_ROOT."/ctemp";
    	//$path=build_unique_path($dir);
    	//$cookie_file_path=$path."/cookie.txt";
    	curl_setopt($ch,CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_HEADER, false);
		//curl_setopt($ch, CURLOPT_NOBODY, false);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
//set the cookie the site has for certain features, this is optional
    	$headers  = array();

$headers[] = 'application/xhtml+voice+xml;version=1.2, application/x-xhtml+voice+xml;version=1.2, text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1';
$headers[] = 'Connection: Keep-Alive';
$headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';

curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt ($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the output in string format
    	curl_setopt($ch,CURLOPT_POST,true);
    	curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data));
    	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Expect:  "));
    	$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
    	curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    	$result=curl_exec($ch);
    	
    	curl_close($ch);
		echo $result;
    //mysqli_close($conn);
   
?>