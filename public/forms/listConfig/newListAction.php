<?php
    verifyUserAccess("");
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $listName = prepareSQLParam(encodeAjaxRequestData(getRequestValue("listName")));
    $copyFrom = prepareSQLParam(getRequestValue("copyFrom"));
    
    //czy podano nazwe
    if(strlen($listName)<5)
    {
        $info = getPageContent("formNewListDef.errNoListName","Podaj nazwę listy");
        echo htmlFormErrorMessage($info);
        return;
    }
    
    //czy taka lista juz istnieje
    $sql = "select * from dbo.processDefinition where processName = '$listName'";
    $dbM->execute($sql);
    if($dbM->numRows()!=0)
    {
        $info = getPageContent("formNewListDef.errListExists","Lista o takiej nazwie już istnieje");
        echo htmlFormErrorMessage($info);
        return;
    }

    $sql = "
	INSERT INTO [CheckList_PL].[dbo].[processDefinition]
           ([groupId]
           ,[processType]
           ,[processName]
           ,[processActive]
		)
        VALUES
           (1
           ,'CHECKLIST'
           ,'$listName'
           ,1
	   )        
        ";
    
    $dbM->execute($sql);
    $dbM->execute("select identity as lastId");//ta_doble
    $lastId = $dbM->getData(0,"lastId");
    
    
    if($copyFrom!="")
    {
        $copyFrom = prepareSQLParam($copyFrom, "int");

        $sql = "
            INSERT INTO [dbo].[processDefinitionTask]
                       ([definitionId]
                       ,[active]
                       ,[taskName]
                       ,[taskSequence]
                       ,[makerNeeded]
                       ,[checkerNeeded]
                       ,[signOffNeeded]
                       ,[cutOffTime]
                       ,[taskAddInfo]
                       ,[responsibleArea]
                       ,[isGroupName]
                       ,[showMaker]
                       ,[showChecker]
                       ,[showSignoff]
                       ,[makerName]
                       ,[checkerName]
                       ,[signoffName]
                       ,[showActionDates]
                       ,[showCutOffTime])
                 SELECT
                       $lastId
                       ,[active]
                       ,[taskName]
                       ,[taskSequence]
                       ,[makerNeeded]
                       ,[checkerNeeded]
                       ,[signOffNeeded]
                       ,[cutOffTime]
                       ,[taskAddInfo]
                       ,[responsibleArea]
                       ,[isGroupName]
                       ,[showMaker]
                       ,[showChecker]
                       ,[showSignoff]
                       ,[makerName]
                       ,[checkerName]
                       ,[signoffName]
                       ,[showActionDates]
                       ,[showCutOffTime]
                    FROM
                            [processDefinitionTask]
                    WHERE
                            [definitionId] = $copyFrom
            ";
            $dbM->execute($sql);
    }
    
    
    $page = cryptURLParam("listTemplates");
    echo htmlJavaScript(" window.location.href=\"?page=$page\"; ");
?>
