<?php

    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    
    $edReason = getRequestValue("edReason");
    $edReason = encodeAjaxRequestData($edReason);
    $edReason = prepareSQLParam($edReason);
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");
    $taskInstance = getRequestValue("taskInstance");
    $taskInstance = prepareSQLParam($taskInstance, "int");
    
    if(strlen($edReason)<5)
    {
        echo htmlFormErrorMessage("Wprowadź powód wycofania");
        return;
    }
    
    if(strlen($edReason)>300)
    {
        echo htmlFormErrorMessage("Dozwolone max 300 znaków");
        return;
    }
    
    $sql = "select * from processInstanceTask where instanceTaskId = $taskInstance";
    $dbM->execute($sql);
    $taskName = $dbM->getData(0, "taskName");
    
    $sql = "update dbo.[processInstanceTask] set 
                makerDate = NULL,
                makerUserId = NULL,
                checkerDate = NULL,
                checkerUserId = NULL
            where
                instanceTaskId = $taskInstance
        ";
    $dbM->execute($sql);
    generateEvent(ET_ROLLBACK_TASK, "[$taskName] Powód: $edReason", $instanceId, "ProcessInstanceTask");
    
    $action = cryptURLParam("checklist.showDetails");
    $script = "
            ajaxLoadData('pageControlId-body','$action','instanceId='+$instanceId);        
        ";
    echo htmlJavaScript($script);
    
?>
