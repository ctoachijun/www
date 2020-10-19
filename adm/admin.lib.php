<?php
if (!defined('_GNUBOARD_')) exit;

/*
// 081022 : CSRF 방지를 위해 코드를 작성했으나 효과가 없어 주석처리 함
if (!get_session('ss_admin')) {
    set_session('ss_admin', true);
    goto_url('.');
}
*/

// 스킨디렉토리를 SELECT 형식으로 얻음
function get_skin_select($skin_gubun, $id, $name, $selected='', $event='')
{
    global $config;

    $skins = array();

    if(defined('G5_THEME_PATH') && $config['cf_theme']) {
        $dirs = get_skin_dir($skin_gubun, G5_THEME_PATH.'/'.G5_SKIN_DIR);
        if(!empty($dirs)) {
            foreach($dirs as $dir) {
                $skins[] = 'theme/'.$dir;
            }
        }
    }

    $skins = array_merge($skins, get_skin_dir($skin_gubun));

    $str = "<select id=\"$id\" name=\"$name\" $event>\n";
    for ($i=0; $i<count($skins); $i++) {
        if ($i == 0) $str .= "<option value=\"\">선택</option>";
        if(preg_match('#^theme/(.+)$#', $skins[$i], $match))
            $text = '(테마) '.$match[1];
        else
            $text = $skins[$i];

        $str .= option_selected($skins[$i], $selected, $text);
    }
    $str .= "</select>";
    return $str;
}

// 모바일 스킨디렉토리를 SELECT 형식으로 얻음
function get_mobile_skin_select($skin_gubun, $id, $name, $selected='', $event='')
{
    global $config;

    $skins = array();

    if(defined('G5_THEME_PATH') && $config['cf_theme']) {
        $dirs = get_skin_dir($skin_gubun, G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR);
        if(!empty($dirs)) {
            foreach($dirs as $dir) {
                $skins[] = 'theme/'.$dir;
            }
        }
    }

    $skins = array_merge($skins, get_skin_dir($skin_gubun, G5_MOBILE_PATH.'/'.G5_SKIN_DIR));

    $str = "<select id=\"$id\" name=\"$name\" $event>\n";
    for ($i=0; $i<count($skins); $i++) {
        if ($i == 0) $str .= "<option value=\"\">선택</option>";
        if(preg_match('#^theme/(.+)$#', $skins[$i], $match))
            $text = '(테마) '.$match[1];
        else
            $text = $skins[$i];

        $str .= option_selected($skins[$i], $selected, $text);
    }
    $str .= "</select>";
    return $str;
}


// 스킨경로를 얻는다
function get_skin_dir($skin, $skin_path=G5_SKIN_PATH)
{
    global $g5;

    $result_array = array();

    $dirname = $skin_path.'/'.$skin.'/';
    if(!is_dir($dirname))
        return;

    $handle = opendir($dirname);
    while ($file = readdir($handle)) {
        if($file == '.'||$file == '..') continue;

        if (is_dir($dirname.$file)) $result_array[] = $file;
    }
    closedir($handle);
    sort($result_array);

    return $result_array;
}


// 테마
function get_theme_dir()
{
    $result_array = array();

    $dirname = G5_PATH.'/'.G5_THEME_DIR.'/';
    $handle = opendir($dirname);
    while ($file = readdir($handle)) {
        if($file == '.'||$file == '..') continue;

        if (is_dir($dirname.$file)) {
            $theme_path = $dirname.$file;
            if(is_file($theme_path.'/index.php') && is_file($theme_path.'/head.php') && is_file($theme_path.'/tail.php'))
                $result_array[] = $file;
        }
    }
    closedir($handle);
    natsort($result_array);

    return $result_array;
}


