<?PHP 
include ("connect.php");

//login
session_start(); 
if(isset($_POST['username'])){
 $user=$_POST['username']; 
 $pass=$_POST['password'];
 $users_id=$_POST['users_id'];
 $sql = "select * from users where username='$user' and password='$pass' ";

 $select_data=mysqli_query($conn,$sql);
 $fetch = $select_data->fetch_assoc();
 
 if($fetch) {
  $_SESSION['username']=$fetch['username'];
  $_SESSION['users_id']=$fetch['users_id']; 
  $result[][status] = 1;
  $result[][msg] = "login completed";
  $result[][user_id]=$fetch['users_id']; 
  $_SESSION["user_id"]=$fetch['users_id'];
 }else{
	$result[][status] = 0;
  	$result[][msg] = "Username or password Incorrect!";
 }
  echo json_encode ($result);

}  


//changestatus
if ($_GET[mode] == "status_change") { 
	$sql = "update appointment set  status_id='1'";
	$sql .= " where appointment_id='$_GET[appointment_id]'";
	
	mysqli_query ($conn, $sql);
	echo $sql;
	$result[msg] = "update completed";
	header("Location: http://mba.bkksol.com/uiPK/apipk/page2.html?users_id=".$_SESSION['user_id']."");
	echo json_encode ($result);
}


//logout
if( isset($_GET['argument']) && $_GET['argument'] == 'logOut') {
	session_destroy();
	header("location: http://mba.bkksol.com/uiPK/apipk/index.html");
	exit;

}


//datetime, split date
if ($_GET[mode] == "readitto") { 
	$sqldata="SELECT*	FROM appointment
	WHERE appointment_id= ".$_GET['appointment_id']." ";
		 
		$query = mysqli_query ($conn, $sqldata);	
		if (mysqli_num_rows($query) > 0) {
			while ($rec = mysqli_fetch_assoc ($query)) { 
					$result[] = $rec;
			}
			}else {
					$result = [];
			}
		echo json_encode ($result);
	
	}

	if($_GET['mode']=="getmapping"){
		$sql ="SELECT * FROM users where users_id=102"; 
		$query = mysqli_query ($conn, $sql);

		if (mysqli_num_rows($query) > 0) {
			while ($rec = mysqli_fetch_assoc ($query)) { 
					$result[] = $rec;
			}
			}else {
					$result = [];
			}
	
		echo json_encode ($result);
	
	}
	

// page2
if ($_GET[mode] == "read") { 
$sqldata="SELECT*,
   appointment.patient_id, patient.users_id, patient_user.firstname, patient_user.lastname, appointment.appointment_date, appointment.appointment_date_stop
FROM appointment
LEFT JOIN pt ON appointment.pt_id = pt.pt_id
LEFT JOIN users AS pt_users ON pt.users_id = pt_users.users_id
LEFT JOIN patient ON appointment.patient_id = patient.patient_id
LEFT JOIN users AS patient_user ON patient.users_id = patient_user.users_id
WHERE pt_users.users_id = ".$_SESSION['user_id']." AND status_id = 0";
   
  $query = mysqli_query ($conn, $sqldata);

	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}

	echo json_encode ($result);

}


