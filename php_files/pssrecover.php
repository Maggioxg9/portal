<?php
	session_start();

	if(count($_POST) > 0){

		//$secret= "6Ld-CA4TAAAAAER5Lq6-85en9z0XjFVI3sX1ure5";
		//$response = null;
	
		//$recaptcha = new ReCaptcha($secret);
	
		
		// isset($_POST["g-recaptcha-response"])
		if(true){
			//$response= $recaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]);
			//if($response !=null && $response->success){
			if(true){
				if(empty( $_POST["userfield"])||empty( $_POST["psspin"])||empty( $_POST["newpass"])||empty( $_POST["renewpass"])){
					$_SESSION['badrecover']="Try again. Make sure the PIN is valid and the new passwords match for the identified user.";
					header("Location: ../recover.html");
					exit();
				}else if( $_POST["newpass"]!= $_POST["renewpass"]){
					$_SESSION['badrecover']="Try again. Make sure the PIN is valid and the new passwords match for the identified user.";
					header("Location: ../recover.html");
					exit(); 
				}else{
					//everything is valid, assign variables
	
					//database constants
					$servername = "localhost";
					$username = "root";
					$password = "raspberry";
					$dbname = "marketing";
					$newuser= htmlspecialchars($_POST["userfield"]);
					$newpass= htmlspecialchars($_POST["newpass"]);
					$pin =  htmlspecialchars($_POST["psspin"]);
				
					try{
		
						//create a database connection
						$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
						$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					
						//create and execute sql query to check if username exists
						$stmt= $conn->prepare("select userid, recovery from users where username= :name");
						$stmt->execute(array(':name' => "$newuser"));
						$result=$stmt->fetch(PDO::FETCH_ASSOC);
						
						//check to see if user exists
						if($stmt->rowcount() > 0){
							if($pin==$result["recovery"]){
								//update password
								$tmpuserid=$result["userid"];
								$chk= $conn->prepare("update users set password=:pass where userid=:userid");
								$chk->execute(array(':userid' => "$tmpuserid",':pass'=> password_hash($newpass,PASSWORD_DEFAULT)));
			
								$conn=null;
								header("Location: ../recoveryvalidated.html");	
								exit();				
							}else{
								//bad PIN
								$conn=null;
								$_SESSION['badrecover']="Try again. Make sure the PIN is valid and the new passwords match for the identified user.";
								header("Location: ../recover.html");
								exit(); 
							}
						}else{
							//no username found
							$conn=null;
							$_SESSION['badrecover']="Try again. Make sure the PIN is valid and the new passwords match for the identified user.";
							header("Location: ../recover.html");
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
				$_SESSION['badrecover']="Complete the reCaptcha box before submitting.";
				header("Location: ../recover.html");
				exit();
			}
		}else{
			//user never entered captcha
			$_SESSION['badrecover']="Click the reCaptcha box before submitting.";
			header("Location: ../recover.html");
			exit();
		}
	}else{
		//request didnt come from newuser.html, redirect there
		header("Location: ../recover.html");
		exit();
	}
?>