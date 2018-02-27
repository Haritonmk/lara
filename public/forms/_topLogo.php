<?
    if( isUserLoggedIn() && getCurrentUserType()!="admin" )
        return;
     
    $logoFile = getInstanceFile("img/topLogo.png");
    echo "<center><img src=\"$logoFile\" width=\"100%\" height=\"150\" alt=\"\"/></center>";
?>
