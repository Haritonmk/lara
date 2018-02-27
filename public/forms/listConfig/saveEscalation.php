<?php

    verifyUserAccess("");
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $definitionId = getRequestValue("listId");
    $definitionId = prepareSQLParam($definitionId,"int");

    $edEmails = getRequestValue("edEmails");
    $edEmails = prepareSQLParam($edEmails);
    
    $emailsList = explode("\n", $edEmails);

    $dbM->execute("
                delete 
                    from [dbo].[processDefinitionAlerts]                
                where 
                    processDefinitionId = $definitionId
                    and contactType = 'escalation'
            ");

    for($i=0;$i<count($emailsList);$i++)
    {
        $emailPos = $emailsList[$i];
        $emailPos = trim($emailPos);
        if(isEmailAddress($emailPos))
        {
            $sql = "
                INSERT INTO [dbo].[processDefinitionAlerts]
                       ([processDefinitionId]
                       ,[emailAddress]
                       ,[contactType])
                 VALUES
                       ($definitionId
                       ,'$emailPos'
                       ,'escalation')    
            ";
            $dbM->execute($sql);
        }
    }
    $info = getPageContent("formListConfig.notSaved","Zmiana została zapisana");    
    echo htmlFormSuccessMessage($info);

?>
