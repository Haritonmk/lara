<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml">
<head>

<? 
    $sessionId = getExtContentReloadToken();
?>

<title><? echo APP_NAME;  ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


<link type="text/css" href="../../framework/lib/jquery/css/smoothness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />

<script type="text/javascript" src="../../framework/lib/jquery/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../framework/lib/jquery/jquery-ui-1.8.1.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script>

<script type="text/javascript" src="../../framework/lib/jquery/jquery.contextmenu.r2.packed.js"></script>

<script type="text/javascript" src="../../framework/lib/jqtreeview/jquery.treeview.js"></script>
<link type="text/css" href="../../framework/lib/jqtreeview/jquery.treeview.css" rel="stylesheet" />

<script type="text/javascript" src="../../framework/lib/multiselect/jquery.multiSelect.js"></script>
<link type="text/css" href="../../framework/lib/multiselect/jquery.multiSelect.css" rel="stylesheet" />


<SCRIPT type="text/javascript" src="../../framework/lib/general/common.js?<?echo $sessionId;?>"></SCRIPT>
<SCRIPT type="text/javascript">
    var phoneWindow = null;
</SCRIPT>
<link type="text/css" href="../../framework/css/general.css?<?echo $sessionId;?>" rel="stylesheet" />
<?
    if( defined("BASE_APP_NAME") )
    {
        $baseAppName = BASE_APP_NAME;
        echo "<link type=\"text/css\" href=\"../../apps/".$baseAppName."/css/app.css?$sessionId\" rel=\"stylesheet\" />";
    }
?>
<link type="text/css" href="css/appInstance.css?<?echo $sessionId;?>" rel="stylesheet" />
<?
    if( isUserLoggedIn() && getCurrentUserType()!="admin" )
    {
        $baseAppName = BASE_APP_NAME;
        echo "<link type=\"text/css\" href=\"../../apps/".$baseAppName."/css/login.css?$sessionId\" rel=\"stylesheet\" />";
    }

?>


</head>
<body  >

<div id="calosc" align="center">
<div class="szerokosc">