//ประวัติการรักษา page44
if ($_GET[mode] == "readhistory" and $_GET[users_id] <> "") {  
	$sqldata="SELECT *, treatment.treatment_id, patient_user.firstname, patient_user.lastname, treatment.treatment_date, treatment.weight, treatment.height, treatment.in_hr, treatment.in_bp, treatment.out_hr, treatment.out_bp, treatment.s_text, treatment.o_text, treatment.a_text, treatment.p_text 
	FROM treatment 
	LEFT JOIN pt ON treatment.pt_id = pt.pt_id 
	LEFT JOIN users AS pt_users ON pt.users_id = pt_users.users_id 
	LEFT JOIN patient ON treatment.patient_id = patient.patient_id 
	LEFT JOIN users AS patient_user ON patient.users_id = patient_user.users_id 
	WHERE patient.users_id =".$_GET[users_id]."
	ORDER BY `treatment`.`treatment_id`  DESC";
	
	 $query = mysqli_query ($conn, $sqldata);
	 
	 if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
	 $data[counters] = $result;
	 // $_SESSION["user_id"]=user_id;
	 echo json_encode ($data);
 
 }


 //get ข้อมูลการตรวจ page44
 if ($_GET[mode] == "readedit" and $_GET[treatment_id] <> "") {  
	$sqldata="SELECT treatment.treatment_id,treatment.weight,treatment.height,treatment.in_hr,treatment.out_hr,treatment.in_bp,treatment.out_bp,treatment.s_text,treatment.o_text,treatment.a_text,treatment.p_text 
	FROM treatment WHERE treatment_id= '$_GET[treatment_id]'";
	
	 $query = mysqli_query ($conn, $sqldata);
	 
	 if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
	 $data[counters] = $result;
	 // $_SESSION["user_id"]=user_id;
	 echo json_encode ($data);
 
 }

//uplode img
 if($_POST[mode]== "image"){

	$sql = "INSERT INTO `picture`(`img`) VALUES ('$_POST[msg]')";
	
	$query = mysqli_query ($conn, $sql);
	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
	echo json_encode ($result);	
}


//get map lat lng
if($_POST[mode]== "map"){

	$sql = "UPDATE `users` SET`latitudes`='$_POST[latitudes]'
	,`longitudes`='$_POST[longitudes]' WHERE users_id=".$_GET[users_id]."";
	
	$query = mysqli_query ($conn, $sql);
	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
//   header("Location: http://mba.bkksol.com/uiPK/apipk/page2.html?users_id=".$_SESSION['user_id']."");
	echo json_encode ($result);	
}



//update วันเวลา page2
 if ($_POST[mode] == "updatedatetime") { 

	$sql = "UPDATE `appointment` SET`appointment_date_stop`='$_POST[appointment_date_mounth]"." ".$_POST[appointment_date_end_time]."'
	,`appointment_date`='$_POST[appointment_date_mounth]"." ".$_POST[appointment_date_time]."' WHERE appointment_id=$_POST[appointment_id] ";
	
	$query = mysqli_query ($conn, $sql);
	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
  
	echo json_encode ($result);


}





//update+status change page2
 if ($_GET['mode']=="change_date" ){

	if("$_GET[status_change]"==0){
		// `appointment_date`='$_GET[appointment_date]' ,
	$result = $sql="UPDATE `appointment` SET `status_change`=1 WHERE appointment_id=$_GET[appointment_id] ";
	}else if("$_GET[status_change]"==1){
	$result = $sql="UPDATE `appointment` SET`appointment_date_stop`='$_GET[appointment_date_stop]'
	,`appointment_date`='$_GET[appointment_date]',
	`status_change`=0 WHERE appointment_id=$_GET[appointment_id] ";
echo "$_GET[reasonx]";
	}
	$query = mysqli_query ($conn, $sql);
	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				header("Location: http://mba.bkksol.com/uiPK/apipk/page2.html?users_id=".$_SESSION['user_id']."");
				$result[msg] = "update completed";
		}
		}else {
				$result = [];
		}
	

echo json_encode ($result);
}



// อัพเดตข้อมูลการตรวจ page44
if ($_POST[mode] == "updatetreatment") { 
	date_default_timezone_set('Asia/Bangkok');
	$date2 = date("Y-m-d H:i:s");

	$sql = "update treatment set";
	$sql.=" treatment_date='$date2'";
	$sql.=", weight='$_POST[weight2]'";
	$sql .= ", height = '$_POST[height2]'";
	$sql .= ", in_hr = '$_POST[in_hr2]'";
  $sql .= ", out_hr = '$_POST[out_hr2]'"; 
 	$sql .= ", in_bp = '$_POST[in_bp2]'"; 
	$sql .= ", out_bp = '$_POST[out_bp2]'"; 
	$sql .= ", s_text = '$_POST[s_text2]'"; 
	$sql .= ", o_text = '$_POST[o_text2]'"; 
	$sql .= ", a_text = '$_POST[a_text2]'"; 
	$sql .= ", p_text = '$_POST[p_text2]'"; 
	$sql .= " where treatment_id ='$_POST[treatment_id]'";
	
	$query = mysqli_query ($conn, $sql);
	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
  
	echo json_encode ($result);


}


