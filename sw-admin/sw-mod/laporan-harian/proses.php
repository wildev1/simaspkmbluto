<?php session_start(); error_reporting(0);
if(empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php'); 

switch (@$_GET['action']){
/* -------  LOAD LAPORAN ----------*/
case 'laporan':

      $name     = strip_tags($_POST['name']);

      if($name==''){
        $filter_employees ='';
      }else{
        $filter_employees ="WHERE employees.id='$name'";
      }


      if(isset($_POST['month']) OR isset($_POST['year'])){
          $bulan   = date ($_POST['month']);
          $month_en = bulan_indo2((int)$bulan);
      } 
      else{
          $bulan  = date ("m");
      }
        $hari       = date("d");
        $tahun      = date("Y");
        $jumlahhari = date("t",mktime(0,0,0,$bulan,$hari,$tahun));
        $s          = date ("w", mktime (0,0,0,$bulan,1,$tahun));

      
      echo'
        <div class="table-responsive" style="overflow-x: auto!important;">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th rowspan="2" width="40" class="text-center" style="vertical-align: middle;">No</th>
              <th colspan="2" rowspan="2" style="vertical-align: middle;">Nama Pegawai</th>
              <th class="text-center" colspan="'.$jumlahhari.'">'.$month_en.'</th>
              <th class="text-center" colspan="4">Keterangan</th>
            </tr>
            <tr>';
            $sum = 0;
            $libur = 0;
            for ($d=1;$d<=$jumlahhari;$d++) {
                    $warna      = '';
                    $background = '';
              if (date("l",mktime (0,0,0,$bulan,$d,$tahun)) == "Sunday" OR date("l",mktime (0,0,0,$bulan,$d,$tahun)) == "Saturday") {
                    $warna='color:white';
                    $background ='background:#FF0000';
                    $sum++;
                    $status_hadir ='Libur';
                }else{
                    $date_month_year = ''.$year.'-'.$bulan.'-'.$d.'';
                    $query_holiday="SELECT holiday_date FROM holiday WHERE holiday_date='$date_month_year'";
                    $result_holiday = $connection->query($query_holiday);
                      if($result_holiday->num_rows > 0){
                        $warna='color:white';
                        $background ='background:#FF0000';
                        $libur++;
                        $status_hadir ='Lembur';
                      }
                }

              echo'
              <th width="50" class="text-center" style="'.$warna.'; '.$background.'">'.$d.'</th>';
            }
            echo'
            <th width="50" class="text-center">H</th>
            <th width="50" class="text-center">I</th>
            <th width="50" class="text-center">A</th>
            <th width="50" class="text-center">S</th>
            </tr>
          </thead>
          <tbody>';
          $limit =20; 
          if(isset($_GET['halaman'])){
            $halaman = mysqli_real_escape_string($connection,$_GET['halaman']);}
          else{$halaman = 1;} $offset = ($halaman - 1) * $limit;

          $query ="SELECT id,employees_name FROM employees $filter_employees ORDER BY id ASC LIMIT $offset, $limit";
          $result = $connection->query($query);$no=0;
          $nomor = $halaman_awal+1;
          while ($row = $result->fetch_assoc()){$no++;
            echo'
            <tr>
              <td rowspan="2" class="text-center">'.$no.'</td>
              <td rowspan="2" width="150">'.$row['employees_name'].'</td>
              <td width="60">Masuk</td>';
               for ($d=1;$d<=$jumlahhari;$d++) {
                $date_month_year = ''.$year.'-'.$bulan.'-'.$d.'';
                 if(isset($_POST['month']) OR isset($_POST['year'])){
                    $month = $_POST['month'];
                    $year  = $_POST['year'];
                    $filter ="presence_date='$date_month_year' AND MONTH(presence_date)='$month' AND year(presence_date)='$year'";
                  } 
                  else{
                    $filter ="presence_date='$date_month_year' AND MONTH(presence_date) ='$month'";
                  }

                $query_absen ="SELECT presence_id,presence_date,time_in FROM presence WHERE $filter AND employees_id='$row[id]' ORDER BY presence_id DESC";
                $result_absen = $connection->query($query_absen);
                $row_absen = $result_absen->fetch_assoc();
                if($row_absen['time_in']==NULL){
                  $jam_masuk ='<span class="text-red"><i class="fa fa-times" aria-hidden="true"></i></span>';
                }else{
                  $jam_masuk = $row_absen['time_in'];
                }
                echo'
                <td class="text-center">'.$jam_masuk.'</td>';
              }

             if(isset($_POST['month']) OR isset($_POST['year'])){
                $month = $_POST['month'];
                $year  = $_POST['year'];
                $filter ="MONTH(presence_date)='$month' AND year(presence_date)='$year'";
              } 
              else{
                $filter ="MONTH(presence_date)='$month' AND year(presence_date)='$year'";
              }

            $query_hadir ="SELECT presence_id FROM presence WHERE $filter AND present_id='1' ORDER BY presence_id DESC";
            $hadir= $connection->query($query_hadir);

            $query_alpha="SELECT presence_id FROM presence WHERE $filter AND employees_id='$row[id]'";
            $alpha = $connection->query($query_alpha);
            $alpha = $jumlahhari - $alpha->num_rows - $sum - $libur;

            $query_sakit="SELECT presence_id FROM presence WHERE $filter AND employees_id='$row[id]' and present_id='2'";
            $sakit = $connection->query($query_sakit);

            $query_izin="SELECT presence_id FROM presence WHERE $filter AND employees_id='$row[id]' and present_id='3'";
            $izin = $connection->query($query_izin);

            echo'
            <th width="50" rowspan="2" class="text-center">'.$hadir->num_rows.'</th>
            <th width="50" rowspan="2" class="text-center">'.$izin->num_rows.'</th>
            <th width="50" rowspan="2" class="text-center">'.$alpha.'</th>
            <th width="50" rowspan="2" class="text-center">'.$sakit->num_rows.'</th>
            </tr>
            <tr>
              <td width="60">Pulang</td>';
              for ($d=1;$d<=$jumlahhari;$d++) {
                $date_month_year = ''.$year.'-'.$bulan.'-'.$d.'';
                 if(isset($_POST['month']) OR isset($_POST['year'])){
                    $month = $_POST['month'];
                    $year  = $_POST['year'];
                    $filter ="presence_date='$date_month_year' AND MONTH(presence_date)='$month' AND year(presence_date)='$year'";
                  } 
                  else{
                    $filter ="presence_date='$date_month_year' AND MONTH(presence_date) ='$month'";
                  }

                $query_absen ="SELECT presence_id,presence_date,time_in,time_out FROM presence WHERE $filter AND employees_id='$row[id]' ORDER BY presence_id DESC";
                $result_absen = $connection->query($query_absen);
                $row_absen = $result_absen->fetch_assoc();
                if($row_absen['time_in']==NULL){
                  $jam_pulang ='<span class="text-red"><i class="fa fa-times" aria-hidden="true"></i></span>';
                }else{
                  $jam_pulang = $row_absen['time_out'];
                }
                echo'
                <td class="text-center">'.$jam_pulang.'</td>';
              }
              echo'
            </tr>';
          }
          echo'
          </tbody>
      </table>
        <p><span class="label label-info">H: Hadir</span> <span class="label label-success">I: Izin</span> <span class="label label-danger">A: Alpha</span> <span class="label label-warning">S: Sakit</span></p>
          <nav>
            <ul class="pagination justify-content-center">';
            $query = mysqli_query($connection,"SELECT COUNT(id) AS jumData FROM employees $pagination") or die ('Pagination error');
                      $data  = mysqli_fetch_assoc($query);
                      $jumData = $data['jumData'];
                      $jumPage = ceil($jumData/$limit);
                  //menampilkan link << Previou
                  if ($halaman > 1){echo '<li class="page-item"><a class="page-link btn-pagination" href="javascript:void(0);" data-id="'.($halaman-1).'">«</a></li>';}
                  //menampilkan urutan paging
                      for($i = 1; $i <= $jumPage; $i++){
                  //mengurutkan agar yang tampil i+3 dan i-3
                      if ((($i >= $halaman - 1) && ($i <= $halaman + 4)) || ($i == 1) || ($i == $jumPage)){
                          if($i==$jumPage && $halaman <= $jumPage-4)
                              echo'<li class="disabled"><a href="#">..</a></li>';
                              if ($i == $halaman) echo '<li class="active"><a href="#">'.$i.'</a></li>';
                              else echo '<li class="page-item"><a class="page-link btn-pagination"  href="javascript:void(0)" data-id="'.$i.'">'.$i.'</a></li>';
                      if($i==1 && $halaman >= 4) echo '<li class="disabled"><a href="#">..</a></li>';
                  }}
                  //menampilkan link Next >>
                  if ($halaman < $jumPage){echo'<li class="page-item"><a class="page-link btn-pagination" href="javascript:void(0);" data-id="'.($halaman+1).'">»</a></li>';
                  }

            echo'
            </ul>
          </nav>

        <div>';


break;

}

}
