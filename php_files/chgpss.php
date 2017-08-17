<?php
	session_start();
	if(isset($_SESSION['usr']) && isset($_SESSION['pswd'])){
		//user logged in proceed
	}else{
		//not logged in, redirect to login
		header("Location: ../login.html");
		exit();
	}
	if(count($_POST) > 0){
		$tmpoldpass= htmlspecialchars($_POST['currentpass']);
		$tmpnewpass= htmlspecialchars($_POST['newpass']);
		$tmprenewpass= htmlspecialchars($_POST['renewpass']);
		if(!password_verify($tmpoldpass, $_SESSION['pswd'])){
			$_SESSION['badchgpss']=true;
			header("Location: ../chgpss.html");
			exit();
		
		}else if($tmpnewpass!=$tmprenewpass){
			$_SESSION['badchgpss']=true;
			header("Location: ../chgpss.html");
			exit();
		}else{
			$servername = "localhost";
			$username = "root";
			$password = "raspberry";
			$dbname = "marketing";
			$uid=$_SESSION['uid'];
			try{

				//create a database connection
				$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				//create and execute sql query to update password
				$newhash= password_hash($tmpnewpass,PASSWORD_DEFAULT);
				$stmt= $conn->prepare("update users set password=:upass where userid=:userid");
				$stmt->execute(array(':upass' => "$newhash", ':userid' => "$uid"));
				$_SESSION['pswd'] = "$newhash";
				$conn=null;

				//redirect to success page
				header("Location: ../newpssvalidated.html");

			}catch(PDOException $e){
		
				//print error details to screen
				echo $result . "<br>" . $e->getMessage();

				//close database connection
				$conn = null;
			}

		}

	}else{
		//redirect to homepage, user typed the url in		
		header("Location: ../notfound.html");
		exit();
	}
?>