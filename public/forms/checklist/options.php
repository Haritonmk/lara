<?php
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");
    
    $sql ="
        select 
                count(*) as 'cnt' 
        from 
                dbo.processInstanceTask
        where
                instanceId = $instanceId
                and isnull(isgroupname,0)=0
                and makerDate is null        
    ";
    $dbM->execute($sql);
    $cnt = $dbM->getData(0,"cnt");
    
    $form = new TForm();
    $form->setCaption(getPageContent("fmrCheckListOptions.operations", "Operacje"));
    
    $form->addContent("<div id=\"OperContainer\">");
    if($cnt>0)
    {
        $form->setErrorMessage("Na liście istnieją zadania, które nie są wykonane");
    }        
    $form->addContent("</div>");
        
    
    

    $tbl = new TTable();

    $ajaxValues = "";
    $ajaxValues .= "'instanceId=$instanceId'";

    if($cnt>0)
    {
        $info = "Podaj powód zamknięcia listy mimo niedokończonych zadań (min 20 znaków).";
        $message = getPageContent("fmrCheckListOptions.notCompleted", "$info");
        $row = new TTableRow();
        $row->addCell(new TTableCell($info));
        $actionTxt = htmlTextArea("edReason", "width:400px;height:100px;", "");
        $row->addCell(new TTableCell( $actionTxt ));
        $tbl->addRow($row);
        $ajaxValues .= " + '&edReason=' + $escFun($('#edReason').val()) ";
    }
    
    $row = new TTableRow();
    $row->addCell(new TTableCell(getPageContent("fmrCheckListOptions.finish", "Zatwierdź listę i oznacz jako zakończoną")));
    $actionButton = htmlAjaxLinkButton("btGo", "", getPageContent("fmrCheckListOptions.run", "Wykonaj"), "OperContainer", "checklist.finishInstance", "$ajaxValues");
    $row->addCell(new TTableCell( $actionButton ));
    $tbl->addRow($row);
    
    $form->addContent("<div align=\"left\">");
    $form->addContent($tbl->getHtmlData());
    $form->addContent("</div>");

    echo $form->getHtmlData();
    
    
?>
