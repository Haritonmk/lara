<?php
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $definitionId = getEncryptedRequestValue("listId");
    $definitionId = prepareSQLParam($definitionId,"int");

    $dbM->execute("select * from ProcessDefinition where definitionId = $definitionId");
    $processName = $dbM->getData(0, "processName");
    $periodType = $dbM->getData(0, "periodType");
    $creationMode = $dbM->getData(0, "creationMode");
    $setDateMethod = $dbM->getData(0, "setDateMethod");
    $responsibleTeam = $dbM->getData(0, "responsibleTeam");
    
    echo "<div id=\"UpdateGenData\"></div>";
    
    $tbl = new TTable();
    $tbl->setWidth("60%");

    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("formLCGeneral.name","Nazwa listy")  ));
    $row->addCell(new TTableCell( "<b>".$processName."</b>"  ));
    $tbl->addRow($row);

    $ids = array("","day","week","month","quarter");
    $idn = array(
        "",
        getPageContent("formLCGeneral.fqDay","Dzienna"),
        getPageContent("formLCGeneral.fqWeek","Tygodniowa"),
        getPageContent("formLCGeneral.fqMonth","Miesięczna"),
        getPageContent("formLCGeneral.fqQuarter","Kwartalna")
        
    );
    $list = htmlDropDownList("lstFreq", "width:200px", "$periodType", $idn, $ids);
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("formLCGeneral.frequency","Częstotliwość")  ));
    $row->addCell(new TTableCell($list ));
    $tbl->addRow($row);
    
    $ids = array("","automatic","manual");
    $idn = array(
        "",
        getPageContent("formLCGeneral.ltAutomat","Automatycznie"),
        getPageContent("formLCGeneral.ltManual","Ręcznie")
        
    );
    $list = htmlDropDownList("lstCreateType", "width:200px", "$creationMode", $idn, $ids);
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("formLCGeneral.creationMethod","Sposób tworzenia")  ));
    $row->addCell(new TTableCell($list  ));
    $tbl->addRow($row);

    /*
    $ids = array("","nextBussinessDay","lastWorkWeekDay","lastWorkMonthDay");
    $idn = array(
        "",
        getPageContent("formLCGeneral.ctCurrentDay","Najbliższy dzień roboczy"),
        getPageContent("formLCGeneral.ctLastWeekBusinessDay","Ostatni dzień roboczy tygodnia"),
        getPageContent("formLCGeneral.ctLastMonthBussDay","Ostatni dzień roboczy miesiąca")
        
    );
    $list = htmlDropDownList("lstDateType", "width:200px", "$setDateMethod", $idn, $ids);
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("formLCGeneral.dateMethod","Sposób wyznaczenia daty")  ));
    $row->addCell(new TTableCell($list  ));
    $tbl->addRow($row);
*/
    $sql = "
            select 
                departmentShortName as 'idn', 
                departmentShortName + ' - ' +departmentFullName as 'desc' 
            from 
                dbo.departmentsList order by 1";
    
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    $idn = array("");
    $desc = array("");
    for($i=0;$i<$rows;$i++)
    {
        $idn[] = $dbM->getData($i, "idn");
        $desc[] = $dbM->getData($i, "desc");
    }
    
    $list = htmlDropDownList("lstOwnerTeam", "width:300px", "$responsibleTeam", $desc, $idn);
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("formLCGeneral.ownerTeam","Zespół odpowiedzialny")  ));
    $row->addCell(new TTableCell($list  ));
    $tbl->addRow($row);

    echo $tbl->getHtmlData();
    
    
    $ajaxValues = "";
    $ajaxValues .= "'listId=$definitionId' + ";
    $ajaxValues .= "'&lstFreq=' + $escFun($('#lstFreq').val()) + ";
    $ajaxValues .= "'&lstCreateType=' + $escFun($('#lstCreateType').val()) + ";
    $ajaxValues .= "'&lstDateType=' + $escFun($('#lstDateType').val()) + ";
    $ajaxValues .= "'&lstOwnerTeam=' + $escFun($('#lstOwnerTeam').val())  ";
    
    echo htmlEmptyArea(10);
    $info = getPageContent("formLCGeneral.save","Zapisz");
    echo htmlAjaxLinkButton("btSave", "", $info, "UpdateGenData", "listConfig.saveGeneral", $ajaxValues);
    
?>
