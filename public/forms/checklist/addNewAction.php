<?php

    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    
    $edDate = encodeAjaxRequestData(getRequestValue("edDate"));
    $edTemplate = encodeAjaxRequestData(getRequestValue("edTemplate"));
    

    if($edTemplate == "")
    {
        echo htmlFormErrorMessage(getPageContent("fmrCheckListAdd.err1", "Wybierz szablon listy kontrolnej"));
        return;
    }
    
    if(!isDateFormat($edDate) )
    {
        echo htmlFormErrorMessage(getPageContent("fmrCheckListAdd.err2", "Podaj poprawnie datę listy"));
        return;
    }
    
    $edTemplate = prepareSQLParam($edTemplate,"int");
    $edDate = prepareSQLParam($edDate);
    $userId = getCurrentUserId();

    $sql = "select * from processDefinition where definitionId = $edTemplate";
    $dbM->execute($sql);
    $definitionId = $dbM->getData(0, "definitionId");
    $instanceName = $dbM->getData(0, "processName");
    
    //sprawdzimy, czy lista na dany dzien juz zostala utworzona
    $sql = "select * from dbo.processInstance where businessDate = '$edDate' and definitionId = $definitionId";
    $dbM->execute($sql);
    if( $dbM->numRows() >0 )
    {
        echo htmlFormErrorMessage(getPageContent("fmrCheckListAdd.err3", "Lista na ten dzień już istnieje"));
        return;
    }
    
    $sql = "exec dbo.createProcessInstance $definitionId,$userId,'$instanceName','$edDate'";
    
    try
    {
        $dbM->execute($sql);
    }
    catch(Exception $e)
    {
        echo htmlFormErrorMessage(getPageContent("fmrCheckListAdd.err3", "Wystąpił błąd podczas zakładania listy"));
        return;
    }

    $page = cryptURLParam("checklist");
    echo htmlJavaScript(" window.location.href=\"?page=$page\"; ");
    
?>
