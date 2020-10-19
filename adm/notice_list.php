<?php

$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');
$g5['title'] = $menu['menu300'][$s_key][1];
$curr_title = "공지 업데이트";

include_once('./admin.head.php');

$cur_url = "./notice_list.php";

?>
<script>
$(document).ready(function(){
  total_sum(1);
});
</script>


<div id="no_list">

  <div class="no_content">
    <div class="no_send">
      <form method="POST" name="send_sn" action="./adm_proc.php" onsubmit="return con_submit()"enctype="multipart/form-data"/>
      <input type="hidden" name="type" value="send_sn" />
      <input type="hidden" name="t_sum" />
      <input type="hidden" name="kind" value="notice" />
        <table>
          <tr>
            <td class="head_td">전송 구분</td>
            <td class="cont_td low"><span class="send_type_txt">어플리케이션 내</span></td>
          </tr>
          <tr>
            <td class="head_td">회원 구분</td>
            <td class="cont_td low">
              <label><input type="radio" name="mem_type" value="P" onclick="total_sum(1)" checked />농원</label>
              <label><input type="radio" name="mem_type" value="M" onclick="total_sum(2)"/>고객</label>
            </td>
          </tr>
          <tr>
            <td class="head_td">받는 회원</td>
            <td class="cont_td low">
              <input type="text" id="send_mem" placeholder="회원(00명)" disabled/>
            </td>
          </tr>
          <tr>
            <td class="head_td">알림 이미지</td>
            <td class="cont_td high1">
              <div class="file_box">
                <label for="sn_img">파일 업로드</label>
                <input type="file" name="sn_img" id="sn_img" />
                <input class="up_file" value="" disabled/>
              </div>
            </td>

          </tr>
          <tr>
            <td class="head_td">알림 내용</td>
            <td class="cont_td high2">
              <textarea class="sn_cont" name="t_cont" cols="10" rows="10"></textarea><br>
              <span class="counter">(0 / 최대 200자)</span>
            </td>
          </tr>
        </table>
        <div class="send_box">
          <input type="submit" class="s_btn" value="전송하기" />
        </div>

      </form>
    </div>

    <div class="no_history">
      <?=view_no_history()?>
    </div>

  </div>
</div>

<?php
include_once('./admin.tail.php');
?>
