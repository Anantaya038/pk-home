<?PHP 
  include ("../include/connect.php");
  include ("config.php");
 
  $msg = "";  
  function check_free_pt($pt_id, $start, $stop) { 
    global $con;
    global $msg;
 

    $sql = "select * from appointment where (pt_id ='$pt_id') ";
    $sql .= " AND (  ('$start' >= appointment_date AND  '$start' < appointment_date_stop ) OR(  '$stop' > appointment_date AND '$stop' <= appointment_date_stop ) OR(   '$start' <= appointment_date AND '$stop' >= appointment_date_stop ) )   ";
 
    $pass = 0;
    $query = mysqli_query ($con, $sql) or die ($sql);
    $rows = mysqli_num_rows ($query);
    if ($rows > 0) { 
        $pass = 1; 
        // echo "<BR>" . $sql;
    }
    while ($rec = mysqli_fetch_assoc ($query)) { 
      $msg .= "pt_id: $rec[appointment_id]";
      $msg .=  $rec[appointment_date] . " : " .  $rec[appointment_date_stop] . " : " .  $rec[patient_id] . " : " . $rec[pt_id] . " : " .$rec[appointment_type_id];
      $msg .=   "<BR>" ; 
      $msg .=  "<BR> $rec[allot]   $rec[c]";
    }
    return $pass;

  } 
  function check_free_patient ($patient_id, $start, $stop) { 
    global $con;
    global $msg;
 

    $sql = "select * from appointment where (patient_id ='$patient_id') ";
    $sql .= " AND (  ('$start' >= appointment_date AND  '$start' < appointment_date_stop ) OR(  '$stop' > appointment_date AND '$stop' <= appointment_date_stop ) OR(   '$start' <= appointment_date AND '$stop' >= appointment_date_stop ) )   ";
 
    $pass = 0;
    $query = mysqli_query ($con, $sql) or die ("1: " . $sql);
    $rows = mysqli_num_rows ($query);
    if ($rows > 0) { 
        $pass = 1; 
        // echo "<BR>" . $sql;
    }
    while ($rec = mysqli_fetch_assoc ($query)) { 
      $msg .= "Patient: $rec[appointment_id]";
      $msg .=  $rec[appointment_date] . " : " .  $rec[appointment_date_stop] . " : " .  $rec[patient_id] . " : " . $rec[pt_id] . " : " .$rec[appointment_type_id];
      $msg .=   "<BR>" ; 
      $msg .=  "<BR> $rec[allot]   $rec[c]";
    }
    return $pass;

  } 
  function check_free_pt2 ($pt_id, $start, $stop) { 
    global $con;
    global $msg;
 
    $sql = "select count(appointment.appointment_type_id) as c, appointment.appointment_type_id, appointment_type.allot from appointment left join appointment_type on appointment.appointment_type_id = appointment_type.appointment_type_id where (pt_id ='$pt_id') ";
    $sql .= " AND (  ('$start' >= appointment_date AND  '$start' < appointment_date_stop ) OR(  '$stop' > appointment_date AND '$stop' <= appointment_date_stop ) OR(   '$start' <= appointment_date AND '$stop' >= appointment_date_stop ) )   ";

    $sql .= " OR (pt_id =0) ";
    $sql .= " AND (  ('$start' >= appointment_date AND  '$start' < appointment_date_stop ) OR(  '$stop' > appointment_date AND '$stop' <= appointment_date_stop ) OR(   '$start' <= appointment_date AND '$stop' >= appointment_date_stop ) ) group by appointment_type_id ";
      
    $query = mysqli_query ($con, $sql) or die ($sql);
    $pass = 0;
     
    while ($rec = mysqli_fetch_assoc ($query)) { 
        $msg .= "PT";

        $msg .= $rec[appointment_id] . " : " . $rec[appointment_date] . " : " .  $rec[appointment_date_stop] . " : " .  $rec[patient_id] . " : " . $rec[pt_id] . " : " .  $rec[c]. " : " .$rec[appointment_type_id]. " : " .  $rec[allot];
        $msg .= "<BR>" ; 
        $msg .=  "<BR> $rec[allot]   $rec[c]";
        if ($rec[allot] == 0 ) $pass = 1; // ไม่ได้ 
        if ($rec[allot] <= $rec[c]) $pass = 1; // ไม่ได้ 
    }
     
    return $pass; 
  }
  function check_free_pt1 ($pt_id, $role, $start, $stop) { 
    global $con;
    global $msg;
 
    $sql = "select pt_id, role, count(appointment.appointment_type_id) as c, appointment.appointment_type_id, appointment_type.allot from appointment left join appointment_type on appointment.appointment_type_id = appointment_type.appointment_type_id where (pt_id ='$pt_id') ";
    $sql .= " AND (  ('$start' >= appointment_date AND  '$start' < appointment_date_stop ) OR(  '$stop' > appointment_date AND '$stop' <= appointment_date_stop ) OR(   '$start' <= appointment_date AND '$stop' >= appointment_date_stop ) )   ";

    $sql .= " OR (pt_id =0) ";
    $sql .= " AND (  ('$start' >= appointment_date AND  '$start' < appointment_date_stop ) OR(  '$stop' > appointment_date AND '$stop' <= appointment_date_stop ) OR(   '$start' <= appointment_date AND '$stop' >= appointment_date_stop ) ) group by appointment_type_id , role";
      
    // echo "<BR><BR>" . $sql;
    $query = mysqli_query ($con, $sql) or die ($sql);
    $pass = 0; 
    $case = 0;
    while ($rec = mysqli_fetch_assoc ($query)) {  
        // echo "<BR>";
        // var_dump ($rec);
        $msg .= "PT: $rec[pt_id] ";
        $msg .=    $rec[appointment_type_id] . ": Allot: $rec[allot] Counter: $rec[c], Role: $rec[role]";
        $msg .=   "<BR>" ;  
         
        if ($rec[allot] == 0 ) $pass = 1; // ไม่ได้ 

        if ($rec[role] == 1) $pass = 1; // เป็น main 

        if ($rec[role] <> 1 ) $case += $rec[c];
        if ($case >=  3) $pass = 1; // ผู้ช่วย+adviser ได้ไม่เกินสองนัด 
        
        if ($rec[allot] <= $rec[c]) $pass = 1; // ไม่ได้ 
    }   
    return $pass; 
  }
  if ($_POST[action] == "submit") {  
    $start = $_POST[appointment_date] . " " . $_POST[pt1_time_start];
    $stop = $_POST[appointment_date] . " " . $_POST[pt1_time_stop];

    $total_pass =0;
    foreach ($_POST[patient_id] as $patient_id)  {
      // echo "<BR>patient : $patient_id";
      $total_pass += check_free_patient ($patient_id, $start, $stop);
     }
     $i=1;
      $start1 = $_POST[appointment_date] . " " . $_POST[pt1_time_start];
      $stop1 = $_POST[appointment_date] . " " . $_POST[pt1_time_stop];

      $total_pass += check_free_pt ($_POST[pt1_id], $start1, $stop1);   // role pt 
      $start2 = $_POST[appointment_date] . " " . $_POST[pt2_time_start];
      $stop2 = $_POST[appointment_date] . " " . $_POST[pt2_time_stop];

      $total_pass += check_free_pt ($_POST[pt2_id], $start2, $stop2);   // role pt 
       
      $start3 = $_POST[appointment_date] . " " . $_POST[adviser_time_start];
      $stop3 = $_POST[appointment_date] . " " . $_POST[adviser_time_stop];

      $total_pass += check_free_pt ($_POST[advisor_id],  $start3, $stop3); // adviser pt
 
 
    if ($total_pass == 0) { 
      $msg .=  "<BR>Can Insert";
      $ref_appointment_id = 0;
      for ($i=0;$i<count($_POST[patient_id]);$i++) { 
          $role=1;
          $sql = "insert into appointment (pt_id, branch_id, patient_id, appointment_type_id,appointment_date, appointment_date_stop,role, ref_appointment_id) values (" . $_POST[pt1_id] .", '" . $_POST[branch_id] ."', " . $_POST[patient_id][$i] . ",'$_POST[appointment_type_id]','$start1', '$stop1', $role, $ref_appointment_id)";
            $query = mysqli_query ($con, $sql); 
           $ref_appointment_id = mysqli_insert_id ($con);
           if ($_POST[pt2_id] <>"") { 
               $sql = "insert into appointment (pt_id, branch_id, patient_id, appointment_type_id,appointment_date, appointment_date_stop,role, ref_appointment_id) values (" . $_POST[pt2_id] .", '" . $_POST[branch_id] ."', " . $_POST[patient_id][$i] . ",'$_POST[appointment_type_id]','$start2', '$stop2', $role, $ref_appointment_id)";
             $query = mysqli_query ($con, $sql); 
           }
        // for ($j=0;$j<count ($_POST[pt_id]);$j++) { 
        //   if ($j==0) $role = 1; else   $role = 2;
        //   $sql = "insert into appointment (pt_id, branch_id, patient_id, appointment_type_id,appointment_date, appointment_date_stop,role, ref_appointment_id) values (" . $_POST[pt_id][$j] .", '" . $_POST[branch_id][$j] ."', " . $_POST[patient_id][$i] . ",'$_POST[appointment_type_id]','$start', '$stop', $role, $ref_appointment_id)";
        //   // echo $sql;
        //    $query = mysqli_query ($con, $sql); 
        //    $ref_appointment_id = mysqli_insert_id ($con);
        // }
      }
      for ($i=0;$i<count($_POST[patient_id]);$i++) { 
        for ($j=0;$j<count ($_POST[adviser_id]);$j++) { 
          $role = 3;
          $sql = "insert into appointment (pt_id, branch_id, patient_id, appointment_type_id,appointment_date, appointment_date_stop,role,ref_appointment_id ) values (" . $_POST[adviser_id][$j] .", '" .  $_POST[branch_id] ."', " . $_POST[patient_id][$i] . ",'$_POST[appointment_type_id]','$start3', '$stop3', $role, $ref_appointment_id)";
            $query = mysqli_query ($con, $sql); 
        }
      }
      
      
        header ("location: index.php?msg=$msg");
    } 
    else 
    $msg .=  "<BR>Insert ไม่ได้";

  }
   
  

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Creative - Bootstrap 3 Responsive Admin Template">
  <meta name="author" content="GeeksLabs">
  <meta name="keyword" content="Creative, Dashboard, Admin, Template, Theme, Bootstrap, Responsive, Retina, Minimal">
  <link rel="shortcut icon" href="img/favicon.png">
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

  <script type="text/javascript" src="jquery.timepicker.js"></script>
  <link rel="stylesheet" type="text/css" href="jquery.timepicker.css">

  <script type="text/javascript" src="lib/bootstrap-datepicker.js"></script>
  <link rel="stylesheet" type="text/css" href="lib/bootstrap-datepicker.css">

  <script type="text/javascript" src="lib/site.js"></script>
  <link rel="stylesheet" type="text/css" href="lib/site.css">

  <title> Responsive Admin  </title>

  <!-- Bootstrap CSS -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <!-- bootstrap theme -->
  <link href="../css/bootstrap-theme.css" rel="stylesheet">
  <!--external css-->
  <!-- font icon -->

  <!-- Custom styles -->
  <link href="../css/style.css" rel="stylesheet">
  <link href="../css/style-responsive.css" rel="stylesheet" />

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
    
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.css" />
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>


