<?php


$path = $_SERVER['DOCUMENT_ROOT'];
error_reporting(0);
include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/pluggable.php';
include_once $path . '/wp-admin/includes/image.php';
include_once $path . '/wp-admin/includes/file.php';
require_once $path . '/wp-load.php';
require_once $path . '/wp-admin/includes/taxonomy.php';

// Initialize Session
$basename_file=basename(__FILE__);
$paths = plugin_dir_url(__FILE__);

session_start();

// Include
include("pressfunctions.php");
include("presstemplate.php");

// jQuery
wp_enqueue_script('jquery');
// This will enqueue the Media Uploader script
wp_enqueue_media();

// die if user isnt logged in into Wordpress
if(!is_user_logged_in()){
  echo "Please log in first.";
  die;
}

// Initialize Login

if (isset($_POST['login'])){

    $username=$_POST['username'];
    $password=$_POST['password'];
    $hostname=$_POST['hostname'];

    $token=1;
    $token=seafileLogin($username,$password,$hostname);
    if (!isset($token['token'])){
        $header_html = str_replace("##TITLE##", $header_template);
        echo $header_html;
        $error_msg= '<div class="alert alert-danger form-alert" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Oops, da ist etwas schiefgelaufen!</strong> '  .$token['non_field_errors']['0'].'</div>';

        $login_template = str_replace("##ERROR_ALERT##", $error_msg, $login_template);

        echo $login_template;
        die($footer_template);
    }
    $_SESSION['username']=$_POST['username'];
    $_SESSION['hostname']=$_POST['hostname'];
    $_SESSION['token']=$token['token'];

    header("Location:$basename_file");
}

if (isset($_GET['logout'])){
session_destroy();
echo "<p> Erfolgreich ausgeloggt.</p>";
die;
}

// Upload in Media Library, POST

if (isset($_GET['upload'])){
      if ( !function_exists('media_handle_upload') ) {
          require_once(ABSPATH . "wp-admin" . '/includes/image.php');
          require_once(ABSPATH . "wp-admin" . '/includes/file.php');
          require_once(ABSPATH . "wp-admin" . '/includes/media.php');
        }
    $upload_dir = wp_upload_dir();

    $url = $_SESSION['hostname'] . '/api2/repos/' . $_GET['repo'] . '/file/?p=/' . $_GET['file'] . '&reuse=1';

    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Token ' . $_SESSION['token'],
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $raw=curl_exec($ch);
    curl_close ($ch);

    $raw2 = str_replace('"', "", $raw);
    //Nur zum debug in js console!
    echo $raw2 . " ";

    $filename = $_GET['file'];

    $tmp = download_url( $raw2 );
    if( is_wp_error( $tmp ) ){
        // download failed, handle error
    }
    $post_id = 0;
    $desc = $_GET['file'];
    $file_array = array();

    // Set variables for storage
    // fix file filename for query strings
    preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png|xls|xlsx|doc|docx|zip|ppt|pptx)/i', $url, $matches);
    $file_array['name'] = basename($matches[0]);
    $file_array['tmp_name'] = $tmp;

    // If error storing temporarily, unlink
    if ( is_wp_error( $tmp ) ) {
      @unlink($file_array['tmp_name']);
      $file_array['tmp_name'] = '';
    }

    // do the validation and storage stuff
    $id = media_handle_sideload( $file_array, $post_id, $desc );

    // If error storing permanently, unlink
    if ( is_wp_error($id) ) {
      @unlink($file_array['tmp_name']);
      return $id;
    }
    die;
}

//Infos für max File upload
$paths = plugin_dir_url(__FILE__);
$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

// Seapress Library Content

