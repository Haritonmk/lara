<?php
    verifyUserAccess("");
    $escFun = getJSEscapeFunction();
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();

    $definitionId = getEncryptedRequestValue("listId");
    $definitionId = prepareSQLParam($definitionId,"int");
    $definitionIdCrypted = cryptURLParam($definitionId);
    

    $oper = getRequestValue("oper");
    if($oper=="addTask")
    {
        $dbM->execute("exec dbo.addListTask $definitionId, $userId");
    }
    
    if($oper=="removeTask")
    {
        $taskId = getRequestValue("taskId");
        $taskId = prepareSQLParam($taskId,"int");
        $dbM->execute("exec dbo.removeListTask $definitionId, $taskId, $userId");
    }

    if($oper=="downTask")
    {
        $taskId = getRequestValue("taskId");
        $taskId = prepareSQLParam($taskId,"int");
        debug("exec dbo.changeTaskPosition $definitionId, $taskId, 'down'");
        $dbM->execute("exec dbo.changeTaskPosition $definitionId, $taskId, 'down', $userId");
    }

    if($oper=="upTask")
    {
        $taskId = getRequestValue("taskId");
        $taskId = prepareSQLParam($taskId,"int");
        $dbM->execute("exec dbo.changeTaskPosition $definitionId, $taskId, 'up', $userId");
    }
    
    
    echo "<div id=\"TaskChangeAction\"></div>";
    
    $tbl = new TTable();
    $tbl->setWidth("100%");
    
    $dbM->execute("SELECT periodType FROM processDefinition WHERE definitionId = ".$definitionId);
    $periodType = $dbM->getData(0, "periodType");

    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell(new TTableCell(getPageContent("formListTasks.lp","Lp.")  ));
    $row->addCell(new TTableCell(getPageContent("formListTasks.name","Nazwa")  ));
    $row->addCell(new TTableCell(getPageContent("formListTasks.critical","Krytyczny")  ));
    $row->addCell(new TTableCell(getPageContent("formListTasks.cutOff","Cut-off [godzina]")  ));
    if($periodType != 'day')
    $row->addCell(new TTableCell(getPageContent("formListTasks.cutOffDate","Cut-off [dzień]")  ));
    $row->addCell(new TTableCell(getPageContent("formListTasks.notify","Przypomnienie [godzina]")  ));
    if($periodType != 'day')
    $row->addCell(new TTableCell(getPageContent("formListTasks.notifyDate","Przypomnienie [dzień]")  ));

    $row->addCell(new TTableCell(getPageContent("formListTasks.options","Opcje")  ));
    $tbl->addRow($row);
    
    

    $sql = "
        select * from dbo.processDefinitionTask where definitionId = $definitionId
        order by taskSequence        
        ";
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    
    for($i=0;$i<$rows;$i++)
    {
        $taskId = $dbM->getData($i, "taskId");
        $taskName = $dbM->getData($i, "taskName");
        $taskCritical = $dbM->getData($i, "criticalProcess");
        $cutOffTime = $dbM->getData($i, "cutOffTime");
        $cutOffDate = $dbM->getData($i, "cutOffDate");
        $notifyTime = $dbM->getData($i, "notifyTime");
        $notifyDate = $dbM->getData($i, "notifyDate");
        $cutOffKalendaz = $dbM->getData($i, "cutOffDateKalendaz")*1;
        $notifyKalendaz = $dbM->getData($i, "notifyDateKalendaz")*1;
        
        $row = new TTableRow();
        $row->addCell(new TTableCell($i+1  ));

        $editName = htmlEdit("edName$taskId", "width:550px;", $taskName,"onchange=changeTaskData('name','$taskId');");
        $row->addCell(new TTableCell( $editName  ));

        $editName = htmlCheckBox( "edCritical_$taskId", "", "$taskCritical", "" , $taskCritical?true:false);
        $row->addCell(new TTableCell( $editName  ));
        
        $editName = htmlEdit("edTime$taskId", "width:50px;", $cutOffTime,"onchange=changeTaskData('time','$taskId'); data-mask='time'");
        $row->addCell(new TTableCell( $editName  ));

        if($periodType != 'day'){
            if($periodType == 'month'){
                $editName = htmlEdit("edDate$taskId", "width:50px;".($cutOffKalendaz?'display:none;':''), $cutOffDate,"onchange=changeTaskData('date','$taskId'); data-task='$taskId'");
                $check = htmlCheckBox( "edCurDate$taskId", "", "$taskId", "Kalendarz Date" , $cutOffKalendaz?true:false);
                $editNameSecond = htmlEdit("edCurDateKalen$taskId", "width:50px;".($cutOffKalendaz?'':'display:none;'), $cutOffDate,"data-kalendaz='day' data-task='$taskId' data-type='cutoff'");
                $cell = $editName.$editNameSecond." ".$check;
            } else {
                $cell = htmlEdit("edDate$taskId", "width:50px;", $cutOffDate,"onchange=changeTaskData('date','$taskId'); data-task='$taskId'");
            }
            $row->addCell(new TTableCell(  $cell ));
        }

        $editName = htmlEdit("edNotifyTime$taskId", "width:50px;", $notifyTime,"onchange=changeTaskData('notifytime','$taskId'); data-mask='time'");
        $row->addCell(new TTableCell( $editName  ));

        if($periodType != 'day'){
            if($periodType == 'month'){
                $editName = htmlEdit("edNotifyDate$taskId", "width:50px;".($notifyKalendaz?'display:none;':''), $notifyDate,"onchange=changeTaskData('notifydate','$taskId'); data-task='$taskId'");
                $check = htmlCheckBox( "edNotifyDateChek$taskId", "", "$taskId", "Kalendarz Date" , $notifyKalendaz?true:false);
                $editNameSecond = htmlEdit("edNotifyDateKalen$taskId", "width:50px;".($notifyKalendaz?'':'display:none;'), $notifyDate,"data-kalendaz='day' data-task='$taskId' data-type='notify'");
                $cell = $editName.$editNameSecond." ".$check;
            } else {
                $cell = htmlEdit("edNotifyDate$taskId", "width:50px;", $notifyDate,"onchange=changeTaskData('notifydate','$taskId'); data-task='$taskId'");
            }
            $row->addCell(new TTableCell( $cell  ));
        }

        $options = "";

        $info = getPageContent("formListTasks.btRemove","Usuń");
        $options .= htmlAjaxLink("[".$info."] ", "pcTaskConfig-body", "listConfig.tasks", 
                "'listId=$definitionIdCrypted&oper=removeTask&taskId=$taskId'");

        if($i>0)
        {
            $info = getPageContent("formListTasks.btUp","Do góry");
            $options .= "&nbsp; ".htmlAjaxLink("[".$info."] ", "pcTaskConfig-body", "listConfig.tasks", 
                    "'listId=$definitionIdCrypted&oper=upTask&taskId=$taskId'");
        }
        
        if($i!=($rows-1))
        {
            $info = getPageContent("formListTasks.btDown","W dół");
            $options .= "&nbsp; ".htmlAjaxLink("[".$info."] ", "pcTaskConfig-body", "listConfig.tasks", 
                    "'listId=$definitionIdCrypted&oper=downTask&taskId=$taskId'");
        }
        
        $row->addCell(new TTableCell("$options"  ));
        $tbl->addRow($row);
    }
    $row = new TTableRow();
    $row->addCell(new TTableCell($i+1  ));
    $info = getPageContent("formListTasks.NewTask","Nowe zadanie");
    $btn = htmlAjaxLink( $info, "pcTaskConfig-body", "listConfig.tasks", "'listId=$definitionIdCrypted&oper=addTask'");
    $row->addCell(new TTableCell( $btn  ));
    $row->addCell(new TTableCell( ""  ));
    $row->addCell(new TTableCell( ""  ));
    $row->addCell(new TTableCell( ""  ));
    $row->addCell(new TTableCell( ""  ));
