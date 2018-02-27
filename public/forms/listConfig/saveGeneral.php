<?php
    verifyUserAccess("");
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $definitionId = getRequestValue("listId");
    $definitionId = prepareSQLParam($definitionId,"int");

    $lstCreateType = prepareSQLParam(getRequestValue("lstCreateType"));
    $lstFreq = prepareSQLParam(getRequestValue("lstFreq"));
    $lstDateType = prepareSQLParam(getRequestValue("lstDateType"));
    $lstOwnerTeam = prepareSQLParam(getRequestValue("lstOwnerTeam"));
    
    $sql = "
           UPDATE [dbo].[processDefinition]
           SET 
               [creationMode] = '$lstCreateType'
              ,[periodType] = '$lstFreq'
              ,[responsibleTeam] = '$lstOwnerTeam'
              ,[setDateMethod] = '$lstDateType'
         WHERE definitionId = $definitionId       
    ";
    $dbM->execute($sql);
    $info = getPageContent("formLCGeneral.saveSuccess","Zmiany zostały zapisane");
    echo htmlFormSuccessMessage($info);
?>
