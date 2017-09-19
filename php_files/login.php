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
			$dbname = "portal";

			//get login info the user typed
			$newuser= htmlspecialchars($_POST["username"]);
			$newpass= htmlspecialchars($_POST["password"]);


			try{

				//create a database connection
				$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
				//create and execute sql query to check login info
				$stmt= $conn->prepare("select userid,level,type,email,firstname,lastname,password from users where username= :name");
				$stmt->execute(array(':name' => "$newuser"));

				//check to see if user record was found
				if($stmt->rowcount() > 0){

					//username found check password
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$credentials=$result["password"];
			
					//put account details into local variables to be assigned later if login successfull
					$userid = $result["userid"];
					$userlevel = $result["level"];
					$usertype = $result["type"];
					$useremail = $result["email"];
					$userfirstname = $result["firstname"];
					$userlastname = $result["lastname"];

					//check if passwords match
					if(password_verify($newpass, $credentials )){
	
						//passwords are a match, setup session variables for website use


						//assign global session variables to be used throughout the website
						$_SESSION['usr'] = "$newuser";
						$_SESSION['pswd'] = "$credentials";
						$_SESSION['badlogin'] = false;
						$_SESSION['uid'] = $userid;
						$_SESSION['ulevel'] = $userlevel;
						$_SESSION['utype'] = $usertype;
						$_SESSION['uemail'] = "$useremail";
						$_SESSION['ufname'] = "$userfirstname";
						$_SESSION['ulname'] = "$userlastname";

						//close database connection
						$conn = null;
						
						//redirect to homepage 
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