// 테마정보
function get_theme_info($dir)
{
    $info = array();
    $path = G5_PATH.'/'.G5_THEME_DIR.'/'.$dir;

    if(is_dir($path)) {
        $screenshot = $path.'/screenshot.png';
        if(is_file($screenshot)) {
            $size = @getimagesize($screenshot);

            if($size[2] == 3)
                $screenshot_url = str_replace(G5_PATH, G5_URL, $screenshot);
        }

        $info['screenshot'] = $screenshot_url;

        $text = $path.'/readme.txt';
        if(is_file($text)) {
            $content = file($text, false);
            $content = array_map('trim', $content);

            preg_match('#^Theme Name:(.+)$#i', $content[0], $m0);
            preg_match('#^Theme URI:(.+)$#i', $content[1], $m1);
            preg_match('#^Maker:(.+)$#i', $content[2], $m2);
            preg_match('#^Maker URI:(.+)$#i', $content[3], $m3);
            preg_match('#^Version:(.+)$#i', $content[4], $m4);
            preg_match('#^Detail:(.+)$#i', $content[5], $m5);
            preg_match('#^License:(.+)$#i', $content[6], $m6);
            preg_match('#^License URI:(.+)$#i', $content[7], $m7);

            $info['theme_name'] = trim($m0[1]);
            $info['theme_uri'] = trim($m1[1]);
            $info['maker'] = trim($m2[1]);
            $info['maker_uri'] = trim($m3[1]);
            $info['version'] = trim($m4[1]);
            $info['detail'] = trim($m5[1]);
            $info['license'] = trim($m6[1]);
            $info['license_uri'] = trim($m7[1]);
        }

        if(!$info['theme_name'])
            $info['theme_name'] = $dir;
    }

    return $info;
}


// 테마설정 정보
function get_theme_config_value($dir, $key='*')
{
    $tconfig = array();

    $theme_config_file = G5_PATH.'/'.G5_THEME_DIR.'/'.$dir.'/theme.config.php';
    if(is_file($theme_config_file)) {
        include($theme_config_file);

        if($key == '*') {
            $tconfig = $theme_config;
        } else {
            $keys = array_map('trim', explode(',', $key));
            foreach($keys as $v) {
                $tconfig[$v] = trim($theme_config[$v]);
            }
        }
    }

    return $tconfig;
}


// 회원권한을 SELECT 형식으로 얻음
function get_member_level_select($name, $start_id=0, $end_id=10, $selected="", $event="")
{
    global $g5;

    $str = "\n<select id=\"{$name}\" name=\"{$name}\"";
    if ($event) $str .= " $event";
    $str .= ">\n";
    for ($i=$start_id; $i<=$end_id; $i++) {
        $str .= '<option value="'.$i.'"';
        if ($i == $selected)
            $str .= ' selected="selected"';
        $str .= ">{$i}</option>\n";
    }
    $str .= "</select>\n";
    return $str;
}


// 회원아이디를 SELECT 형식으로 얻음
function get_member_id_select($name, $level, $selected="", $event="")
{
    global $g5;

    $sql = " select mb_id from {$g5['member_table']} where mb_level >= '{$level}' ";
    $result = sql_query($sql);
    $str = '<select id="'.$name.'" name="'.$name.'" '.$event.'><option value="">선택안함</option>';
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $str .= '<option value="'.$row['mb_id'].'"';
        if ($row['mb_id'] == $selected) $str .= ' selected';
        $str .= '>'.$row['mb_id'].'</option>';
    }
    $str .= '</select>';
    return $str;
}

// 권한 검사
function auth_check($auth, $attr, $return=false)
{
    global $is_admin;

    if ($is_admin == 'super') return;

    if (!trim($auth)) {
        $msg = '이 메뉴에는 접근 권한이 없습니다.\\n\\n접근 권한은 최고관리자만 부여할 수 있습니다.';
        if($return)
            return $msg;
        else
            alert($msg);
    }

    $attr = strtolower($attr);

    if (!strstr($auth, $attr)) {
        if ($attr == 'r') {
            $msg = '읽을 권한이 없습니다.';
            if($return)
                return $msg;
            else
                alert($msg);
        } else if ($attr == 'w') {
            $msg = '입력, 추가, 생성, 수정 권한이 없습니다.';
            if($return)
                return $msg;
            else
                alert($msg);
        } else if ($attr == 'd') {
            $msg = '삭제 권한이 없습니다.';
            if($return)
                return $msg;
            else
                alert($msg);
        } else {
            $msg = '속성이 잘못 되었습니다.';
            if($return)
                return $msg;
            else
                alert($msg);
        }
    }
}


// 작업아이콘 출력
function icon($act, $link='', $target='_parent')
{
    global $g5;

    $img = array('입력'=>'insert', '추가'=>'insert', '생성'=>'insert', '수정'=>'modify', '삭제'=>'delete', '이동'=>'move', '그룹'=>'move', '보기'=>'view', '미리보기'=>'view', '복사'=>'copy');
    $icon = '<img src="'.G5_ADMIN_PATH.'/img/icon_'.$img[$act].'.gif" title="'.$act.'">';
    if ($link)
        $s = '<a href="'.$link.'">'.$icon.'</a>';
    else
        $s = $icon;
    return $s;
}


