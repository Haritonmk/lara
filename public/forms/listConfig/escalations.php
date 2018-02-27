<?php
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $definitionId = getEncryptedRequestValue("listId");
    $definitionId = prepareSQLParam($definitionId,"int");
    
    $sql = "
        select 
                * 
        from 
                dbo.processDefinitionAlerts 
        where
                ProcessDefinitionId = $definitionId
                and contactType = 'escalation'
        order by
                emailAddress        
    ";
    
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    
    $emails = "";
    for($i=0;$i<$rows;$i++)
    {
        $emailAddress = $dbM->getData($i, "emailAddress");
        $emails .= $emailAddress."\r\n";
    }
   
    $notHelp = getPageContent("formListConfig.notHelp","Podaj adresy email do powiadomień (każdy adres w nowej linii).");    
    echo htmlFormInfoMessage($notHelp);
   
    echo "<div id=\"NotConfigSaver\"></div>";
    echo htmlTextArea("edEmails", "width:80%", $emails);
    
    $ajaxValues = "";
    $ajaxValues .= "'listId=$definitionId' + ";
    $ajaxValues .= "'&edEmails=' + $escFun($('#edEmails').val()) ";
   
    
    $info = getPageContent("formListConfig.save","Zapisz");
    echo htmlAjaxLinkButton("btSave", "", "$info","NotConfigSaver" , "listConfig.saveEscalation", "$ajaxValues");
?>
