<?php	
	require_once "recaptchalib.php";
	session_start();

	if(count($_POST) > 0){

		$secret= "6Ld-CA4TAAAAAER5Lq6-85en9z0XjFVI3sX1ure5";
		$response = null;
	
		$recaptcha = new ReCaptcha($secret);
	
		$_SESSION['tmpuser']= htmlspecialchars($_POST['username']);
		$_SESSION['tmppass']= htmlspecialchars($_POST['password']);
		$_SESSION['tmprepass']= htmlspecialchars($_POST['repassword']);
		$_SESSION['tmpfname']= htmlspecialchars($_POST['firstname']);
		$_SESSION['tmplname']= htmlspecialchars($_POST['lastname']);
		$_SESSION['tmpemail']= htmlspecialchars($_POST['email']);
		$_SESSION['tmpphone']= htmlspecialchars($_POST['phone']);
		$_SESSION['tmpref']= htmlspecialchars($_POST['refcode']);
		$_SESSION['tmpbtn']= htmlspecialchars($_POST['accounttype']);
		
		// isset($_POST["g-recaptcha-response"])
		if(true){
			//$response= $recaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]);
			//if($response !=null && $response->success){
			if(true){
				if(empty( $_POST["username"])||empty( $_POST["password"])||empty( $_POST["repassword"])||empty( $_POST["firstname"])||empty( $_POST["lastname"])||empty( $_POST["email"])){
					$_SESSION['badcreate']="Please fill out all fields.";
					header("Location: ../newuser.html");
					exit();
				}else if( $_POST["password"]!= $_POST["repassword"]){
					$_SESSION['badcreate']="Passwords must match.";
					header("Location: ../newuser.html");
					exit(); 
				}else if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
					$_SESSION['badcreate']="Please enter a valid email address.";
					header("Location: ../newuser.html");
					exit();
				}else{
					//everything is valid, assign variables
	
					//database constants
					$servername = "localhost";
					$username = "root";
					$password = "raspberry";
					$dbname = "marketing";
					$newuser= htmlspecialchars($_POST["username"]);
					$newpass= htmlspecialchars($_POST["password"]);
					$repass= htmlspecialchars($_POST["repassword"]);
					$firstname= htmlspecialchars($_POST["firstname"]);
					$lastname= htmlspecialchars($_POST["lastname"]);
					$email= htmlspecialchars($_POST["email"]);
					$phone= htmlspecialchars($_POST["phone"]);
					$repid = htmlspecialchars($_POST['refcode']);
					$level="4";
					$aelevel="4";
					$rep="";
				
					try{
		
						//create a database connection
						$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
						$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					
						//create and execute sql query to check if username exists
						$stmt= $conn->prepare("select userid from users where username= :name");
						$stmt->execute(array(':name' => "$newuser"));
						
						//check to see if user record was found
						if($stmt->rowcount() > 0){
							//user already exists
							$_SESSION['badcreate']="Username already exists, please choose a different username.";
							$conn=null;
							header("Location: ../newuser.html");
							exit();
						}else{
							//username is free, setup new user
							$gencode = mt_rand(100000000, 999999999);
							$recoveryPIN=mt_rand(100000000,999999999);
							if($_SESSION['tmpbtn']=="accelemployee"){
								
								//create and execute sql query to check if rep id entered exists and is AE superuser
								$chk= $conn->prepare("select username from users where code= :repid and level= :superlevel");
								$chk->execute(array(':repid' => "$repid", ':superlevel'=>"1"));
								
								//check to see if user record was found
								if($chk->rowcount() > 0){
									//rep id exists, continue
								}else{
									//no rep id found
									$_SESSION['badcreate']="Referral code invalid. Enter the code an Accel Entertainment superuser provided.";
									$conn=null;
									header("Location: ../newuser.html");
									exit();
								}
														
								$insrt=$conn->prepare("insert into users (username, password, level, email, firstname, lastname, phone, code, recovery) values (:newusername, :newpasshash, :level, :newemail, :newfirst, :newlast, :newphone, :newcode, :recoverypin)");
								$insrt->execute(array(':newusername'=>"$newuser",':newpasshash'=>password_hash($newpass,PASSWORD_DEFAULT),':level'=>"$aelevel",':newemail'=>"$email",':newfirst'=>"$firstname",'newlast'=>"$lastname",':newphone'=>"$phone",':newcode'=>"$gencode",':recoverypin'=>"$recoveryPIN"));
							
							}else{
								//create and execute sql query to check if rep id entered exists
								$chk2= $conn->prepare("select username from users where code= :repid");
								$chk2->execute(array(':repid' => "$repid"));
			
								//check to see if user record was found
								if($chk2->rowcount() > 0){
									//rep id exists, continue
									$result=$chk2->fetch(PDO::FETCH_ASSOC);
									$rep=$result["username"];
								}else{
									//no rep id found
									$_SESSION['badcreate']="Referral code invalid. Enter the code your rep provided.";
									$conn=null;
									header("Location: ../newuser.html");
									exit();
								}			
								$insrt=$conn->prepare("insert into users (username, password, level, email, firstname, lastname, phone, rep, recovery) values (:newusername, :newpasshash, :level, :newemail, :newfirst, :newlast, :newphone, :rep, :recoverypin)");
								$insrt->execute(array(':newusername'=>"$newuser",':newpasshash'=>password_hash($newpass,PASSWORD_DEFAULT),':level'=>"$level",':newemail'=>"$email",':newfirst'=>"$firstname",'newlast'=>"$lastname",':newphone'=>"$phone",':rep'=>"$rep",'recoverypin'=>"$recoveryPIN"));
	
							}
							$conn=null;
							//clear saved variables
							unset($_SESSION['tmpuser']);
							unset($_SESSION['tmppass']);
							unset($_SESSION['tmprepass']);
							unset($_SESSION['tmpfname']);
							unset($_SESSION['tmplname']);
							unset($_SESSION['tmpemail']);
							unset($_SESSION['tmpphone']);
							unset($_SESSION['tmpref']);
							unset($_SESSION['tmpbtn']);
							$_SESSION['recentlycreated']="true";
	
							//send confirmation email then redirect to screen telling them
							$msg= "***PLEASE DO NOT REPLY TO THIS EMAIL IT WILL NEVER BE RECEIVED***\r\n\r\n";
							$msg.="Greetings ".$firstname.",\r\n\r\n";
							$msg.="Username: ".$newuser."\r\n\r\nYour account application with this username has been submitted for review.";
							$msg.=" Once approved, you can edit the details of your account in the \"Account\" tab";
							$msg.=" after logging in. You will receive email notification once your account has been approved. YOU WILL NOT BE ABLE TO LOG IN UNTIL YOUR ACCOUNT HAS BEEN APPROVED!\r\n\r\n";
							$msg.="Password Recovery PIN: ".$recoveryPIN."\r\n";
							$msg.="Do not lose this PIN, should you forget your password you will be prompted to enter this PIN in order to ";
							$msg.="create a new password. If you lose this PIN, you will need to contact your Accel Entertainment Representative to have them resend it.\r\n\r\n\r\n";
							$msg.="Thank you for choosing to support and advertise Accel Entertainment,\r\n\r\nThe Accel Entertainment Marketing Team\r\n";
							mail($email, "Account Created, Awaiting Approval", wordwrap($msg, 70), "From: <aemarketingtechsupport@gmail.com>"); 
							header("Location: ../usercreated.html");
							exit();
						}
					}catch(PDOException $e){
				
						//print error details to screen
						echo $result . "<br>" . $e->getMessage();
		
						//close database connection
						$conn = null;
					}
				}
			
			}else{
				//recaptcha wasnt valid
				$_SESSION['badcreate']="Complete the reCaptcha box before submitting.";
				header("Location: ../newuser.html");
				exit();
			}
		}else{
			//user never entered captcha
			$_SESSION['badcreate']="Click the reCaptcha box before submitting.";
			header("Location: ../newuser.html");
			exit();
		}
	}else{
		//request didnt come from newuser.html, redirect there
		header("Location: ../newuser.html");
		exit();
	}
?>