// rm -rf 옵션 : exec(), system() 함수를 사용할 수 없는 서버 또는 win32용 대체
// www.php.net 참고 : pal at degerstrom dot com
function rm_rf($file)
{
    if (file_exists($file)) {
        if (is_dir($file)) {
            $handle = opendir($file);
            while($filename = readdir($handle)) {
                if ($filename != '.' && $filename != '..')
                    rm_rf($file.'/'.$filename);
            }
            closedir($handle);

            @chmod($file, G5_DIR_PERMISSION);
            @rmdir($file);
        } else {
            @chmod($file, G5_FILE_PERMISSION);
            @unlink($file);
        }
    }
}

// 입력 폼 안내문
function help($help="")
{
    global $g5;

    $str  = '<span class="frm_info">'.str_replace("\n", "<br>", $help).'</span>';

    return $str;
}

// 출력순서
function order_select($fld, $sel='')
{
    $s = '<select name="'.$fld.'" id="'.$fld.'">';
    for ($i=1; $i<=100; $i++) {
        $s .= '<option value="'.$i.'" ';
        if ($sel) {
            if ($i == $sel) {
                $s .= 'selected';
            }
        } else {
            if ($i == 50) {
                $s .= 'selected';
            }
        }
        $s .= '>'.$i.'</option>';
    }
    $s .= '</select>';

    return $s;
}

// 불법접근을 막도록 토큰을 생성하면서 토큰값을 리턴
function get_admin_token()
{
    $token = md5(uniqid(rand(), true));
    set_session('ss_admin_token', $token);

    return $token;
}

// 관리자가 자동등록방지를 사용해야 할 경우
function get_admin_captcha_by($type='get'){

    $captcha_name = 'ss_admin_use_captcha';

    if($type === 'remove'){
        set_session($captcha_name, '');
    }

    return get_session($captcha_name);
}

//input value 에서 xss 공격 filter 역할을 함 ( 반드시 input value='' 타입에만 사용할것 )
function get_sanitize_input($s, $is_html=false){

    if(!$is_html){
        $s = strip_tags($s);
    }

    $s = htmlspecialchars($s, ENT_QUOTES, 'utf-8');

    return $s;
}

function check_log_folder($log_path, $is_delete=true){

    if( is_writable($log_path) ){

        // 아파치 서버인 경우 웹에서 해당 폴더 접근 막기
        $htaccess_file = $log_path.'/.htaccess';
        if ( !file_exists( $htaccess_file ) ) {
            if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
                fwrite( $handle, 'Order deny,allow' . "\n" );
                fwrite( $handle, 'Deny from all' . "\n" );
                fclose( $handle );
            }
        }

        // 아파치 서버인 경우 해당 디렉토리 파일 목록 안보이게 하기
        $index_file = $log_path . '/index.php';
        if ( !file_exists( $index_file ) ) {
            if ( $handle = @fopen( $index_file, 'w' ) ) {
                fwrite( $handle, '' );
                fclose( $handle );
            }
        }
    }

	if( $is_delete ) {
		try {
			// txt 파일과 log 파일을 조회하여 30일이 지난 파일은 삭제합니다.
			$txt_files = glob($log_path.'/*.txt');
			$log_files = glob($log_path.'/*.log');

			$del_files = array_merge($txt_files, $log_files);

			if( $del_files && is_array($del_files) ){
				foreach ($del_files as $del_file) {
					$filetime = filemtime($del_file);
					// 30일이 지난 파일을 삭제
					if($filetime && $filetime < (G5_SERVER_TIME - 2592000)) {
						@unlink($del_file);
					}
				}
			}
		} catch(Exception $e) {
		}
	}
}

// POST로 넘어온 토큰과 세션에 저장된 토큰 비교
function check_admin_token()
{
    $token = get_session('ss_admin_token');
    set_session('ss_admin_token', '');

    if(!$token || !$_REQUEST['token'] || $token != $_REQUEST['token'])
        alert('올바른 방법으로 이용해 주십시오.', G5_URL);

    return true;
}

