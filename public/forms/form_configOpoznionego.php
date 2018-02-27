<?php
    verifyUserAccess("");
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    
    $delelem = getRequestValue("delelem");
    if(!empty($delelem)){
        $delelem = prepareSQLParam($delelem, "int");
        $dbM->execute("DELETE FROM powodsList WHERE id =".$delelem);
        header('Location: ?page='.cryptURLParam("configOpoznionego"));
        exit;
    }
    
    $nazwa = getRequestValue("nazwa");
    if(!empty($nazwa)){
        $nazwa = prepareSQLParam($nazwa);
        $dbM->execute("INSERT INTO powodsList (nazwa) VALUES ('".$nazwa."')");
    }
    
    $form = new TForm();
    $form->setCaption("Konfiguracja powodów opóźnionego");
    
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell( new TTableCell("Lp.") );
    $row->addCell(new TTableCell("Nazwa"));
    $row->addCell(new TTableCell("Action"));
    $tbl->addRow($row);
    
    $dbM->execute("SELECT * FROM powodsList ORDER BY id");
    $rows = $dbM->numRows();
    $powods = array();
    for($i=0;$i<$rows;$i++)
    {
        //$powods[$dbM->getData($i, "id")] = $dbM->getData($i, "nazwa");
        $row = new TTableRow();
        $row->addCell(new TTableCell($i + 1));
        $row->addCell(new TTableCell($dbM->getData($i, "nazwa")));
        $row->addCell(new TTableCell($dbM->getData($i, "id")!="5"?htmlLink("[delete]","?page=".cryptURLParam("configOpoznionego")."&delelem=".$dbM->getData($i, "id"),"class='delelem'"):""));
        $tbl->addRow($row);
    }
    $form->addContent($tbl->getHtmlData());
    
    $form->addContent("<p style='text-align:left;'>Add new status</p>");
    $form->addContent("<form method='post' style='text-align:left;'>");
    $form->addContent(htmlEdit("nazwa", "display:inline-block;", ""));
    $form->addContent(htmlSubmitButton('save', "display:inline-block;", "Save"));
    $form->addContent("</form>");
    
    $script = "$('.delelem').click(function(){return confirm('Are you sure?');});";
    $form->addContent(htmlJavaScript($script));
    
    echo $form->getHtmlData();
?>