<body> 
  <!-- container section start -->
  <section id="container" class="">
    <?PHP include ("../header.php"); ?>

    <!--sidebar start-->
    <aside>
      <div id="sidebar" class="nav-collapse ">
        <!-- sidebar menu start-->
        <?PHP include ("../menu.php");
        ?>
        <!-- sidebar menu end-->
      </div>
    </aside>

    <!--main content start-->
    <section id="main-content">
      <section class="wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-table"></i> Appointment</h3>
            <ol class="breadcrumb">
            <?PHP include ("breadcrumb.php"); ?>
            </ol>
          </div>
        </div>

        <!-- Form validations -->
        <?PHP if ($msg <> "") { ?>
            <div class="row">
              <div class="alert alert-danger" role="alert">
                <strong>ERROR!</strong> <?PHP echo $msg; ?>
              </div>
            </div>
        <?PHP } ?>

        <div class="row">
          <div class="col-lg-12">
            <section class="panel">
              <header class="panel-heading">
                New Appointment
              </header>
              <div class="panel-body">
                <div class="form">
                  <form class="form-validate form-horizontal" id="appointment_form" method="post" action="<?PHP echo $_SERVER[PHP_SELF]; ?>">
                  <div class="form-group ">
                      <label for="cpatient" class="control-label col-lg-2">คนไข้ <span class="required">*</span></label>
                      <div class="col-lg-10">
                      <select class="js-select2_patient form-control  " multiple="multiple" name="patient_id[]" required>
                         
                      </select> 
                      </div>
                    </div> 
                     
                    <div class="form-group ">
                      <label for="cpt" class="control-label col-lg-2">Appointment Date<span class="required">*</span></label>
                      <div class="col-lg-10"> 
                      <p id="datepairExample">

                      <input type="text" class="date start" name="appointment_date" autocomplete="off">
                      </p>
                      </div>
                    </div> 
                    <div class="form-group ">
                      <label for="cpt" class="control-label col-lg-2">PT<span class="required">*</span></label>
                      <div class="col-lg-10">
                      <select class="js-select2_pt js-select2_pt1 form-control  "  multiple="multiple" name="pt1_id"  required>
                         
                      </select> 
                      <p id="datepairExample1">

                      <input type=text name=pt1_time_start class="time start ui-timepicker-input" autocomplete="off" >
                      <input type=text name=pt1_time_stop class="time end ui-timepicker-input" autocomplete="off" >
                      </p>
                      </div>
                    </div> 
                    <div class="form-group ">
                      <label for="cpt" class="control-label col-lg-2">PT<span class="required">*</span></label>
                      <div class="col-lg-10">
                      <select class="js-select2_pt js-select2_pt2 form-control" multiple="multiple" name="pt2_id">
                         
                      </select> 
                      <p id="datepairExample2">

                      <input type=text name=pt2_time_start class="time start ui-timepicker-input" autocomplete="off" >
                      <input type=text name=pt2_time_stop class="time end ui-timepicker-input" autocomplete="off" >
                      </p>
                      </div>
                    </div> 
                    <div class="form-group ">
                      <label for="cpt" class="control-label col-lg-2">PT<span class="required">*</span></label>
                      <div class="col-lg-10">
                      <select class="js-select2_pt js-select2_pt3 form-control  " multiple="multiple"  name="adviser_id"  >
                         
                      </select> 
                      <p id="datepairExample3">
                      <input type=text name=adviser_time_start class="time start ui-timepicker-input" autocomplete="off" >
                      <input type=text name=adviser_time_sttop class="time end ui-timepicker-input" autocomplete="off" >
                      </p>
                      </div>
                    </div> 
                    <div class="form-group ">
                      <label for="appointment_type" class="control-label col-lg-2">appointment_type<span class="required">*</span></label>
                      <div class="col-lg-10">
                      
                        <select name="appointment_type_id">
                     <?PHP   
                        foreach ($a_appointment_type as $key=>$value) { if (  $key   <>  $_GET[type] ) { ?>
                        <option  name="appointment_type"  value=<? echo $key; ?> <?PHP if ($key == $appointment_type_id) echo 'selected'; ?>><?PHP echo  $value; ?></option>
                        <? } } ?>
                        </select>
                      </div>
                    </div> 
 
                    <div class="form-group">
                      <div class="col-lg-offset-2 col-lg-10">
                          <input type=text name=branch_id value='<?PHP echo $_SESSION[branch_id]; ?>'>
                        <button class="btn btn-primary" type="submit" name="action" value="submit">Save</button>
                        <button class="btn btn-default" type="button">Cancel</button>
                      </div>
                    </div>
                  </form>
                </div>

              </div>
            </section>
          </div>
        </div>
         
        <div class="row">
          <div class="col-lg-12">
            <section class="panel">
              <div id="calendar"></div>
            </section>
          </div>
        </div>
        
      </section>
    </section>
    <!--main content end-->
    <div class="text-right">
      <div class="credits">
           
          Codeed by Bangkok Solutions Co., Ltd.  <a href="../login.php?logout=y">Log Out</a>
        </div>
    </div>
  </section>
  <!-- container section end -->
  <!-- javascripts -->
 
  <script src="../js/bootstrap.min.js"></script>
  <!-- nicescroll -->
  <script src="../js/jquery.scrollTo.min.js"></script>
  <script src="../js/jquery.nicescroll.js" type="text/javascript"></script>
  <!-- custome script for all page -->
  <script src="../js/scripts.js"></script> 

 