// 관리자 페이지 referer 체크
function admin_referer_check($return=false)
{
    $referer = trim($_SERVER['HTTP_REFERER']);
    if(!$referer) {
        $msg = '정보가 올바르지 않습니다.';

        if($return)
            return $msg;
        else
            alert($msg, G5_URL);
    }

    $p = @parse_url($referer);

    $host = preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST']);
    $msg = '';

    if($host != $p['host']) {
        $msg = '올바른 방법으로 이용해 주십시오.';
    }

    if( $p['path'] && ! preg_match( '/\/'.preg_quote(G5_ADMIN_DIR).'\//i', $p['path'] ) ){
        $msg = '올바른 방법으로 이용해 주십시오';
    }

    if( $msg ){
        if($return) {
            return $msg;
        } else {
            alert($msg, G5_URL);
        }
    }
}

function admin_check_xss_params($params){

    if( ! $params ) return;

    foreach( $params as $key=>$value ){

        if ( empty($value) ) continue;

        if( is_array($value) ){
            admin_check_xss_params($value);
        } else if ( (preg_match('/<\s?[^\>]*\/?\s?>/i', $value) && (preg_match('/script.*?\/script/ius', $value) || preg_match('/[onload|onerror]=.*/ius', $value))) || preg_match('/^(?=.*token\()(?=.*xmlhttprequest\()(?=.*send\().*$/im', $value) || (preg_match('/[onload|onerror|focus]=.*/ius', $value) && preg_match('/(eval|expression|exec|prompt)(\s*)\((.*)\)/ius', $value)) ){
            alert('요청 쿼리에 잘못된 스크립트문장이 있습니다.\\nXSS 공격일수도 있습니다.', G5_URL);
            die();
        }
    }

    return;
}

function admin_menu_find_by($call, $search_key){
    global $menu;

    static $cache_menu = array();

    if( empty($cache_menu) ){
        foreach( $menu as $k1=>$arr1 ){

            if (empty($arr1) ) continue;
            foreach( $arr1 as $k2=>$arr2 ){
                if (empty($arr2) ) continue;

                $menu_key = isset($arr2[3]) ? $arr2[3] : '';
                if (empty($menu_key) ) continue;

                $cache_menu[$menu_key] = array(
                    'sub_menu'=>$arr2[0],
                    'title'=>$arr2[1],
                    'link'=>$arr2[2],
                    );
            }
        }
    }

    if( isset($cache_menu[$call]) && isset($cache_menu[$call][$search_key]) ){
        return$cache_menu[$call][$search_key];
    }

    return '';
}

// 접근 권한 검사
if (!$member['mb_id'])
{
    alert('로그인 하십시오.', G5_BBS_URL.'/login.php?url=' . urlencode(correct_goto_url(G5_ADMIN_URL)));
}
else if ($is_admin != 'super')
{
    $auth = array();
    $sql = " select au_menu, au_auth from {$g5['auth_table']} where mb_id = '{$member['mb_id']}' ";
    $result = sql_query($sql);
    for($i=0; $row=sql_fetch_array($result); $i++)
    {
        $auth[$row['au_menu']] = $row['au_auth'];
    }

    if (!$i)
    {
        alert('최고관리자 또는 관리권한이 있는 회원만 접근 가능합니다.', G5_URL);
    }
}

// 관리자의 아이피, 브라우저와 다르다면 세션을 끊고 관리자에게 메일을 보낸다.
$admin_key = md5($member['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']);
if (get_session('ss_mb_key') !== $admin_key) {

    session_destroy();

    include_once(G5_LIB_PATH.'/mailer.lib.php');
    // 메일 알림
    mailer($member['mb_nick'], $member['mb_email'], $member['mb_email'], 'XSS 공격 알림', $_SERVER['REMOTE_ADDR'].' 아이피로 XSS 공격이 있었습니다.<br><br>관리자 권한을 탈취하려는 접근이므로 주의하시기 바랍니다.<br><br>해당 아이피는 차단하시고 의심되는 게시물이 있는지 확인하시기 바랍니다.'.G5_URL, 0);

    alert_close('정상적으로 로그인하여 접근하시기 바랍니다.');
}

@ksort($auth);

// 가변 메뉴
unset($auth_menu);
unset($menu);
unset($amenu);
$tmp = dir(G5_ADMIN_PATH);
$menu_files = array();
while ($entry = $tmp->read()) {
    if (!preg_match('/^admin.menu([0-9]{3}).*\.php$/', $entry, $m))
        continue;  // 파일명이 menu 으로 시작하지 않으면 무시한다.

    $amenu[$m[1]] = $entry;
    $menu_files[] = G5_ADMIN_PATH.'/'.$entry;
}
@asort($menu_files);
foreach($menu_files as $file){
    include_once($file);
}
@ksort($amenu);

