<?php
include_once('./_common.php');


switch ($type){
  case "p_delete":
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    $sql = "UPDATE f_partner SET live='N' WHERE idx={$idx}";
    $re = sql_query($sql);


    if($re==true){
      alert("삭제했습니다","member_list.php?s_key=1");
    }else if($re==false){
      alert("삭제에 실패했습니다",$referer);
    }
  break;

  case "m_delete":
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    $sql = "UPDATE f_member SET live='N' WHERE idx={$idx}";
    $re = sql_query($sql);


    if($re==true){
      alert("삭제했습니다","member_list.php?s_key=1");
    }else if($re==false){
      alert("삭제에 실패했습니다",$referer);
    }
  break;

  case "send_sn":

    $return_url = $_SERVER['HTTP_REFERER'];
    $file = $_FILES['sn_img'];
    $f_err = $file['error'];
    $f_size = $file['size'];
    $f_type = $file['type'];
    $f_tmp = $file['tmp_name'];
    $f_name = $file['name'];
    $allow_file = array("gif", "jpeg", "jpg", "png");


    // 파일 확장자 검사
    if($f_name){
      $box = explode(".",$f_name);
      $box_type = end($box);
      $box_re = in_array($box_type,$allow_file);
      if(!$box_re){
        $return_txt = "이미지 파일만 올려주세요.";
      }
    }

    // 파일 업로드 관련 처리 err 4 = 파일이름 없음(파일 안올릴때)
    if($f_err != 4){
      if($f_err == 1){
        $return_txt = "업로드에 실패했습니다.";
      }else{
        if($f_name && file_exists("./img/forest_adm/".$f_name)){
          $return_txt = "파일 이름 중복입니다.";
        }else{
          $re = move_uploaded_file($f_tmp, "./img/forest_adm/" . $f_name);
          if(!$re){
            $err_msg = "파일 업로드 실패입니다.";
          }
        }
      }
    }

    if($return_txt){
      alert($return_txt,$return_url);
    }else if($err_msg){
      alert($err_msg,$return_url);
    }else{

      // 알림과 공지 나눔
      if($kind == "notice"){
        $s_sql = "INSERT INTO f_notice VALUES ('','{$mem_type}','{$t_sum}','{$f_name}','{$f_size}','{$t_cont}',DEFAULT)";
      }else{
        $s_sql = "INSERT INTO f_sms_push VALUES ('','{$mem_type}','{$send_type}','{$t_sum}','{$f_name}','{$f_size}','{$t_cont}',DEFAULT)";
      }
      $s_re = sql_query($s_sql);

      if($s_re){
        // sms 및 푸쉬 전송처리






        // 정상적으로 전송이 되었다면 아래 코드 실행
        alert("정상적으로 전송되었습니다.",$return_url);
      }else{
        alert("전송에 실패했습니다.",$return_url);
      }

    }
  break;

}


?>