<script src="http://jonthornton.github.io/Datepair.js/dist/datepair.js"></script>
<script src="http://jonthornton.github.io/Datepair.js/dist/jquery.datepair.js"></script>

<script>
  $(document).ready(function () {
    $('#calendar').fullCalendar({  
      editable: true,
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
      },
      // events:'http://mba.bkksol.com/uiPK/apipk/apical.php?mode=select&name=' + $('#name').val(),  
    })
  });
</script>

<script>
                $('#datepairExample1 .time').timepicker({
                    'showDuration': true,
                    'minTime': '8:00am',
                    'maxTime': '7:30pm',
                    'timeFormat': 'G:i'
                });
                $('#datepairExample2 .time').timepicker({
                    'showDuration': true,
                    'minTime': '8:00am',
                    'maxTime': '7:30pm',
                    'timeFormat': 'G:i'
                });
                $('#datepairExample3 .time').timepicker({
                    'showDuration': true,
                    'minTime': '8:00am',
                    'maxTime': '7:30pm',
                    'timeFormat': 'G:i'
                });
                $('#datepairExample .time').timepicker({
                    'showDuration': true,
                    'minTime': '8:00am',
                    'maxTime': '7:30pm',
                    'timeFormat': 'G:i'
                });

                $('#datepairExample .date').datepicker({
                    'format': 'yyyy/m/d',
                    'autoclose': true
                });

                $('#datepairExample').datepair();
            