$amenu = run_replace('admin_amenu', $amenu);
if( isset($menu) && $menu ){
    $menu = run_replace('admin_menu', $menu);
}

$arr_query = array();
if (isset($sst))  $arr_query[] = 'sst='.$sst;
if (isset($sod))  $arr_query[] = 'sod='.$sod;
if (isset($sfl))  $arr_query[] = 'sfl='.$sfl;
if (isset($stx))  $arr_query[] = 'stx='.$stx;
if (isset($page)) $arr_query[] = 'page='.$page;
$qstr = implode("&amp;", $arr_query);

if ( isset($_REQUEST) && $_REQUEST ){
    if( admin_referer_check(true) ){
        admin_check_xss_params($_REQUEST);
    }
}

// 관리자에서는 추가 스크립트는 사용하지 않는다.
//$config['cf_add_script'] = '';


// 여기서부터가 추가 코드
function print_admin_menu(){
  $menu_name = array("매출현황","입금현황","회원관리","알림전송","통계","홈페이지관리");

  for($i=1; $i<=count($menu_name); $i++){
    if($i==1){
      $menu_name[$i][0]="입금현황";
    }else if($i==2){
      $menu_name[$i][0]="전체 회원 리스트";
    }else if($i==3){
      $menu_name[$i][0]="알림전송";
      $menu_name[$i][1]="공지업데이트";
    }else if($i==4){
      $menu_name[$i][0]="품목별 거래량 및 평균단가 ";
      $menu_name[$i][1]="가입자 현황";
    }else if($i==5){
      $menu_name[$i][0]="";
    }
    print_r($menu_name);
    // echo "<button type='button' class='btn_op_menu' onclick='admin_menu_oc({$i})'>".$menu_name[$i]."</button>";
    echo "<ul><li>".$menu_name[$i]."</li></ul>";
    echo "<ul class='menu{$i}'>";
    if(count($menu_name[$i])>1){
      echo "<li>".$menu_name[$i][0]."</li>";
      echo "<li>".$menu_name[$i][1]."</li>";
    }else{
      echo "<li>".$menu_name[$i]."</li>";
    }
    echo "</ul>";
  }
}

//  입금현황 리스트
function list_depo($page,$list,$l_cnt){
  $start_num = ($page-1) * $list;

  $d_sql = "SELECT * FROM f_deposit AS d JOIN f_member AS m ON d.m_idx=m.idx
  JOIN f_partner AS p ON d.p_idx=p.idx WHERE 1 ORDER BY d.idx DESC LIMIT $start_num,$list";
  $d_rs = sql_query($d_sql);

  while($row = sql_fetch_array($d_rs)){
    $m_idx = $row['m_idx'];
    $p_idx = $row['p_idx'];
    $o_idx = $row['o_idx'];
    // 결제금액
    $m_price = $row['m_price'];
    $p_price = $row['p_price'];

    //고객 정보
    $m_name = $row['m_name'];
    $p_name = $row['c_name'];
    $m_tel = $row['m_tel'];
    $p_tel = $row['c_tel'];



    // 고객, 파트너의 주문상태
    if($row['m_deposit']==1){
      $m_depo_txt = "결제 완료";
    }else{
      $m_depo_txt = "결제 대기";
    }

    if($row['p_deposit']==1){
      $p_depo_txt = "입금 완료";
    }else{
      $p_depo_txt = "입금 대기";
    }

    // 주문일자 추출
    $o_sql = "SELECT o_date FROM f_order WHERE idx={$o_idx}";
    $o_rs = sql_fetch($o_sql);
    $o_date = $o_rs['o_date'];

    // 날짜 표시용으로 가공
    $box = explode(" ",$o_date);
    $date1 = $box[0];
    $box2 = explode(":",$box[1]);
    $date2 = $box2[0].":".$box2[1];

    // 견적데이터에서 공사명 추출
    $e_sql = "SELECT * FROM f_estimate WHERE idx={$o_idx}";
    $e_rs = sql_fetch($e_sql);
    $g_name = $e_rs['name'];

    // 요청일자부터 몇시간이 지났는지 계산
    $now = date("Y-m-d H:i:s");
    $re = strtotime($now) - strtotime($o_date);
    $c_h = ceil($re / (60*60));

    // echo "c_h : ".$c_h."<br>";
    // echo "o_date : ".$o_date."<br>";
    // echo "now : ".$now."<br><br>";


    $chk_d = 0;
    if($c_h > 24){
      while($c_h > 24){
        $chk_d++;
        $c_h -= 24;
        $chk_h = $c_h;
      }
    }else{
      $chk_h = $c_h;
    }
    $c_date_txt = "+".$chk_d."일".$chk_h."시간";

    echo "<tr>";
    echo "<td class='depo_cont_l'>".$m_depo_txt."</td>";
    echo "<td class='depo_cont_l'>".$p_depo_txt."</td>";
    echo "<td class='depo_cont_l'>".$g_name."</td>";
    echo "<td class='depo_cont_l'>".$p_name."</td>";
    echo "<td class='depo_cont_l'>".$m_name."</td>";
    echo "<td class='depo_cont_l'>".$p_tel."</td>";
    echo "<td class='depo_cont_l'>".$m_tel."</td>";
    echo "<td class='depo_cont_l'>".$date1." | ".$date2."</td>";
    echo "<td class='depo_cont_l'>".$c_date_txt."</td>";
    echo "<td class='depo_price_l'><div class='attend'><div class='mp'>".number_format($m_price)."</div><div class='g".$row['m_deposit']."'>".$m_depo_txt."</div></div></td>";
    echo "<td class='depo_price_l'><div class='attend'><div class='pp'>".number_format($p_price)."</div><div class='i".$row['p_deposit']."'>".$p_depo_txt."</div></div</td>";
    echo "<tr><td class='b_line' colspan='13'><div id='bottom_line'></div></td></tr>";
    echo "</tr>";
  }
}

