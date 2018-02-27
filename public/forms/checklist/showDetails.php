<?php
    verifyUserAccess("");
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");

    setSessionValue("lastSelectedInstance", $instanceId);
    
    $sql = "
            select 
                    i.instanceId,
                    d.processName,
                    i.createDate,
                    i.businessdate,
                    i.instanceName,
                    i.status,
                    i.isGroupName
            from 
                    dbo.processInstance i
                            join dbo.processDefinition d on d.definitionId = i.definitionId
            where
                    i.instanceId = $instanceId
        ";
    
    $dbM->execute($sql);
    $rows = $dbM->numRows();

    $listName = $dbM->getData(0, "processName");
    $businessdate = $dbM->getData(0, "businessdate");
    $businessdate = substr($businessdate, 0,10);
    $clientName = $dbM->getData(0, "instanceName");
    $listStatus = $dbM->getData(0, "status");
    $isGroupName = $dbM->getData(0, "isGroupName");
    
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->addCell( new TTableCell(getPageContent("fmrCheckListTbl.listName", "Nazwa listy:")) );
    $row->addCell( new TTableCell("<b>$clientName</b>") );
    $row->addCell( new TTableCell(getPageContent("fmrCheckListTbl.date", "Data")) );
    $row->addCell( new TTableCell("<b>$businessdate</b>") );
    $tbl->addRow($row);


    echo $tbl->getHtmlData();
    
    echo htmlEmptyArea(10);
    
    $pageControl = new TPageControl();
    $pageControl->addTab(getPageContent("fmrCheckListTbl.tasksCurrent", "Zadania bieżace"), "checklist.task","instanceId=$instanceId&view=current");
    $pageControl->addTab(getPageContent("fmrCheckListTbl.tasksAll", "Zadania wszystkie"), "checklist.task","instanceId=$instanceId");
    $pageControl->addTab(getPageContent("fmrCheckListTbl.history", "Historia"), "checklist.history","instanceId=$instanceId");
    if($listStatus!=1) //lista nie jest zamknieta
        $pageControl->addTab(getPageContent("fmrCheckListTbl.options", "Opcje"), "checklist.options","instanceId=$instanceId");
    echo $pageControl->getHtmlData();
    
/*
    //lista zadan
    
  */  
?>
