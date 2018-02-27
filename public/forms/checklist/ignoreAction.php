<?php

    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    
    $edIgnore = getRequestValue("edIgnore");
    $edIgnore = encodeAjaxRequestData($edIgnore);
    $edIgnore = prepareSQLParam($edIgnore);
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");
    $taskInstance = getRequestValue("taskInstance");
    $taskInstance = prepareSQLParam($taskInstance, "int");
    
    if(strlen($edIgnore)<5)
    {
        echo htmlFormErrorMessage("Wprowadź powód braku realizacji");
        return;
    }
    
    if(strlen($edIgnore)>300)
    {
        echo htmlFormErrorMessage("Dozwolone max 300 znaków");
        return;
    }
    
    $sql = "select * from processInstanceTask where instanceTaskId = $taskInstance";
    $dbM->execute($sql);
    $taskName = $dbM->getData(0, "taskName");
    
    // data realizacji utworzona, ale bez osoby -> zadanie zignorowane
    $sql = "update dbo.[processInstanceTask] set 
                makerDate = getdate(),
                makerUserId = NULL
            where
                instanceTaskId = $taskInstance
                and makerUserId is null
        ";
    $dbM->execute($sql);
    $userName = getCurrentUserName();
    generateEvent(ET_IGNORE_TASK, $userName."</br>"."$edIgnore", $taskInstance, "ProcessInstanceTask");
    
    $action = cryptURLParam("checklist.showDetails");
    $script = "
            ajaxLoadData('pageControlId-body','$action','instanceId='+$instanceId);        
        ";
    echo htmlJavaScript($script);
    
?>
