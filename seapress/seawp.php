<?php
error_reporting(0);

$paths = plugin_dir_url(__FILE__);

## Session
date_default_timezone_set('Europe/London');
session_start();
$basename_file=basename(__FILE__);

include("pressfunctions.php");
include("presstemplate.php");

## Login
if (isset($_POST['login'])){
    $username= htmlentities($_POST['username']);
    $password= htmlentities($_POST['password']);
    $hostname= htmlentities($_POST['hostname']);
    $token=1;
    $token=seafileLogin($username,$password,$hostname);
    if (!isset($token['token'])){
        $error_msg= '
        <div class="alert alert-danger form-alert" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Oops! Irgendwas ist schief gelaufen!</strong>
        </div>';

        $login_template = str_replace("##ERROR_ALERT##", $error_msg, $login_template);

        echo $login_template;
        die($footer_template);
    }
    $_SESSION['username']= $username;
    $_SESSION['hostname']= $hostname;
    $_SESSION['password']= $password;
    $_SESSION['token']=$token['token'];

}
##

## Library content

if(isset($_GET['dir']) AND isset($_SESSION['token'])){

    $table_results=<<<TABLE_RESULTS

<table class="footable" data-filter="#filter">
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
    $repo_name = $_GET['repo_name'];
    $dirc = cut_last_occurence($_GET['dir'],"/");
    if ($_GET['dir']!=""){
        $table_results.=<<<TABLE_RESULTS

    <tr class="footable-disabled">
        <td colspan="3" class="footable-disabled">
            <a href="?dir=$dirc&repo={$_GET['repo']}&repo_name=$repo_name"><img src="$paths/img/folder.png" alt="Back" height="24" width="24">..</a>
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


    foreach ($repo_list as $array_value) {

        if($array_value['type']=="dir"){
            $time_elapsed=time_elapsed_string($array_value['mtime'],'1');
            $table_results.=<<<TABLE_RESULTS

    <tr>
        <td><img src="$paths/img/folder.png" alt="Dir" height="24" width="24"><a href="?dir={$_GET['dir']}/{$array_value['name']}&repo={$_GET['repo']}&repo_name=$repo_name">{$array_value['name']}</td>
        <td>&nbsp;</td>
        <td data-value="{$array_value['mtime']}">$time_elapsed</td>
    </tr>
TABLE_RESULTS;

            }else{
            $time_elapsed=time_elapsed_string($array_value['mtime'],'1');
            $format_bytes=formatBytes($array_value['size']);

            $table_results.=<<<TABLE_RESULTS
    <tr>
        <td><img src="$paths/img/file.png" alt="File" height="24" width="24">{$array_value['name']} <a href="?upload={$_GET['repo']}/file/?p=/{$_GET['dir']}/&file={$array_value['name']}&repo_name=repo_name" title="Upload {$array_value['name']}"><button type="button" class="btn btn-default  btn-xs"><span class="glyphicon glyphicon-upload"></span></span></button></a></div></td>

        <td data-value="{$array_value['size']}">$format_bytes</a> </td>
        <td data-value="{$array_value['mtime']}">$time_elapsed</td>
    </tr>
TABLE_RESULTS;

        }
    }
    $table_results.= '  </tbody>';

    $table_results.='</table>';

    $dirbreadcrumb = explode( '/', (ltrim ($_GET['dir'],'/') ) );

    $other_content= '
    <ol class="breadcrumb">
        <li><a href="?">Home</a></li>';

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
            $other_content.= '<li><a href="seawp.php?repo=' . $_GET['repo'] . '&repo_name='.$_GET['repo_name'].'&dir=/'.$value.'">'.$value.'</a> </li>';
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
##



## Download file


if (isset($_GET['download']) AND isset($_SESSION['token'])){
    $library="/api2/repos/" .  $_GET['download'] . rawurlencode($_GET['file']);
    $repo_list = seafileApi('GET',$library,'',$_SESSION['token'],$_SESSION['hostname']);
    header("Location:$repo_list");
    die();
}
##
##

## Library list (must be next to last)

if (isset($_SESSION['token'])){
    $table_results=<<<TABLE_RESULTS

<table class="footable" data-filter="#filter">
    <thead>
        <tr>
            <th data-sort-initial="ascending">Bibliotheken</th>
            <th data-hide="phone,tablet">Rechte</th>
            <th data-sort-ignore="true" data-hide="all">Letzte Änderung</th>
            <th data-type="numeric">Inhaber</th>
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
        <td>${array_value['permission']}</td>
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
##

## Not logged. Show login.

if (!isset($_SESSION['token'])){
    $error_msg='';
        if (isset($_GET['logged_out'])){
            $error_msg=$error_alert_logout_msg;
        }

    $login_template = str_replace("##ERROR_ALERT##", $error_msg, $login_template);
    echo $login_template;
    die($footer_template);
}
##
?>