//เพิ่มข้อมูลการตรวจ page44
 if ($_POST[mode]=="insert" ){
	
		date_default_timezone_set('Asia/Bangkok');
		$date = date("Y-m-d H:i:s");
	
		$sql="INSERT INTO`treatment`(`treatment_date`,`pt_id`,`patient_id`,`branch_id`,`appointment_id`,`weight`,`room`,`height`,`in_hr`,`out_hr`,`in_bp`,`out_bp`,`s_text`,`o_text`,`a_text`,`p_text`,`parent_treatment_id`,`discharge`,`cert_id`,`created_at`)
									VALUES('$date','$_POST[pt_id]','$_POST[patient_id]','$_POST[branch_id]','$_POST[appointment_id]','$_POST[weight]','5','$_POST[height]','$_POST[in_hr]','$_POST[out_hr]','$_POST[in_bp]','$_POST[out_bp]','$_POST[s_text]','$_POST[o_text]','$_POST[a_text]','$_POST[p_text]','0','0','0','2019-02-15 00:00:00') ";
		$query = mysqli_query ($conn, $sql);

		if (mysqli_num_rows($query) > 0) {
			while ($rec = mysqli_fetch_assoc ($query)) { 
					$result[] = $rec;
			}
			}else {
					$result = [];
			}
		
		echo json_encode ($result);
	
	
	}



//get information page3
if ($_GET[mode] == "update" and $_GET[users_id] <> "") { 
	$sql = "select * from patient left join users on patient.users_id=users.users_id where patient.users_id='$_GET[users_id]'";
	$query = mysqli_query ($conn, $sql);

	$fieldlist = array ("users_id", "firstname", "lastname", "nickname", "card_id","email", "birthdate", "mobile","address","sub_district","district","province","postcode");
	
	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
	echo json_encode ($result); 
 
}



//update information page3 
if ($_POST[mode] == "update") { 

	$sql = "update patient left join users on patient.users_id=users.users_id ";
	$sql.="set firstname='$_POST[firstname]'";
	$sql .= ", lastname = '$_POST[lastname]'";
	$sql .= ", nickname = '$_POST[nickname]'";
  $sql .= ", card_id = '$_POST[card_id]'"; 
 	$sql .= ", email = '$_POST[email]'"; 
	$sql .= ", birthdate = '$_POST[birthdate]'"; 
	$sql .= ", mobile = '$_POST[mobile]'"; 
	$sql .= ", address = '$_POST[address]'"; 
	$sql .= ", mobile = '$_POST[mobile]'"; 
	$sql .= ", sub_district = '$_POST[sub_district]'"; 
	$sql .= ", district = '$_POST[district]'"; 
	$sql .= ", province = '$_POST[province]'"; 
	$sql .= ", postcode = '$_POST[postcode]'"; 
	$sql .= " where patient.users_id='$_POST[users_id]'";
	
	$query = mysqli_query ($conn, $sql);
	if (mysqli_num_rows($query) > 0) {
		while ($rec = mysqli_fetch_assoc ($query)) { 
				$result[] = $rec;
		}
		}else {
				$result = [];
		}
  
	echo json_encode ($result);


}



if ($_GET[mode] == "delete") { 
    $sql = "DELETE FROM customer_pk WHERE cus_id = '$_GET[cus_id]'";

    mysqli_query ($conn, $sql);

    if ($conn->query($sql) === TRUE) {
		header("Location: http://mba.bkksol.com/uiPK/PKatHome/page2.html");
		
    } else {
        echo "Error deleting record: " . $conn->error;
    }

}
?> 