<?php
    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");
    
    $blankPage = cryptURLParam("printCheckListHistory");
    echo "<a href=\"?blankPage=$blankPage&list=$instanceId\" target=\"_blank\"><img src=\"img/printer.jpeg\" width=\"30\" border=\"0\"/></a>" ;
    echo htmlEmptyArea(4);
    
    $sql = "
        select
                e.eventDate 'Date',
                u.userdesc 'User',
                et.eventtypedesc 'Event type',
                e.addInfo 'Additional info'
        from
                frmEvent e
                        join frmEventType et on et.eventtypeid = e.eventtypeid
                        join frmSystemUser u on u.userId = e.userid
        where
                e.refKey = $instanceId
                and refTable = 'ProcessInstanceTask'
        order by
                e.eventdate    
    ";
    
    echo showResultInTable($sql, "100%") ;
?>