function list_mem($s_key,$type){
  // $start_num = ($page-1) * $list;

  if($type=="com"){
    $table_name = "f_partner";
    $search_col = "c_";
    $v_type = 1;
  }else if($type=="mem"){
    $table_name = "f_member";
    $search_col = "m_";
    $v_type = 2;
  }
  if($s_key){
    $where = "{$search_col}name like '%{$s_key}%' ";
  }else{
    $where = 1;
  }
  $p_sql = "SELECT * FROM {$table_name} WHERE {$where} AND live='Y' ORDER BY idx DESC ";
  $p_rs = sql_query($p_sql);

  $col_name_txt = $search_col."name";


  // 상세페이지로 넘길 데이터 설정
  echo "<input type='hidden' name='page_type' value='{$v_type}'/>";


  if(!$p_rs){
    echo "<tr>";
    echo "<td class='no_search' colspan='6'>찾으시는 결과가 없습니다</td>";
    echo "</tr>";
  }else{

    while($row=sql_fetch_array($p_rs)){
      $addr1 = explode(" ",$row['addr1']);
      $date = explode(" ",$row['join_date']);
      $col_name = $row[$col_name_txt];
      // 베스트농원 글자색 변화
      if($type=="com"){
        $ps = $row['partner_ship'];
        if($ps==3){
          $col_name = "<span class='yt'>".$col_name."</span>";
        }
      }


      echo "<input type='hidden' name='idx' />";
      echo "<tr>";
      echo "<td class='mem_p_cont'>".$row['idx']."</td>";
      echo "<td class='mem_p_cont'>".$col_name."</td>";
      echo "<td class='mem_p_cont'>".$row[$search_col.'tel']."</td>";
      echo "<td class='mem_p_cont'>".$addr1[0]."</td>";
      echo "<td class='mem_p_cont'>".$date[0]."</td>";
      echo "<td class='mem_p_cont'><button type='button' class='detail_btn' onclick='view_detail(".$row['idx'].",{$v_type})'>상세보기</button></td>";
      echo "<tr><td class='b_line' colspan='6'><div id='bottom_line'></div></td></tr>";
      echo "</tr>";
    }

  }
}

