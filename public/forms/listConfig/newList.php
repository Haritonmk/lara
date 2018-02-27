<?php
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $form = new TModalForm();
    $form->setWidth("500px");
    $form->setCaption(getPageContent("formNewListDef.windowName","Nowa lista kontrolna"));

    $form->addContent("<div id=\"AddAction\"></div>");
    
    
    $sql = "
            select * from ProcessDefinition 
            where
                    processType = 'CHECKLIST'
                    and processActive = 1
            order by processName
            ";
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    $listValue = array("");
    $listName = array("");
    for($i=0;$i<$rows;$i++)
    {
        $listValue[] = "".$dbM->getData($i, "definitionId")."";
        $listName[] = $dbM->getData($i, "processName");
    }
    $templateList = htmlDropDownList("copyFrom", "width:300px", array(), $listName, $listValue);
    
    
    
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("formNewListDef.name","Nazwa listy")  ));
    $row->addCell(new TTableCell(htmlEdit("listName", "width:300px", "")  ));
    $tbl->addRow($row);
    
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("formNewListDef.copyFrom","Kopiuj zadania z:")  ));
    $row->addCell(new TTableCell($templateList  ));
    $tbl->addRow($row);
    
    $form->addContent($tbl->getHtmlData());

    $ajaxValues = "";
    $ajaxValues .= "'listName=' + $escFun($('#listName').val()) + ";
    $ajaxValues .= "'&copyFrom=' + $escFun($('#copyFrom').val())  ";
    
    $form->addContent(htmlEmptyArea(10));
    $form->addContent("<div align=\"center\">");
    $info = getPageContent("formNewListDef.addBtn","Zapisz");
    $form->addContent(htmlAjaxLinkButton("btAdd", "", $info, "AddAction", "listConfig.newListAction", "$ajaxValues"));
    $form->addContent("</div>");
    
    echo $form->getHtmlData();
?>
