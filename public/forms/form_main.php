<?
    verifyUserAccess("");

    $frameworkDir = FRM_ROOT_DIR;
    if( getCurrentUserType()=="admin" )
        require $frameworkDir."forms/form_users.php";
    else
        require 'form_checklist.php';
     
?>
