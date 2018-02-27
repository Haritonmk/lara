<?php
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");

    $view = getRequestValue("view");
    
    $taskInstance = getRequestValue("taskInstance");
    $type = getRequestValue("type");
    
    $form = new TModalForm();
    $form->addContent("<div id=\"RollbackDivAction\"></div>");
    $form->setWidth("450px");
    $form->setCaption("Wycofanie zadania");
    $form->setHelpMessage("Powód wycofania, min 5 max 300 znaków (krzyżyk anuluje wycofanie).");
    
    
    $form->addContent(htmlTextArea("edReason", "width:400px;height:100px;", "") );
    
    $parms = "";
    $parms .= "'taskInstance=$taskInstance' + ";
    $parms .= "'&instanceId=$instanceId' + ";
    $parms .= "'&type=rollback' +  ";
    $parms .= "'&view=$view' + ";
    $parms .= "'&edReason=' + $escFun($('#edReason').val()) ";
    
    $btn = htmlAjaxLinkButton("btSave", "", "Zapisz", "RollbackDivAction", "checklist.rollbackAction",$parms );
    $form->addContent( "<p align=\"center\">" );
    $form->addContent( $btn );
    $form->addContent( "</p>" );
    
    echo $form->getHtmlData();
?>
