<?php
    verifyUserAccess("manager");
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $form = new TForm();
    $form->setCaption(getPageContent("clTemplates.windowName","Szablony list") );

    $depList = "";
    $sql = "
            select 
                departmentShortName as 'idn', 
                departmentShortName + ' - ' +departmentFullName as 'desc' 
            from 
                dbo.departmentsList order by 1";
    
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    $idn = array("--");
    $desc = array("-- Wszystkie --");
    for($i=0;$i<$rows;$i++)
    {
        $idn[] = $dbM->getData($i, "idn");
        $desc[] = $dbM->getData($i, "desc");
    }
    
    $lastDepartment = getrequestValue("lastDepartment");
    $lastDepartment = prepareSQLParam($lastDepartment);

    $depList = htmlDropDownList("lastDepartment", "width:300px", "$lastDepartment", $desc, $idn,
            "onchange=\"changeDepartment() \"");
    
    //dodawanie nowej listy
    
    $info = getPageContent("formListTemplate.addNewList","Dodaj nową");
     
    $form->addContent("<div id=\"NewListAction\"></div>");

    $newBtn = htmlAjaxLinkButton("btNew", "", $info, "NewListAction", "listConfig.newList", "''");
    $tbl = new TTable();

    $row = new TTableRow();
    $row->addCell(new TTableCell("Dodawanie nowej listy"));
    $row->addCell(new TTableCell($newBtn));
    $tbl->addRow($row);

    $row = new TTableRow();
    $row->addCell(new TTableCell("Pokaż tylko listy wydziału:"));
    $row->addCell(new TTableCell($depList));
    $tbl->addRow($row);
    
    $form->addContent("<p align=\"left\">");
    $form->addContent($tbl->getHtmlData());
    $form->addContent("</p>");
    
    $form->addContent(htmlEmptyArea(4));
    
    
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell(new TTableCell(getPageContent("formListTemplate.lp","Lp.")  ));
    $row->addCell(new TTableCell(getPageContent("formListTemplate.listName","Nazwa")  ));
    $row->addCell(new TTableCell(getPageContent("formListTemplate.listActive","Aktywna")  ));
    $row->addCell(new TTableCell(getPageContent("formListTemplate.listTeam","Wydział")  ));
    $row->addCell(new TTableCell(getPageContent("formListTemplate.listCreationMode","Sposób tworzenia")  ));
    $row->addCell(new TTableCell(getPageContent("formListTemplate.listFrequency","Częstotliwość")  ));
    $tbl->addRow($row);

    if($lastDepartment=="" || $lastDepartment =="--")
        $sql = "select * from dbo.processDefinition order by responsibleTeam,processName";
    else
        $sql = "select * from dbo.processDefinition 
            where responsibleTeam = '$lastDepartment'
            order by responsibleTeam,processName";
        
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    for($i=0;$i<$rows;$i++)
    {
        $definitionId = $dbM->getData($i,"definitionId");
        $processName = $dbM->getData($i,"processName");
        $processActive = $dbM->getData($i,"processActive");
        if($processActive=="1")
            $processActive = "YES";
        else
            $processActive = "NO";
        
        $page = cryptURLParam("listConfiguration");
        $listIdCrypted = cryptURLParam($definitionId);
        $info = getPageContent("formListTemplate.edit","Edytuj");
        $processLink = htmlLink("$processName", "?page=$page&listId=$listIdCrypted") ;
        
        $row = new TTableRow();
        $row->addCell(new TTableCell( $i+1 ));
        $row->addCell(new TTableCell( $processLink ));
        $row->addCell(new TTableCell( $processActive ));
        $row->addCell(new TTableCell( $dbM->getData($i,"responsibleTeam") ));
        $row->addCell(new TTableCell( $dbM->getData($i,"creationMode") ));
        $row->addCell(new TTableCell( $dbM->getData($i,"periodType") ));
        $tbl->addRow($row);
    }
    
    $form->addContent($tbl->getHtmlData());
    
    echo $form->getHtmlData();
    $page = cryptURLParam("listTemplates");
?>
    <script type="text/javascript">
    <!-- 
    
    function changeDepartment( )
    {
        var value = $('#lastDepartment').val();
        window.location.href="?page=<? echo $page ?>&lastDepartment="+value;
    }
    
    //-->
    </script>
