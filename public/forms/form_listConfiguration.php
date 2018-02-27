<?php
    verifyUserAccess("manager");
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();

    $definitionId = getEncryptedRequestValue("listId");
    $definitionId = prepareSQLParam($definitionId,"int");
    
    $sql = "select * from dbo.processDefinition where definitionId = $definitionId";
    $dbM->execute($sql);
    $listName = $dbM->getData(0,"processName");
    
    $form = new TForm();
    $form->setCaption(getPageContent("formListConfig.windowName","Konfiguracja listy") );
    $form->setHelpMessage($listName);
    
    $pageControl = new TPageControl();
    $pageControl->setObjectId("pcTaskConfig");
    $pageControl->addTab(getPageContent("formListConfig.tabGeneral","Ogólne"), "listConfig.general","listId=".  getRequestValue("listId"));
    $pageControl->addTab(getPageContent("formListConfig.tabTask","Zadania"), "listConfig.tasks","listId=".  getRequestValue("listId"));
    $pageControl->addTab(getPageContent("formListConfig.tabHistory","Historia zmian"), "listConfig.history","listId=".  getRequestValue("listId"));
    $pageControl->addTab(getPageContent("formListConfig.tabNotification","Powiadomienia"), "listConfig.notifications","listId=".  getRequestValue("listId"));
    $pageControl->addTab(getPageContent("formListConfig.tabEscalations","Eskalacje"), "listConfig.escalations","listId=".  getRequestValue("listId"));
    $form->addContent( $pageControl->getHtmlData() );
   
    
    echo $form->getHtmlData();
?>