if(isset($_GET['dir']) AND isset($_SESSION['token'])){

    $table_results=<<<TABLE_RESULTS
    <p style="position:fixed;top:0px;right:0px;padding:2px;background-color: #fff;">max allowed Upload: $upload_mb MB | <a href="$paths/seawp.php?logout">Logout</a></p>

<table class="footable" data-filter="#filter"">
    <thead>
        <tr>
            <th data-type="alpha">Name</th>
            <th data-type="numeric" data-hide="phone">Größe</th>
            <th data-type="numeric" data-hide="phone,tablet">Letzte Änderung</th>
        </tr>
    </thead>
<tbody>
TABLE_RESULTS;

    $library="/api2/repos/" . $_GET['repo'] . "/dir/?p=/" . $_GET['dir'] . "/";
    $repo_list = seafileApi('GET',$library,'',$_SESSION['token'],$_SESSION['hostname']);
    echo"<pre>";
    print_r($repo_list);
    echo"</pre>";
    $repo_name = rawurlencode($_GET['repo_name']);
    $dirc = cut_last_occurence($_GET['dir'],"/");
    $direncode = rawurlencode($dirc);
    if ($_GET['dir']!=""){
        $table_results.=<<<TABLE_RESULTS

    <tr class="footable-disabled">
        <td colspan="3" class="footable-disabled">
            <a href="?dir={$direncode}&repo={$_GET['repo']}&repo_name=$repo_name"><img src="$paths/img/folder.png" alt="Back" height="24" width="24">..</a>
        </td>
    </tr>
TABLE_RESULTS;

        }else{
        $table_results.=<<<TABLE_RESULTS

    <tr class="footable-disabled">
        <td colspan="3" class="footable-disabled">
            <a href="?"><img src="$paths/img/folder.png" alt="Back" height="24" width="24">..</a>
        </td>
    </tr>
TABLE_RESULTS;

    }
    //if(isset($repo_list) && !is_array($repo_list)){
    foreach ($repo_list as $array_value) {

        if($array_value['type']=="dir"){
            $time_elapsed=time_elapsed_string($array_value['mtime'],'1');
            $direncode = rawurlencode($array_value['name']);

            echo $direncode;

            $table_results.=<<<TABLE_RESULTS

    <tr>
        <td><img src="$paths/img/folder.png" alt="Dir" height="24" width="24"><a href="?dir={$direncode}/&repo={$_GET['repo']}&repo_name=$repo_name">{$array_value['name']}</td>
        <td>Test &nbsp;</td>
        <td data-value="{$array_value['mtime']}">$time_elapsed</td>

    </tr>
TABLE_RESULTS;

            }else{
              $ext = pathinfo($array_value['name'], PATHINFO_EXTENSION);
              $avail_exts = array('ai','avi','css','csv','dbf','doc','dwg','exe','fla','html','iso','js','jpg','jpeg','json','mp3','mp4','pdf','png','ppt','psd','rtf','svg','txt','xls','xml','zip');
                if(in_array($ext, $avail_exts)){
                  $file_img = $ext;
                }else{ $file_img = 'file';}
            $time_elapsed=time_elapsed_string($array_value['mtime'],'1');
            $format_bytes=formatBytes($array_value['size']);
            $direncode = urlencode($_GET['dir']);
            $table_results.=<<<TABLE_RESULTS
    <tr>
        <td><img src="img/file-icons/JPEG/{$file_img}.jpg" alt="File" height="24" width="24">{$array_value['name']} <div class="pull-right"><a href="#" class="seapress-upload" data-path="{$direncode}/" data-repo="{$_GET['repo']}" data-file="{$array_value['name']}" title="Upload {$array_value['name']}">Upload</a></div></td>

        <td data-value="{$array_value['size']}">$format_bytes</a></td>
        <td data-value="{$array_value['mtime']}">$time_elapsed</td>
    </tr>
TABLE_RESULTS;

        }
    }
 // }else{echo $repo_list;}

    $table_results.= '  </tbody>';

    $table_results.='</table>';

    $dirbreadcrumb = explode( '/', (ltrim ($_GET['dir'],'/') ) );

    $other_content= '
    <ol class="breadcrumb">
        <li><a href="?">Startseite</a></li>';

    if($_GET['dir']==''){
        $other_content.='<li class="active">' . $_GET['repo_name'] . '</li>';
        }else{
        $other_content.='<li><a href="seawp.php?repo=' . $_GET['repo'] . '&repo_name='.$_GET['repo_name'].'&dir=">' . $_GET['repo_name'] . '</a></li>';
        $numItems = count($dirbreadcrumb);
        $i = 0;
        foreach($dirbreadcrumb as $value){
                if(++$i === $numItems) {
                    $other_content.='<li class="active">'.$value.'</li>';
                    break;
                }
            $other_content.= '<li><a href="$paths/seawp.php?repo=' . $_GET['repo'] . '&repo_name='.$_GET['repo_name'].'&dir=/'.$value.'">'.$value.'</a> </li>';
        }
    }
    $other_content.="</ol>";

    $main_template = str_replace("##TABLE_RESULTS##", $table_results, $main_template);
    $main_template = str_replace("##OTHER_CONTENT##", $other_content, $main_template);

    $main_template = str_replace("##ACTIVE_ITEM##", '', $main_template);
    $main_template = str_replace("##ACTIVE_ITEM2##", '', $main_template);

    echo $header_template;
    echo $main_template;

    die($footer_template);
}