//    $row->addCell(new TTableCell( ""  ));
    $tbl->addRow($row);
    
    
    echo $tbl->getHtmlData();
    
    
    // opis pól
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->addCell(new TTableCell(""));
    $row->addCell(new TTableCell(""));
    $tbl->addRow($row);

    $row = new TTableRow();    
    $row->setAsHeader();
    $row->addCell(new TTableCell(getPageContent("formListTasks.Field","Pole")  ));
    $row->addCell(new TTableCell(getPageContent("formListTasks.Description","Opis")  ));
    $tbl->addRow($row);

    $row = new TTableRow();    
    $row->addCell(new TTableCell(getPageContent("formListTasks.cutOff","Cut-off [godzina]")  ));
    $row->addCell(new TTableCell("oznacza godzinę graniczną realizacji zadania. Dotyczy list dziennych i list okresowych. Format HH:MM (H - godzina, M - minuta)."));
    $tbl->addRow($row);
    
    $row = new TTableRow();    
    $row->addCell(new TTableCell(getPageContent("formListTasks.cutOffDate","Cut-off [dzień]")  ));
    $row->addCell(new TTableCell("oznacza dzień graniczny realizacji zadania. Dotyczy list okresowych, nie dotyczy list dziennych. Format: D (D - dzień roboczy np. 1, 2, 3…)."));
    $tbl->addRow($row);

    $row = new TTableRow();    
    $row->addCell(new TTableCell(getPageContent("formListTasks.notify","Przypomnienie [godzina]")  ));
    $row->addCell(new TTableCell("oznacza godzinę, o której ma zostać przesłane przypomnienie o zbliżającym się terminie realizacji zadania. Dotyczy list dziennych i list okresowych. Format HH:MM (H - godzina, M - minuta)."));
    $tbl->addRow($row);

    $row = new TTableRow();    
    $row->addCell(new TTableCell(getPageContent("formListTasks.notifyDate","Przypomnienie [dzień]")  ));
    $row->addCell(new TTableCell("oznacza dzień, w którym ma zostać przesłane przypomnienie o zbliżającym się terminie realizacji zadania. Dotyczy list okresowych, nie dotyczy list dziennych. Format: D (D - dzień roboczy np. -1,0,1, 2, 3…)"));
    $tbl->addRow($row);
    
    echo $tbl->getHtmlData();
    
    
    $action = cryptURLParam("listConfig.updateTaskField");