</script>

<script type="text/javascript">
  $(document).ready(function() {

    $(".js-select2_patient").select2({
			  ajax: {
					url: "json_data_patient.php",/* Url ที่ต้องการส่งค่าไปประมวลผลการค้นข้อมูล*/
					dataType: 'json',
					delay: 0,
					data: function (params) {
						return {
						q: params.term, // ค่าที่ส่งไป
						page: params.page
						};
					},
					processResults: function (data, params) { 
						params.page = params.page || 1;
						return {
							results: data.items,
							pagination: {
								more: (params.page * 30) < data.total_count
							}
						};
					},
					cache: true
			  },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        maximumSelectionSize: 4,
			  // minimumInputLength: 1,
			  templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
       
    });
    
    $(".js-select2_patient").on('select2:select', function (e) {
      var data = e.params.data;
      console.log(data);
      $.ajax({
				type:"GET",
				url:"json_data_appointment.php",
        data:{ 
          "patient_id": data.id
        },
				success: function(response){
          var response = JSON.parse(response);
          console.log( response ); 
          response.forEach((element) => {
            let eventData = {
              color: 'pink',
              patient_id: element.patient_id,
              title: "คนไข้ชื่อ "+element.patient_firstname+" "+element.patient_lastname,
              start: moment(element.appointment_date),
              end: moment(element.appointment_date_stop)
            };
            console.log( eventData ); 
            $('#calendar').fullCalendar('renderEvent', eventData, true);
          });
        },
      });
    });

    $(".js-select2_patient").on('select2:unselect', function (e) {
      var data = e.params.data;
      $('#calendar').fullCalendar('removeEvents', eventObject => eventObject.patient_id == data.id);
    });
    
    $(".js-select2_pt").select2({
			  ajax: {
					url: "json_data_pt.php",/* Url ที่ต้องการส่งค่าไปประมวลผลการค้นข้อมูล*/
					dataType: 'json',
					delay: 0,
					data: function (params) {
						return {
						q: params.term, // ค่าที่ส่งไป
						page: params.page
						};
					},
					processResults: function (data, params) { 
						params.page = params.page || 1;
						return {
							results: data.items,
							pagination: {
								more: (params.page * 30) < data.total_count
							}
						};
					},
					cache: true
			  },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        maximumSelectionSize: 4,
			  // minimumInputLength: 1,
			  templateResult: formatRepo, // omitted for brevity, see the source of this page
			  templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
			});
    });

    $(".js-select2_pt").on('select2:select select2:unselect', function (e) {
      $('#calendar').fullCalendar('removeEvents');
      data1 = $(".js-select2_pt1").select2('data');
      data2 = $(".js-select2_pt2").select2('data');
      data3 = $(".js-select2_pt3").select2('data');
      concat = [].concat(data1,data2,data3);
      data = [];
      concat.forEach((item) => {
        data.push(item.id);
      })
      data = Array.from(new Set(data));
      console.log(data);
      data.forEach((item) => {
        $.ajax({
          type:"GET",
          url:"json_data_appointment.php",
          data:{ 
            "pt_id": item.id
          },
          success: function(response){
            var response = JSON.parse(response);
            // console.log( response ); 
            response.forEach((element) => {
              let eventData = {
                color: 'yellow',
                pt_id: element.pt_id,
                title: "PT ชื่อ "+element.pt_firstname+" "+element.pt_lastname,
                start: moment(element.appointment_date),
                end: moment(element.appointment_date_stop)
              };
              // console.log( eventData ); 
              $('#calendar').fullCalendar('renderEvent', eventData, true);
            });
          },
        });
      });
    });

    // $(".js-select2_pt").on('select2:unselect', function (e) {
    //   console.log($(".js-select2_pt").select2('data'));
    //   var data = e.params.data;
    //   $('#calendar').fullCalendar('removeEvents', eventObject => eventObject.pt_id == data.id);
    // });

 		function formatRepo (repo) {
 		  if (repo.loading) return repo.text;
 		  var markup = "<div class='select2-result-repository clearfix'>" +
			"<div class='select2-result-repository__meta'>" +
			  "<div class='select2-result-repository__title'>" + repo.value + "</div></div></div>";
 		  return markup;
		}
 		function formatRepoSelection (repo) {
		  return repo.value || repo.text;
		}

</script>


</body>
</html>
