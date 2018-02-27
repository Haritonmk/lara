<?php

    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    
    $edDateFrom = encodeAjaxRequestData(getRequestValue("edDateFrom"));
    $edDateTo = encodeAjaxRequestData(getRequestValue("edDateTo"));
    $edTemplate = encodeAjaxRequestData(getRequestValue("edTemplate"));
    
    if( !isDateFormat($edDateFrom) )
    {
        echo htmlFormErrorMessage(getPageContent("fmrCheckListHist.err1", "Podaj poprawnie datę od"));
        return;
    }

    if( !isDateFormat($edDateTo) )
    {
        echo htmlFormErrorMessage(getPageContent("fmrCheckListHist.err1", "Podaj poprawnie datę do"));
        return;
    }
    
    $edDateFrom = prepareSQLParam($edDateFrom);
    $edDateTo = prepareSQLParam($edDateTo);
    $edTemplate = prepareSQLParam($edTemplate);
    
    $sql = "
        select
                *
        from
                dbo.processInstance
        where
                instanceName = '$edTemplate'
                and businessDate >='$edDateFrom'	
                and businessDate <='$edDateTo'
                and status = 1
        order by
                businessDate        
        ";
    
    $dbM->execute($sql);
    $rows = $dbM->numRows();

    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell( new TTableCell(getPageContent("fmrCheckListHist.name", "Nazwa")) );
    $row->addCell( new TTableCell(getPageContent("fmrCheckListHist.listDate", "Data")) );
    $tbl->addRow($row);
    
    for($i=0;$i<$rows;$i++)
    {
        $instanceName = $dbM->getData($i, "instanceName");
        $instanceId = $dbM->getData($i, "instanceId");
        $listLink = htmlAjaxLink($instanceName, "SelectedListDetails", "checklist.showDetails", "'instanceId=$instanceId'");
        
        $row = new TTableRow();
        $row->addCell( new TTableCell($listLink ) );
        $row->addCell( new TTableCell(substr($dbM->getData($i, "businessdate"), 0,10)) );
        $tbl->addRow($row);
    }

    echo $tbl->getHtmlData();
    
    /*
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell( new TTableCell( getPageContent("fmrCheckList.lp", "Lp.")  ) );
    $row->addCell( new TTableCell(getPageContent("fmrCheckList.template", "Szablon")) );
    $row->addCell( new TTableCell(getPageContent("fmrCheckList.name", "Nazwa")) );
    $row->addCell( new TTableCell(getPageContent("fmrCheckList.date", "Date")) );
    $tbl->addRow($row);
    
    for($i=0;$i<$rows;$i++)
    {
        $listName = $dbM->getData($i, "processName");
        $instanceName = $dbM->getData($i, "instanceName");
        $instanceId = $dbM->getData($i, "instanceId");
        $listLink = htmlAjaxLink($instanceName, "SelectedListDetails", "checklist.showDetails", "'instanceId=$instanceId'");
        
        $row = new TTableRow();
        $row->addCell( new TTableCell( $i+1 ) );
        $row->addCell( new TTableCell($listName) );
        $row->addCell( new TTableCell($listLink ) );
        $row->addCell( new TTableCell(substr($dbM->getData($i, "businessdate"), 0,10)) );
        $tbl->addRow($row);
    }
    
    return $tbl->getHtmlData();
    */
?>
