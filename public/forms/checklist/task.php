<?php
    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    $escFun = getJSEscapeFunction();
    
    $instanceId = getRequestValue("instanceId");
    $instanceId = prepareSQLParam($instanceId, "int");

    $view = getRequestValue("view");

    echo "<div id=\"RollbackDiv\"></div>";
    echo "<div id=\"IgnoreDiv\"></div>";
    
    $taskInstance = getRequestValue("taskInstance");
    $type = getRequestValue("type");
    // list powodow start
    $dbM->execute("SELECT * FROM powodsList ORDER BY id");
    $rows = $dbM->numRows();
    $powods = array();
    for($i=0;$i<$rows;$i++)
    {
        $powods[$dbM->getData($i, "id")] = $dbM->getData($i, "nazwa");
    }
    // end
    while($taskInstance!="" && $type !="")
    {
        $taskInstancenceId = prepareSQLParam($taskInstance, "int");
        $type = prepareSQLParam($type);
        
        $sql = "select t.*,dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) as maxDate,
                    case
                     when dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) < getdate() then '1'
                     else 0 end
                    as 'redAlert' from dbo.processInstanceTask t join ProcessInstance pi on pi.instanceId = t.instanceId
                        join Processdefinition pd on pd.definitionid = pi.definitionid where t.instanceTaskId = $taskInstance";
        $dbM->execute($sql);
        $taskName = $dbM->getData(0,"taskName");
        
        if($type=="maker")
        {
            if($dbM->getData(0,"makerUserId")!="")
                    break;
            if($dbM->getData(0,"checkerUserId")==$userId)
                    break;
            
            $curentDate = new DateTime('NOW');
            $date = new DateTime('NOW');
            $maxDate = $dbM->getData(0,"maxDate");
            $makerDate = $dbM->getData(0,"makerDate");
            $redAlert = $dbM->getData(0,"redAlert");
            $time = $dbM->getData(0, "cutOffTime");
            $edPowods = getRequestValue("edPowods");
            $edReason = prepareSQLParam(getRequestValue("edReason"));
            $dopSql = '';
            if(!empty($edPowods)){
                $edPowods = prepareSQLParam($edPowods, "int");
                if($edPowods*1 == 5){
                    $edReason = trim($edReason);
                    if((mb_strlen($edReason) < 5)||(mb_strlen($edReason) > 300)){
                        break;
                    }
                    
                    $dopSql = ", powodID = ".$edPowods.", powodDescription = N'".$edReason."'";
                } else {
                    $dopSql = ", powodID = ".$edPowods;
                }
            } else {
                if(!empty($time)){//if($maxDate!="" && $makerDate=="" && $redAlert==1)
                    //echo $maxDate." | ".$makerDate." | ".$redAlert;break;
                    if($maxDate!=""){
                        $date = DateTime::createFromFormat('Y-m-d H:i:s', $maxDate);
                    } else {
                        $time = explode(":", $time);
                        $date->setTime($time[0]*1, $time[1]*1);
                    }
                    if($curentDate > $date && $makerDate=="" && $redAlert==1){
                        $form = new TModalForm();
                        $form->addContent("<div id=\"RollbackDivAction\"></div>");
                        $form->setWidth("450px");
                        $form->setCaption("Spóźnione zadania");
                        $form->setHelpMessage("Powód spóźnienia, min 5 max 300 znaków.");

                        $form->addContent(htmlDropDownList("edPowods", "width:200px", "", array_values($powods), array_keys($powods)));
                        $form->addContent(htmlTextArea("edReason", "width:400px;height:100px;display:none;", "") );

                        $parms = "";
                        $parms .= "'taskInstance=$taskInstance' + ";
                        $parms .= "'&instanceId=$instanceId' + ";
                        $parms .= "'&type=maker' +  ";
                        $parms .= "'&view=$view' + ";
                        $parms .= "'&edPowods='+ $escFun($('#edPowods').val()) + ";
                        $parms .= "'&edReason=' + $escFun($('#edReason').val()) ";

                        $btn = htmlAjaxLinkButton("btSave", "", "Zapisz", "pageControlId-body", "checklist.task",$parms );
                        $form->addContent( "<p align=\"center\">" );
                        $form->addContent( $btn );
                        $form->addContent( "</p>" );
                        $form->addContent(htmlJavaScript("$('#htmlFormModalDialog').find('.htmlFormHelpMessage').hide(); $('#edPowods').change(function(){if($(this).val() == '5'){ $('#edReason').show();$('#htmlFormModalDialog').find('.htmlFormHelpMessage').show();}else{ $('#edReason').val(''); $('#edReason').hide();$('#htmlFormModalDialog').find('.htmlFormHelpMessage').hide();}});"));

                        echo $form->getHtmlData();
                        break;
                    }
                }
            }
            $sql = "update dbo.[processInstanceTask] set 
                        makerDate = getdate(),
                        makerUserId = $userId
                        ".$dopSql."
                    where
                        instanceTaskId = $taskInstance
                        and makerUserId is null
                ";
            
            $dbM->execute($sql);
            generateEvent(ET_MAKER_CLICK, $taskName, $instanceId, "ProcessInstanceTask");
            break;
        }
        
        if($type=="rollback")
        {
            
            $sql = "update dbo.[processInstanceTask] set 
                        makerDate = NULL,
                        makerUserId = NULL,
                        checkerDate = NULL,
                        checkerUserId = NULL
                    where
                        instanceTaskId = $taskInstance
                ";
            $dbM->execute($sql);
            generateEvent(ET_ROLLBACK_TASK, $taskName, $instanceId, "ProcessInstanceTask");
            break;
        }

        if($type=="checker")
        {
            if($dbM->getData(0,"checkerUserId")!="")
                    break;

            if($dbM->getData(0,"makerUserId")==$userId)
                    break;

            $sql = "update dbo.[processInstanceTask] set 
                        checkerDate = getdate(),
                        checkerUserId = $userId
                    where
                        instanceTaskId = $taskInstance
                        and checkerUserId is null
                ";
            $dbM->execute($sql);
            generateEvent(ET_CHECKER_CLICK, $taskName, $instanceId, "ProcessInstanceTask");
            break;
        }
        
        
        break;
    }

    $blankPage = cryptURLParam("printCheckList");
    echo "<a href=\"?blankPage=$blankPage&listInstance=$instanceId\" target=\"_blank\"><img src=\"img/printer.jpeg\" width=\"30\" border=\"0\"/></a>" ;
