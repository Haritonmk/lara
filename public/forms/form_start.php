<? 
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();


    $selectedGroupId = getRequestValue("parGroup");
    $selectedGroupId = prepareSQLParam($selectedGroupId);
    
    $form = new TForm();
    $form->setCaption( "Procesy" );

    $form->addContent("<div id=\"NewProcessDiv\"></div>");
    
    
    //---------- grupa procesow
    $tbl = new TTable();
    
    $groupDesc[] = "-- Wybierz --";
    $groupId[] = "";
    $sql = "select * from dbo.processGroup order by groupName";
    $dbM->execute($sql);
    for($i=0;$i<$dbM->numRows();$i++)
    {
        $groupDesc[] = $dbM->getData($i, "groupName");
        $groupId[] = "".$dbM->getData($i, "groupId")."";
    }
    $groupList = htmlDropDownList("parGroup", "width:250px;", "$selectedGroupId", $groupDesc, $groupId);

    $row = new TTableRow();
    $row->addCell(new TTableCell("Wybierz grupę procesów"));
    $row->addCell(new TTableCell($groupList));
    $row->addCell(new TTableCell(htmlSubmitButton("btGr", "", "Zmień")));
    $row->addCell(new TTableCell(htmlAjaxLinkButton("btNew", "", "Dodaj nowy", "NewProcessDiv", "process.new", "''") ));
    $row->addCell(new TTableCell(htmlAjaxLinkButton("btContact", "", "Kontakty", "NewProcessDiv", "process.contacts", "'groupId='+$escFun($('#parGroup').val())") ));
    $tbl->addRow($row);
    
    $form->addContent("<form method=\"POST\">");
    $form->addContent($tbl->getHtmlData());
    $form->addContent("</form>");
    $form->addContent(htmlEmptyArea(10));
    
    
    
    $tbl = new TTable();
    $tbl->setWidth("100%");
    
    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell(new TTableCell("Lp"));
    $row->addCell(new TTableCell("Id"));
    $row->addCell(new TTableCell("Nazwa procesu"));
    $row->addCell(new TTableCell("Monitoring aktywny"));
    $row->addCell(new TTableCell("Host"));
    $row->addCell(new TTableCell("DBsrv1"));
    $row->addCell(new TTableCell("DBsrv2"));
    $row->addCell(new TTableCell("Rodzaj"));
    $row->addCell(new TTableCell("Warunek testu"));
    $row->addCell(new TTableCell("Ostatni test"));
    $row->addCell(new TTableCell("Czas testu [s]"));
    $row->addCell(new TTableCell("Status"));
    $row->addCell(new TTableCell("Odśwież"));
    $row->addCell(new TTableCell("Edytuj"));
    $tbl->addRow($row);
    
    
    if($selectedGroupId=="")
        $selectedGroupId = 2222222;
    
    $whereGroup = "";
    if($selectedGroupId!="")
        $sql ="exec dbo.getProcessStatus $selectedGroupId";
    else
        $sql ="exec dbo.getProcessStatus";

    $dbM->execute($sql);
    $rows = $dbM->numRows();
    
    for($i=0;$i<$rows;$i++)
    {
        $txtActive = $dbM->getData($i, "processActive");;
        if($txtActive=="1")
            $txtActive = "TAK";
        else
            $txtActive = "<font color=\"#FF0000\"><b>NIE</b></font>";
        
        
        $groupName = $dbM->getData($i, "groupName");
        $definitionId = $dbM->getData($i, "definitionId");
        $processName = $dbM->getData($i, "processName");
        $processType = $dbM->getData($i, "processType");
        $processActive = $dbM->getData($i, "processActive");
        $pageHost = $dbM->getData($i, "pageHost");
        $processCheckSchedule = $dbM->getData($i, "processCheckSchedule");
        $processActiveTxt = "NIE";
        if($processActive=="1") $processActiveTxt = "TAK";
        $errorFlag = $dbM->getData($i, "errorFlag");

        $row = new TTableRow();
        $row->addCell(new TTableCell( $i+1 ));
        $row->addCell(new TTableCell("$definitionId"));
        $row->addCell(new TTableCell("$processName"));
        $row->addCell(new TTableCell("$txtActive"));
        $row->addCell(new TTableCell("$pageHost"));
        $row->addCell(new TTableCell($dbM->getData($i, "dbServerFirst")));
        $row->addCell(new TTableCell($dbM->getData($i, "dbServerSecond")));
        $row->addCell(new TTableCell("$processType"));
        $row->addCell(new TTableCell("$processCheckSchedule"));
        $row->addCell(new TTableCell($dbM->getData($i, "resultDate")));
        $row->addCell(new TTableCell($dbM->getData($i, "loadingTime")));
        
        if($errorFlag=="1")       
            $procStatusData = "<span id=\"ps-$definitionId\"><font color=\"#FF0000\"><b>".$dbM->getData($i, "errorInfo")."</b></font></span>";
        else
            $procStatusData = "<span id=\"ps-$definitionId\"><font color=\"#008000\">".$dbM->getData($i, "errorInfo")."</font></span>";
        $row->addCell(new TTableCell("$procStatusData"));

        $action = cryptURLParam("process.getProcessStatus");
        $refreshLink = "<a style=\"cursor:pointer\"  onclick=\"ajaxLoadData('ps-$definitionId','$action','processId=$definitionId&processType=$processType')\" ><img src=\"../../apps/ProcessMonitor/img/refresh.png\" border=\"0\"/></a>";    
        $row->addCell(new TTableCell("$refreshLink"));

        $action = cryptURLParam("process.editProcess");
        $editLink = "<a style=\"cursor:pointer\"  onclick=\"ajaxLoadData('ps-$definitionId','$action','processId=$definitionId&processType=$processType')\" ><img src=\"../../apps/ProcessMonitor/img/edit.png\" border=\"0\"/></a>";    
        $row->addCell(new TTableCell("$editLink"));
        
        $tbl->addRow($row);
    }
    
    
    $form->addContent($tbl->getHtmlData());
    
    
    echo $form->getHtmlData();
?>

