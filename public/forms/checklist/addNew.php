<?php
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    
    
    $sql = "
            select * from ProcessDefinition 
            where
                    processType = 'CHECKLIST'
                    and processActive = 1
            order by processName
            ";
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    $listValue = array();
    $listName = array();
    for($i=0;$i<$rows;$i++)
    {
        $listValue[] = "".$dbM->getData($i, "definitionId")."";
        $listName[] = $dbM->getData($i, "processName");
    }
    $templateList = htmlDropDownList("edTemplate", "width:350px", array(), $listName, $listValue);
    
    $form = new TModalForm();
    $form->setCaption(getPageContent("fmrCheckListAdder.caption", "Tworzenie nowej listy kontrolnej"));
    $form->setWidth("500px");
    $form->addContent("<div id=\"NewListAddAction\"></div>");

    
    $tbl = new TTable();
    $tbl->setWidth("100%");
    
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("fmrCheckListAdder.template", "Szablon checklisty:")));
    $row->addCell(new TTableCell($templateList ));
    $tbl->addRow($row);
    
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("fmrCheckListAdder.listDate", "Data (dzień początkowy):")));
    $row->addCell(new TTableCell(htmlDateSelector("edDate", "", "") ));
    $tbl->addRow($row);

    $form->addContent($tbl->getHtmlData());
    
    $ajaxValues = "'edDate=' + $escFun($('#edDate').val()) + ";
    $ajaxValues .= "'&edTemplate=' + $escFun($('#edTemplate').val())";
    
    $form->addContent("<p align=\"center\">");
    $form->addContent(htmlAjaxLinkButton("btSave", "", getPageContent("fmrCheckListAdder.addBtn", "Dodaj"), "NewListAddAction", "checklist.addNewAction", "$ajaxValues"));
    $form->addContent("</p>");
    
    echo $form->getHtmlData();
?>
