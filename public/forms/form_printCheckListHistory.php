<?php

    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $currentUser = getCurrentUserId();
    $applicatioId = getCurrentApplicationId();
    
    $list = getRequestValue("list");
    $list = prepareSQLParam($list, "int");

    $sql = "select * from [processInstance] where instanceId = $list";
    $dbM->execute($sql);
    $listName = $dbM->getData(0,"instanceName");
    $businessDate = $dbM->getData(0,"businessDate");
    
    
    $sql = "
        select
                e.eventDate ,
                u.userdesc,
                et.eventtypedesc,
                e.addInfo
        from
                frmEvent e
                        join frmEventType et on et.eventtypeid = e.eventtypeid
                        join frmSystemUser u on u.userId = e.userid
        where
                e.refKey = $list
                and refTable = 'ProcessInstanceTask'
        order by
                e.eventdate    
        ";
    $dbM->execute($sql);
 
    
    $tpl = new TTemplate();
    $tpl->loadFromFile(APP_ROOT_DIR."templates\\print_CheckListHistory.html");

    $tpl->setParam("PRINTED_BY", getCurrentUserName() );
    $tpl->setParam("PRINTED_DATE", date("Y-m-d H:i:s",time() ));
    $tpl->setParam("BUSINESS_DATE", $businessDate);
    $tpl->setParam("LIST_NAME", $listName);
    
    $infoBody = "";
    $rows = $dbM->numRows();
    for($i=0;$i<$rows;$i++)
    {
          
        $infoBody .= "<tr>";
        $infoBody .= "  <td align=\"left\">".($i+1)."</td>";
        $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"eventDate")."</td>";
        $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"userdesc")."</td>";
        $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"eventtypedesc")."</td>";
        $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"addInfo")."</td>";
        $infoBody .= "</tr>";
    }
    $tpl->setParam("LIST_BODY", $infoBody);
    
    echo $tpl->getData();
    
    
?>
