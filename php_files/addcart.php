<?php
	//before anything, check to make sure user still logged in
	session_start();
	if(isset($_SESSION['usr']) && isset($_SESSION['pswd'])){
		//user is logged in, proceed
	}else{
		//not logged in, redirect to login page
		header("Location: ../login.html");
		exit();
	}
	if(count($_POST) > 0){

		//database constants
		$servername = "localhost";
		$username = "root";
		$password = "raspberry";
		$dbname = "marketing";
		
		//create local variables from superglobal variables
		$producturl=htmlspecialchars($_POST["producturl"]);
		$returnurl=htmlspecialchars($_POST["returnurl"]);
		$cartid=$_SESSION['cartid'];
		
		try{
		
			//create database connection
			$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
		
		
			//prepare sql query statements
			$stmt= $conn->prepare("select productid from products where pageurl= :url");
			$insrt= $conn->prepare("insert into cartitems (cartid,productid) values(:cartid , :productid)");
			$getcart = $conn->prepare("select products.name, cartitems.ud1,cartitems.ud2,cartitems.ud3,cartitems.ud4,cartitems.ud5,cartitems.ud6,cartitems.comments, cartitems.cartitemid from cartitems inner join products on cartitems.productid=products.productid where cartitems.cartid = :newcart");
		
		
			//get product to be added
			$stmt->execute(array(':url' => "$producturl"));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$productid=$result["productid"];
		
		
			//add product to cart table
			$insrt->execute(array(":cartid" => "$cartid", ":productid" => "$productid"));
		
		
			//get new cart array
			$getcart->execute(array(":newcart" => "$cartid"));
			$cartresult= $getcart->fetchAll(PDO::FETCH_ASSOC);
			
		
			//set boolean to change shopping cart
			$_SESSION['changecart']=true;
			$_SESSION['cartarray']=json_encode($cartresult);
		
		
			//refresh page
			$conn = null;
			header("Location: ../$returnurl");
			exit();
		
		}catch(PDOException $e){
			
			//print error to screen, possibly change to redirect?
			echo $result . "<br>" . $e->getMessage();
			$conn = null;
		} 
	}else{
		//user typed in url, redirect to page not found
		header("Location: ../notfound.html");
		exit();
	}	
?>