<?php
    verifyUserAccess("");
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();

    $definitionId = getEncryptedRequestValue("listId");
    $definitionId = prepareSQLParam($definitionId,"int");

    
    $sql = "
        select 
                h.changeDate 'Data zmiany',
                u.userDesc 'Użytkownik',
                h.changeType 'Rodzaj zmiany',
                h.changeFullInfo 'Dodatkowe informacje'
        from 
                dbo.processDefinitionTaskHistory h
                        join frmSystemUser u on u.userId = h.userId
        where
                h.definitionId = $definitionId
        order by
                h.changeDate        
    ";
    
    echo showResultInTable($sql, "100%");
    
?>
