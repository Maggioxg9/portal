<?php

	session_start();

	require_once "recaptchalib.php";

	$secret= "6Ld-CA4TAAAAAER5Lq6-85en9z0XjFVI3sX1ure5";
	$response = null;

	$recaptcha = new ReCaptcha($secret);

	if(count($_POST) > 0){

		//if($_POST["g-recaptcha-response"]){
			//$response= $recaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]);
		//}
		//if($response !=null && $response->success){
		if(true){	

			//database constants
			$servername = "localhost";
			$username = "root";
			$password = "raspberry";
			$dbname = "marketing";

			//get login info the user typed
			$newuser= htmlspecialchars($_POST["username"]);
			$newpass= htmlspecialchars($_POST["password"]);


			try{

				//create a database connection
				$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
				//create and execute sql query to check login info
				$stmt= $conn->prepare("select userid,level,email,firstname,lastname,phone,password,code from users where username= :name");
				$stmt->execute(array(':name' => "$newuser"));

				//check to see if user record was found
				if($stmt->rowcount() > 0){

					//username found check password
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$credentials=$result["password"];
			
					//put account details into local variables to be assigned later if login successfull
					$userid = $result["userid"];
					$userlevel = $result["level"];
					$useremail = $result["email"];
					$userfirstname = $result["firstname"];
					$userlastname = $result["lastname"];
					$userphone = $result["phone"];
					$refcode=$result["code"];

					//check if passwords match
					if(password_verify($newpass, $credentials )){
						if($userlevel==4){
							//account not approved yet, no access
							$_SESSION['badlogin']=true;
							//close database connection
							$conn = null;

							//implement a 2 second sleep to prevent brute-force hacking attempts
							sleep(2);
	
							//redirect back to login page where a try again message will appear
							header("Location: ../login.html");
							exit();
						}
	
						//passwords are a match, setup session variables for website use

						//create and execute sql query to get the shopping cart info for this user
						$assigncart = $conn->prepare("select cartid from carts where userid= :name");
						$assigncart->execute(array(':name' => "$userid"));
						$cartresult= $assigncart->fetch(PDO::FETCH_ASSOC);

						//get the cartid for this user so it can be set here once for use throughout the website
						$cartid = $cartresult["cartid"];


						//assign global session variables to be used throughout the website
						$_SESSION['usr'] = "$newuser";
						$_SESSION['pswd'] = "$credentials";
						$_SESSION['badlogin'] = false;
						$_SESSION['cartid'] = $cartid;
						$_SESSION['uid'] = $userid;
						$_SESSION['ulevel'] = $userlevel;
						$_SESSION['uemail'] = "$useremail";
						$_SESSION['ufname'] = "$userfirstname";
						$_SESSION['ulname'] = "$userlastname";
						$_SESSION['uphone'] = "$userphone";
						$_SESSION['ucode'] = "$refcode";
		
						//create and execute sql query to load any previously saved shopping cart items	
						$getcart = $conn->prepare("select products.name, cartitems.ud1,cartitems.ud2,cartitems.ud3,cartitems.ud4,cartitems.ud5,cartitems.ud6,cartitems.comments, cartitems.cartitemid from cartitems inner join products on cartitems.productid=products.productid where cartitems.cartid = :newcart");
						$getcart->execute(array(":newcart" => "$cartid"));
						$loadedcart= $getcart->fetchAll(PDO::FETCH_ASSOC);

						//put the saved cart item results into a session variable to be used by the homepage
						$_SESSION['cartarray']=json_encode($loadedcart);

						//if level 1 or 2 account, populate user array
						if($userlevel ==1){
							$getusers = $conn->prepare("select userid, username, email, firstname, lastname, phone, rep, code, level from users order by lastname asc");
							$getusers->execute();
							$allusers= $getusers->fetchAll(PDO::FETCH_ASSOC);
							$_SESSION['userarray']=json_encode($allusers);
						}else if($userlevel ==2){
							$getusers = $conn->prepare("select userid, username, email, firstname, lastname, phone, rep, code, level from users where rep=:rep order by lastname asc");
							$getusers->execute(array(':rep' => "$newuser"));
							$allusers= $getusers->fetchAll(PDO::FETCH_ASSOC);
							$_SESSION['userarray']=json_encode($allusers);
						}else{
							$_SESSION['userarray']=json_encode("");
						}
			
						//close database connection
						$conn = null;

			
						//redirect to homepage where shopping cart will be parsed from the session variable
						header("Location: ../index.html");
						exit();
					}else{

						//passwords dont match, set variable to impose a try again message
						$_SESSION['badlogin'] = true;

						//close database connection
						$conn = null;

						//implement a 2 second sleep to prevent brute-force hacking attempts
						sleep(2);

						//redirect back to login page where a try again message will appear
						header("Location: ../login.html");
						exit();
					}
				}else{
	
					//no username found, set variable to impose a try again message
					$_SESSION['badlogin'] = true;

					//close database connection
					$conn = null;

					//implement a 2 second sleep to prevent brute-force hacking attempts
					sleep(2);

					//redirect back to login page where a try again message will appear
					header("Location: ../login.html");
					exit();
				}
			}catch(PDOException $e){
		
				//print error details to screen
				echo $result . "<br>" . $e->getMessage();

				//close database connection
				$conn = null;
			}
		}else{
			//captcha wasnt clicked or bot found
			header("Location: ../login.html");
			exit();
		}
	}else{
		//request didnt come from login.html, redirect to that
		header("Location: ../login.html");
		exit();
	}
?>