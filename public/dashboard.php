<?php
    $typeReqest = getRequestValue("typeReq");
    try
    {
        require  APP_ROOT_DIR."business/TDashBoard.php";
        $pageManager = new TPageManager();
        $pageManager->init();
        
        if($typeReqest != 'ajax'){

            $team = getRequestValue("team");
            $act = getRequestValue("act");

            $db = new TDashBoard();
            $db->setSeparation($team);
            echo $db->getHtmlData();
        } else {
            header("Content-Type: text/json; charset=UTF-8");
            $team = getRequestValue("team");
            $act = getRequestValue("act");

            $db = new TDashBoard();
            $db->setSeparation($team);
            
            echo json_encode($db->getUpdateData());
        }
        $pageManager->close();
    }
    catch( Exception $e )
    {
        logAndShowApplicationException( $e );
    }
?>
