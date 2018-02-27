<?php
 
    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $currentUser = getCurrentUserId();
    $applicatioId = getCurrentApplicationId();

    $list = getRequestValue("listInstance");
    $list = prepareSQLParam($list, "int");

    $sql = "select * from dbo.processInstanceTask where instanceId = $list";
    $dbM->execute($sql);
    $showMaker = $dbM->getData(0,"showMaker") == "1" ? TRUE : FALSE;
    $showChecker = $dbM->getData(0,"showChecker") == "1" ? TRUE : FALSE;
    $showSignOff = $dbM->getData(0,"showSignoff") == "1" ? TRUE : FALSE;
    $makerName = $dbM->getData(0,"makerName");
    $checkerName = $dbM->getData(0,"checkerName");
    $signoffName = $dbM->getData(0,"signoffName");
    $showActionDates = $dbM->getData(0,"showActionDates") == "1" ? TRUE : FALSE;
    
    $sql = "select * from [processInstance] where instanceId = $list";
    $dbM->execute($sql);
    $listName = $dbM->getData(0,"instanceName");
    $businessDate = $dbM->getData(0,"businessDate");
    
    
    $sql = "
            select
                    t.*,
                    m.userDesc 'MakerName',
                    c.userDesc 'CheckerName',
                    s.userDesc 'SignOffName'
            from
                    dbo.processInstanceTask t
                        left outer join frmSystemUser m on m.userId = t.makerUserId
                        left outer join frmSystemUser c on c.userId = t.checkerUserId
                        left outer join frmSystemUser s on s.userId = t.signoffUserId
            where
                    t.instanceId = $list
            order by
                    taskSequence
        ";
    $dbM->execute($sql);

    
    $tpl = new TTemplate();
    $tpl->loadFromFile(APP_ROOT_DIR."templates\\print_CheckList.html");

    $tpl->setParam("PRINTED_BY", getCurrentUserName() );
    $tpl->setParam("PRINTED_DATE", date("Y-m-d H:i:s",time() ));
    $tpl->setParam("BUSINESS_DATE", $businessDate);
    $tpl->setParam("LIST_NAME", $listName);
    
    
    $infoHeader = "";
    $infoHeader .="            <tr>";
    $infoHeader .="                <td align=\"left\"><b>No.</b></td>";
    $infoHeader .="                <td align=\"left\"><b>Task name</b></td>";
    if($showMaker===TRUE)
        $infoHeader .="                <td align=\"left\" width=\"170px\"><b>$makerName</b></td>";
    if($showChecker===TRUE)
        $infoHeader .="                <td align=\"left\"><b>$checkerName</b></td>";
    if($showSignOff===TRUE)
        $infoHeader .="                <td align=\"left\"><b>$signoffName</b></td>";
    $infoHeader .="            </tr>";
    
    
    
    $infoBody = "";
    $rows = $dbM->numRows();
    for($i=0;$i<$rows;$i++)
    {
          
        if($showActionDates===TRUE)
        {
            $infoBody .= "<tr>";
            $infoBody .= "  <td >".($i+1)."</td>";
            $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"taskName")."</td>";
            if($showMaker===TRUE)
                $infoBody .= "  <td align=\"left\" >".$dbM->getData($i,"MakerName")."<br/>".$dbM->getData($i,"makerDate")."</td>";
            if($showChecker===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"CheckerName")."<br/>".$dbM->getData($i,"checkerDate")."</td>";
            if($showSignOff===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"SignOffName")."<br/>".$dbM->getData($i,"signOffDate")."</td>";
            $infoBody .= "</tr>";
        }
        else
        {
            $infoBody .= "<tr>";
            $infoBody .= "  <td >".($i+1)."</td>";
            $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"taskName")."</td>";
            if($showMaker===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"MakerName")."</td>";
            if($showChecker===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"CheckerName")."</td>";
            if($showSignOff===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"SignOffName")."</td>";
            $infoBody .= "</tr>";
        }
    }
    $tpl->setParam("LIST_HEADER", $infoHeader);
    $tpl->setParam("LIST_BODY", $infoBody);
    
    echo $tpl->getData();
    
?>
