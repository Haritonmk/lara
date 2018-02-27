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
    $form->addContent("<div id=\"IgnoreDivAction\"></div>");
    $form->setWidth("470px");
    $form->setCaption("Potwierdzenie brak realizacji zadania");
    $form->setHelpMessage("Powód braku realizacji, min 5 max 300 znaków (krzyżyk anuluje pominięcie zadania).");
    
    
    $form->addContent(htmlTextArea("edIgnore", "width:400px;height:100px;", "") );
    
    $parms = "";
    $parms .= "'taskInstance=$taskInstance' + ";
    $parms .= "'&instanceId=$instanceId' + ";
    $parms .= "'&type=ignore' +  ";
    $parms .= "'&view=$view' + ";
    $parms .= "'&edIgnore=' + $escFun($('#edIgnore').val()) ";
    
    $btn = htmlAjaxLinkButton("btSave", "", "Zapisz", "IgnoreDivAction", "checklist.ignoreAction",$parms );
    $form->addContent( "<p align=\"center\">" );
    $form->addContent( $btn );
    $form->addContent( "</p>" );
    
    echo $form->getHtmlData();
?>