function partner_detail($idx,$type){

  if($type==1){
    $idx_name = "p_idx";
    $t_name = "f_partner";
  }else{
    $idx_name = "m_idx";
    $t_name = "f_member";
  }

  $sql = "SELECT * FROM {$t_name} WHERE idx={$idx}";
  $rs = sql_fetch_array(sql_query($sql));

  if($type==1){
    $ps_jud = $rs['partner_ship'];
    if($ps_jud==1){
      $partner_ship_txt = "일반 농원";
    }else if($ps_jud==2){
      $partner_ship_txt = "베스트 농원";
    }else if($ps_jud==3){
      $partner_ship_txt = "공식파트너 농원";
    }
  }
  echo "<tr>";
  echo "<td class='column'>이름</td>";
  echo "<td class='cont'>".$rs['m_name']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";
  echo "<td class='column'>직급</td>";
  echo "<td class='cont'>".$rs['position']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";
  echo "<td class='column'>업체명</td>";
  echo "<td class='cont'>".$rs['c_name']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";
  echo "<td class='column'>주소</td>";
  echo "<td class='cont'>".$rs['addr1'].$rs['addr2']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";
  echo "<td class='column'>휴대전화번호</td>";
  echo "<td class='cont'>".$rs['m_tel']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";
  echo "<td class='column'>사업장전화번호</td>";
  echo "<td class='cont'>".$rs['c_tel']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";
  echo "<td class='column'>계좌은행</td>";
  echo "<td class='cont'>".$rs['bank_name']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";
  echo "<td class='column'>계좌번호</td>";
  echo "<td class='cont'>".$rs['bank_num']."</td>";
  echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
  echo "</tr>";

  if($type==1){
    echo "<td class='column'>농원등급</td>";
    echo "<td class='cont'>{$partner_ship_txt}</td>";
    echo "<tr><td class='b_line' colspan='2'><div class='bottom_lines'></div></td></tr>";
    echo "</tr>";
  }
}

function view_depo($idx,$type,$c_name){
  if($type==1){
    $idx_name = "p_idx";
    $col_name = "p_";
  }else{
    $idx_name = "m_idx";
    $col_name = "m_";
  }


  $sql = "SELECT d.{$col_name}price, d.{$col_name}deposit,e.name,e.d_date FROM f_deposit as d JOIN f_estimate as e ON d.e_idx=e.idx WHERE d.{$idx_name} = {$idx}";
  $rs = sql_query($sql);
  $cnt = sql_num_rows($rs);
  // echo $sql."<br>";

  if($cnt==0){
    echo "<tr>";
    echo "<td colspan='6' class='no_data'>거래 내역이 없습니다.</td>";
    echo "</tr>";
  }else{
    while($row = sql_fetch_array($rs)){
      $box = explode(" ",$row['d_date']);
      $d_date = $box[0];
      $jud = $row[$col_name.'deposit'];

      if($jud==1){
        $class_name = "ok";
        if($type==1){
          $depo_txt = "입금완료";
        }else{
          $depo_txt = "결제완료";
        }
      }else{
        $class_name = "no";
        if($type==1){
          $depo_txt = "입금대기";
        }else{
          $depo_txt = "결제대기";
        }
      }
      echo "<tr>";
      echo "<td>{$cnt}</td>";
      echo "<td>".$c_name."</td>";
      echo "<td>".$row['name']."</td>";
      echo "<td>".number_format($row[$col_name.'price'])."</td>";
      echo "<td>".$d_date."</td>";
      echo "<td class='chk_depo'><button type='button' class='detail_btn{$class_name}'>{$depo_txt}</button></td>";
      echo "<tr><td class='b_line' colspan='6'><div id='bottom_line'></div></td></tr>";
      echo "</tr>";
      $cnt--;
    }
  }
}

function star_score($idx){
  $st_sql = "SELECT sum(point) as t FROM f_partner_ship WHERE p_idx = {$idx}";
  $box = sql_fetch_array(sql_query($st_sql));

  $t_sql = "SELECT * FROM f_partner_ship WHERE p_idx = {$idx}";
  $cnt = sql_num_rows(sql_query($t_sql));

  $score = $box['t'] / $cnt;
  if(!$cnt) $score=0;

  return $score;
}

function get_Partnername($idx,$type){

  if($type==1){
      $t_name = "f_partner";
      $col_name = "c_";
  }else{
      $t_name = "f_member";
      $col_name = "m_";
  }

  $n_sql = "SELECT * FROM {$t_name} WHERE idx = {$idx}";
  $box = sql_fetch_array(sql_query($n_sql));

  return $box[$col_name.'name'];
}



function newbi($opt){
  $ym = date("Y-m") ;
  if($opt==1){
    $table = "f_partner";
  }else if($opt==2){
    $table = "f_member";
  }
  $where = "WHERE join_date like '{$ym}%'";
  $n_sql = "SELECT * FROM {$table} {$where}";
  $newbi_cnt = sql_num_rows(sql_query($n_sql));

  return $newbi_cnt;

}