// End Library Content



// Library List

if (isset($_SESSION['token'])){

    $table_results=<<<TABLE_RESULTS
<p style="position:fixed;top:0px;right:0px;padding:2px;background-color: #fff;">max allowed Upload: $upload_mb MB | <a href="$paths/seawp.php?logout">Logout</a></p>
<table class="footable" data-filter="#filter">
    <thead>
        <tr>
            <th data-sort-initial="ascending">Bibliotheken</th>
            <th data-type="numeric">Letzte Änderung</th>
            <th data-sort-ignore="true" data-hide="all">Inhaber</th>
            <th data-sort-ignore="true" data-hide="all">Verschlüsselt</th>
        </tr>
    </thead>
<tbody>
TABLE_RESULTS;

    $_SESSION['repo_list']=array();
    $repo_list = seafileApi('GET','/api2/repos/','',$_SESSION['token'],$_SESSION['hostname']);

    foreach ($repo_list as $array_value) {
        $encrypted=($array_value['encrypted']!="") ? "Ja" : "Nein";
        $time_elapsed=time_elapsed_string($array_value['mtime'],'1');

        $table_results.=<<<TABLE_RESULTS

    <tr>
        <td><a href="$paths/seawp.php?repo=${array_value['id']}&repo_name=${array_value['name']}&dir=">${array_value['name']}</a></td>
        <td data-value="${array_value['mtime']}">$time_elapsed</td>
        <td>${array_value['owner']}</td>
        <td>
        $encrypted
        </td>
    </tr>

TABLE_RESULTS;
        $_SESSION['repo_list'][$array_value['id']]=$array_value['name'];

    }


    $table_results.=  '</tbody>';

    $table_results.= '</table>';
    $other_content= '
    <ol class="breadcrumb">
        <li class="active">Startseite</li>
        </ol>';
    $main_template = str_replace("##OTHER_CONTENT##", $other_content, $main_template);
    $main_template = str_replace("##TABLE_RESULTS##", $table_results, $main_template);
    $main_template = str_replace("##ACTIVE_ITEM##", ' class="active"', $main_template);
    $main_template = str_replace("##ACTIVE_ITEM2##", '', $main_template);

    echo $header_template;
    echo $main_template;

    die($footer_template);
}

// End Library List





// Show Login if not already logged in

if (!isset($_SESSION['token'])){
    echo $header_template;
    $error_msg='';
        if (isset($_GET['timeout'])){
            $error_msg=$error_alert_timeout_msg;
        }
        if (isset($_GET['logged_out'])){
            $error_msg=$error_alert_logout_msg;
        }

    $login_template = str_replace("##ERROR_ALERT##", $error_msg, $login_template);
    echo $login_template;
    die($footer_template);
}

// End

?>
