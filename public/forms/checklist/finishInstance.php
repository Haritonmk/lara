<?php

    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");

    $edReason = getRequestValue("edReason");
    $edReason = encodeAjaxRequestData($edReason);
    $edReason = prepareSQLParam($edReason);

    //sprawdzamy czy wszystkie zadania wykonane
    $sql ="
        select 
                count(*) as 'cnt' 
        from 
                dbo.processInstanceTask
        where
                instanceId = $instanceId
                and isnull(isgroupname,0)=0
                and makerDate is null        
    ";
    $dbM->execute($sql);
    $cnt = $dbM->getData(0,"cnt");
    
    if($cnt>0)
    {
        if(strlen($edReason)<20)
        {
            echo htmlFormErrorMessage("Nie podano powodu zamknięcia listy mimo niewykonanych zadań (min. 20 znaków)");
            return;
        }
    }
    
    
    $sql = "update dbo.[processInstance] set 
                closeDate = getdate(),
                closedByUserId = $userId,
                status = 1
            where 
                instanceId = $instanceId
            ";
    $dbM->execute($sql);
    generateEvent(ET_MARK_LIST_COMPLETED, "$edReason", $instanceId, "ProcessInstanceTask");

    
    $finishMessage = getPageContent("fmrCheckListFinish.message", "Lista została zamknięta!");
    //forward
    $page = cryptURLParam("checklist");
    echo htmlJavaScript(" 
            alert('$finishMessage');
            window.location.href=\"?page=$page\"; 

            ");
?>