function paging($page,$list,$l_cnt){
  $block_ct = 10;

  $block_num = ceil($page/$block_ct);
  $block_start = (($block_num -1) * $block_ct) + 1;
  $block_end = $block_start + $block_ct -1;

  $total_page = ceil($l_cnt / $list);
  if($block_end > $total_page){
    $block_end = $total_page;
  }
  $total_block = ceil($total_page / $block_ct);
  $start_num = ($page-1) * $list;

  $p_sql = "SELECT * FROM f_deposit
  ORDER BY idx DESC
  LIMIT {$start_num}, {$list}";
  $p_rs = sql_query($p_sql);


  if($page <= 1){
    echo "<li class='fo_re'>처음</li>";
  }else{
    echo "<li><a href='?page=1'>처음</a></li>";
  }
  if($page <= 1){

  }else{
    $pre = $page-1;
    echo "<li><a href='?page=$pre'>이전</a></li>";
  }

  for($i=$block_start; $i<=$block_end; $i++){
    if($page == $i){
      echo "<li class='fo_re'>[$i]</li>"; //현재 페이지에 해당하는 번호에 굵은 빨간색을 적용한다
    }else{
      echo "<li><a href='?page=$i'>[$i]</a></li>"; //아니라면 $i
    }
  }
  if($block_num >= $total_block){ //만약 현재 블록이 블록 총개수보다 크거나 같다면 빈 값
  }else{
    $next = $page + 1; //next변수에 page + 1을 해준다.
    echo "<li><a href='?page=$next'>다음</a></li>"; //다음글자에 next변수를 링크한다. 현재 4페이지에 있다면 +1하여 5페이지로 이동하게 된다.
  }
  if($page >= $total_page){ //만약 page가 페이지수보다 크거나 같다면
    echo "<li class='fo_re'>마지막</li>"; //마지막 글자에 긁은 빨간색을 적용한다.
  }else{
    echo "<li><a href='?page=$total_page'>마지막</a></li>"; //아니라면 마지막글자에 total_page를 링크한다.
  }

}

function view_sn_history(){
  $sql = "SELECT * FROM f_sms_push ORDER BY idx DESC LIMIT 0,3";
  $re = sql_query($sql);

  while($row = sql_fetch_array($re)){
      if($row['target']=="P"){
        $target = "농원";
      }else if($row['target']=="M"){
        $target = "고객";
      }

      $cont_txt = $row['content'];

      if($row['size'] > 0){
        $cont_txt .= "<br><br>";
        $cont_txt .= "<a href='./img/forest_adm/".$row['img']."'><img class='his_img' src='./img/forest_adm/".$row['img']."' /></a>";
      }
      $d_box = explode(" ",$row['send_date']);

      echo "<div class='view_history'>";
      echo "<table>";
      echo "<tr><td colspan='2' class='his_date'>".$d_box[0]."</td></tr>";
      echo "<tr><td class='head_td'>회원 구분</td><td class='cont_td'>{$target}</td></tr>";
      echo "<tr><td class='head_td'>받는 사람</td><td class='cont_td'>".$row['t_idx']."</td></tr>";
      echo "<tr><td class='head_td'>알림 내용</td><td class='cont_td'>{$cont_txt}</td></tr>";
      echo "</table>";
      echo "</div>";
  }

}

function view_no_history(){
  $sql = "SELECT * FROM f_notice ORDER BY idx DESC LIMIT 0,3";
  $re = sql_query($sql);

  while($row = sql_fetch_array($re)){
      if($row['target']=="P"){
        $target = "농원";
      }else if($row['target']=="M"){
        $target = "고객";
      }

      $cont_txt = $row['content'];

      if($row['size'] > 0){
        $cont_txt .= "<br><br>";
        $cont_txt .= "<a href='./img/forest_adm/".$row['img']."'><img class='his_img' src='./img/forest_adm/".$row['img']."' /></a>";
      }

      $d_box = explode(" ",$row['w_date']);

      echo "<div class='view_history'>";
      echo "<table>";
      echo "<tr><td colspan='2' class='his_date'>".$d_box[0]."</td></tr>";
      echo "<tr><td class='head_td'>회원 구분</td><td class='cont_td'>{$target}</td></tr>";
      echo "<tr><td class='head_td'>받는 사람</td><td class='cont_td'>".$row['t_idx']."</td></tr>";
      echo "<tr><td class='head_td'>알림 내용</td><td class='cont_td'>{$cont_txt}</td></tr>";
      echo "</table>";
      echo "</div>";
  }

}



//
//
//
//
//
// }




?>