//    echo htmlEmptyArea(4);
    
    $recordId = cryptURLParam($instanceId);
    $page = cryptURLParam("printPDF");
    $printButton = "
        <a href=\"?page=$page&type=clData&record=$recordId\" target=\"_blank\">
            <img src=\"img/icon-pdf.gif\" width=\"30px\" border=\"0\" />
        </a>";
    $printButton .= htmlEmptyArea(5);
    echo $printButton;
    
    
    //pobranie statusu i typu listy
    $sql = "
            select * from 
                    dbo.processInstance i
                        join dbo.processDefinition d on d.definitionId = i.definitionId
            where
                    i.instanceId = $instanceId
        ";
    
    $dbM->execute($sql);
    $listStatus = $dbM->getData(0, "status");
    $periodType = $dbM->getData(0, "periodType");
    
    $sql = "select * from dbo.processInstanceTask where instanceId = $instanceId";
    $dbM->execute($sql);
    
    $rows = $dbM->numRows();
    if($rows==0)
    {
        echo htmlFormErrorMessage("Brak zadań na liście");
        return;
    }
    
    $showMaker = $dbM->getData(0,"showMaker") == "1" ? TRUE : FALSE;
    $showChecker = $dbM->getData(0,"showChecker") == "1" ? TRUE : FALSE;
    $makerName = $dbM->getData(0,"makerName");
    $checkerName = $dbM->getData(0,"checkerName");
    $showActionDates = $dbM->getData(0,"showActionDates") == "1" ? TRUE : FALSE;
    $showCutOff = $dbM->getData(0,"showCutOffTime") == "1" ? TRUE : FALSE;

   
    $extraSQL = "";
    if($view =="current")
    {
        $extraSQL = " and t.makerDate is null ";
    }
    
    $sql = "
            select
                    t.*,
                    m.userDesc 'MakerName',
                    c.userDesc 'CheckerName',
                    t.isGroupName,
                    t.powodDescription,
                    isnull(pl.nazwa,'') 'nazwa',
                    dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) as maxDate,
                    case
                     when dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) < getdate() then '1'
                     else 0 end
                    as 'redAlert',
                    pd.periodtype,
                    (select e2.addInfo from frmEvent e2 where e2.eventId in
                        (select max(e.eventId) from frmEvent e
                            where e.refKey = t.instanceTaskId 
                              and e.reftable = 'ProcessInstanceTask'
                              and e.eventTypeId = 707)) as ignoreInfo
            from
                    dbo.processInstanceTask t
                        join ProcessInstance pi on pi.instanceId = t.instanceId
                        join Processdefinition pd on pd.definitionid = pi.definitionid
                        left outer join frmSystemUser m on m.userId = t.makerUserId
                        left outer join frmSystemUser c on c.userId = t.checkerUserId
                        left outer join powodsList pl on t.powodID = pl.id
            where
                    t.instanceId = $instanceId
                    $extraSQL    
            order by
                    taskSequence
        ";
    //debug($sql);
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell( new TTableCell(getPageContent("checkList.lp", "Lp.")) );
    
    $taskCell = new TTableCell(getPageContent("checkList.task", "Zadanie"));
    $row->addCell( $taskCell );
    if($showCutOff)
        $row->addCell( new TTableCell(getPageContent("checkList.cuttime", "Cut-off [godzina]")) );

    if($showCutOff && $periodType != "day") 
        $row->addCell( new TTableCell(getPageContent("checkList.cutoffDate", "Cut-off [dzień]")) );
    
    if( $showMaker )
        $row->addCell( new TTableCell($makerName) );
    if( $showChecker )
        $row->addCell( new TTableCell($checkerName) );

    if(getCurrentUserType()=="manager")
        $row->addCell( new TTableCell("Manager") );

    $row->addCell( new TTableCell(getPageContent("checkList.ignoreTask", "Brak realizacji")) );
    
    $tbl->addRow($row);

    
    for($i=0;$i<$rows;$i++)
    {
        $instanceTaskId = $dbM->getData($i,"instanceTaskId");
        $makerNeeded = $dbM->getData($i,"makerNeeded");
        $checkerNeeded = $dbM->getData($i,"checkerNeeded");
        $isGroupName = $dbM->getData($i,"isGroupName");
        $maxDate = $dbM->getData($i,"maxDate");
        $makerDate = $dbM->getData($i,"makerDate");
        $ignoreInfo = $dbM->getData($i,"ignoreInfo");

        $row = new TTableRow();

        $row->addCell( new TTableCell($i+1) );
        
        
        $hint = "";
        $addInfo = $dbM->getData($i,"taskAddInfo");
        $responsibleArea = $dbM->getData($i,"responsibleArea");
        if($addInfo!="")
            $hint = "<br/><br/><i>$responsibleArea $addInfo</i>";
        
        $tName = $dbM->getData($i,"taskName");
        $redAlert = $dbM->getData($i,"redAlert");
        if($maxDate!="" && $makerDate=="" && $redAlert==1)
        {
            $tName = "<font color=\"#e00000\"><b>".$tName." ($maxDate)</b></font>";
        }
        else if ($periodType != "day")
            $tName = $tName." ($maxDate)";
        $taskCell = new TTableCell( $tName.$hint );
        if($isGroupName=="1")
            $row->setAsHeader();
        $row->addCell(  $taskCell );
        if($showCutOff)
            $row->addCell( new TTableCell( $dbM->getData($i,"cutOffTime") ) );
        if($showCutOff && $periodType != "day")
            $row->addCell( new TTableCell( $dbM->getData($i,"cutOffDate") ) );
        
        //----------------------------------------------------------------------
        if($showMaker)
        {
            if($makerNeeded=="1")
            {
                $makerName = $dbM->getData($i,"makerName");
                $makerDate = $dbM->getData($i,"makerDate");
                $makerType = $dbM->getData($i, "nazwa");
                $makerText = $dbM->getData($i, "powodDescription");
                

                if($makerDate!="")
                {
                    $status ="";
                    if ($makerName!="")     // data ustalona i wykonawca ustalony -> zadanie wykonane
                    {
                        $status .= "<img src=\"../../apps/CheckList_PL/img/icon_completed.png\"/>".htmlEmptyArea(5);
                        if($showActionDates===FALSE)
                            $status .= $makerName."<br> ".$makerType." - ".$makerText;
                        else
                            $status .= $makerDate." ".$makerName."<br> ".$makerType." - ".$makerText;
                    }
                    $row->addCell( new TTableCell("$status" ));
                }
                else
                {
                    $statusChanger = "";
                    if($listStatus!="1")
                        $statusChanger = "<a href=\"#\" onclick=\"completeTask($instanceTaskId,'maker')\">
                                        <img border=\"0\" src=\"../../apps/CheckList_PL/img/mark_completed.png\" title=\"Zadanie zrealizowane\" >
                                      </a>  ";
                    $row->addCell( new TTableCell("$statusChanger" ));
                }
            }
            else
                $row->addCell( new TTableCell("" ));
        }   

        //----------------------------------------------------------------------
        if( $showChecker )
        {
            if($checkerNeeded=="1")
            {
                $checkerName = $dbM->getData($i,"checkerName");
                $checkerDate = $dbM->getData($i,"checkerDate");

                if($checkerDate!="")
                {
                    $status ="";
                    $status .= "<img src=\"../../apps/CheckList_PL/img/icon_completed.png\"/>".htmlEmptyArea(5);
                    if($showActionDates===FALSE)
                        $status .= $checkerName;
                    else
                        $status .= $checkerDate." ".$checkerName;
                    $row->addCell( new TTableCell("$status" ));
                }
                else
                {
                    $statusChanger = "";
                    if($listStatus!="1")
                        $statusChanger = "<a href=\"#\" onclick=\"completeTask($instanceTaskId,'checker')\">
                                        <img border=\"0\" src=\"../../apps/CheckList_PL/img/mark_completed.png\"/>
                                      </a>  ";
                    $row->addCell( new TTableCell("$statusChanger" ));
                }
            }
            else
                $row->addCell( new TTableCell("" ));
        }
        
        if(getCurrentUserType()=="manager")
        {
            $statusChanger = "";
            if ($makerDate!="")
            {
                $statusChanger = "<a href=\"#\" onclick=\"rollBackTask($instanceTaskId,'rollback')\">
                                <img border=\"0\" src=\"../../apps/CheckList_PL/img/mark_rollback.png\" title=\"Wycofaj zadanie\" />
                              </a>  ";
                $row->addCell( new TTableCell("$statusChanger" ));
                
            }
            else
                $row->addCell( new TTableCell("" ));
        }

        $makerName = $dbM->getData($i,"makerName");
        $makerDate = $dbM->getData($i,"makerDate");
        $statusIgnore ="";

        if($makerDate=="")  // brak daty - zadanie nie wykonane - można go pominąć
            $statusIgnore = "<a href=\"#\" onclick=\"ignoreTask($instanceTaskId,'ignore')\">
                             <img border=\"0\" src=\"../../apps/CheckList_PL/img/mark_completed.png\" title=\"Pomiń zadanie\" />
                             </a>  ";
        else if($makerDate!="" && $makerName=="")  // data ustalona ale wykonawca nie ustalony -> zadanie pominięte
            $statusIgnore = $ignoreInfo;
            //$statusIgnore .= "<img src=\"../../apps/CheckList_PL/img/icon_completed.png\"/>".htmlEmptyArea(5) . $makerDate;
        
        $row->addCell( new TTableCell("$statusIgnore" ));
        
        
        //----------------------------------------------------------------------
       
        $tbl->addRow($row);
    }    
    echo $tbl->getHtmlData();
    
    
    $action = cryptURLParam("checklist.task");
    $rollbackAction = cryptURLParam("checklist.rollback");
    $ignoreAction = cryptURLParam("checklist.ignore");
    //var powods = ".  json_encode($powods,JSON_FORCE_OBJECT)."
    $script = htmlJavaScript("
        
        function completeTask(taskInstance, type)
        {
            ajaxLoadData('pageControlId-body','$action','taskInstance='+taskInstance + '&instanceId=$instanceId' + '&type='+type + '&view='+'$view');
        }

        function rollBackTask(taskInstance, type)
        {
            ajaxLoadData('RollbackDiv','$rollbackAction','taskInstance='+taskInstance + '&instanceId=$instanceId' + '&type='+type + '&view='+'$view');
        }

        function ignoreTask(taskInstance, type)
        {
            ajaxLoadData('IgnoreDiv','$ignoreAction','taskInstance='+taskInstance + '&instanceId=$instanceId' + '&type='+type + '&view='+'$view');
        }
    ");
    
    echo $script;

    
    
?>