?>
    <script type="text/javascript">
    <!-- 
    //(function(){
        $("[data-mask=time]").mask("99:99");
        
        $("[data-kalendaz=day]").datepicker( { 
            dateFormat: 'dd', 
            duration: 'fast',
            firstDay: 0,
            monthNames: ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec','Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'],
            dayNamesMin: ['Nie', 'Po','Wt', 'Sr', 'Cz', 'Pi', 'So']
        } );
        
    //});
    
    function changeTaskData(fieldType, taskId )
    {
        var newValue = '';
        var parms = '';
        if(fieldType=='name')
            newValue = <?php echo $escFun; ?>($('#edName'+taskId).val());

       if(fieldType=='critical')
            newValue = <?php echo $escFun; ?>($('#edCritical_'+taskId).val());

        if(fieldType=='time')
            newValue = <?php echo $escFun; ?>($('#edTime'+taskId).val());

        if(fieldType=='date'){
            newValue = <?php echo $escFun; ?>($('#edDate'+taskId).val());
            if($('#edCurDate'+taskId).length)
                if($('#edCurDate'+taskId).is(':checked')){
                    newValue += '&kalendarz=on';
                } else {
                    newValue += '&kalendarz=off';
                }
        }

        if(fieldType=='notifytime')
            newValue = <?php echo $escFun; ?>($('#edNotifyTime'+taskId).val());

        if(fieldType=='notifydate'){
            newValue = <?php echo $escFun; ?>($('#edNotifyDate'+taskId).val());
            if($('#edNotifyDateChek'+taskId).length)
                if($('#edNotifyDateChek'+taskId).is(':checked')){
                    newValue += '&kalendarz=on';
                } else {
                    newValue += '&kalendarz=off';
                }
        }

        parms = 'type=' + fieldType + '&taskId=' + taskId + '&value='+ newValue;
        ajaxHiddenLoadData('TaskChangeAction','<? echo $action; ?>',parms);
    }
    
    $('[type=checkbox]').click(function(){
        if($(this).is(':checked')){
            if($(this).attr('id') == ( 'edCurDate'+$(this).val())){
                $('#edCurDateKalen'+$(this).val()).show();
                $('#edDate'+$(this).val()).hide();
            } else if($(this).attr('id') == ( 'edNotifyDateChek'+$(this).val())){
                $('#edNotifyDateKalen'+$(this).val()).show();
                $('#edNotifyDate'+$(this).val()).hide();
            }
        } else {
            if($(this).attr('id') == ( 'edCurDate'+$(this).val())){
                $('#edCurDateKalen'+$(this).val()).hide();
                $('#edDate'+$(this).val()).show();
            } else if($(this).attr('id') == ( 'edNotifyDateChek'+$(this).val())){
                $('#edNotifyDateKalen'+$(this).val()).hide();
                $('#edNotifyDate'+$(this).val()).show();
            }
        }
        
            var id = $(this).attr('id').split('_');
            if(id[0] == 'edCritical'){
                if($(this).is(':checked')){
                    $(this).val('1');
                } else {
                    $(this).val('0');
                }
                changeTaskData('critical', id[1]);
            }
        
    });
    
    $("[data-kalendaz=day]").change(function(){
        if($(this).attr('data-type') == 'cutoff'){
            $('#edDate'+$(this).attr('data-task')).val($(this).val());
            changeTaskData('date', $(this).attr('data-task') );
        } else {
            $('#edNotifyDate'+$(this).attr('data-task')).val($(this).val());
            changeTaskData('notifydate', $(this).attr('data-task') );
        }
    });
    
    //-->
    </script>
