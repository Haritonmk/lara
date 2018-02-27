<?php

    verifyUserAccess("");
    
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    
    $taskId = getRequestValue("taskId");
    $type = getRequestValue("type");
    $value = getRequestValue("value");
    $value = encodeAjaxRequestData($value);

    $taskId = prepareSQLParam($taskId,"int");
    $value = prepareSQLParam($value);

    
    $dbM->execute("select * from dbo.processDefinitionTask where taskId = $taskId");
    $lastTaskName = $dbM->getData(0, "taskName");
    $lastTaskCritical = $dbM->getData(0, "criticalProcess");
    $lastCutOff = $dbM->getData(0, "cutOffTime");
    $lastCutOffDate = $dbM->getData(0, "cutOffDate");
    $lastNotifyTime = $dbM->getData(0, "NotifyTime");
    $lastNotifyDate = $dbM->getData(0, "NotifyDate");
    $definitionId = $dbM->getData(0, "definitionId");
    $lastTaskInfo = "Było: $lastTaskName [$lastTaskCritical] [$lastCutOff] [$lastCutOffDate] [$lastNotifyTime] [$lastNotifyDate] ";
    $lastTaskInfo = prepareSQLParam($lastTaskInfo);
    $newTaskInfo = "";
    
    if($type=="name")
    {
        if($value=="")
        {
            $script= "$('#edName$taskId').css('border-color','#FF0000')";
            echo htmlJavaScript($script);
            return;
        }
        
        $sql = "update dbo.processDefinitionTask set taskName = N'$value' where taskId = $taskId";
        $dbM->execute($sql);
        $newTaskInfo = " Ustawiono: $value";     
        $dbM->execute("
            INSERT INTO [dbo].[processDefinitionTaskHistory]
                       ([userId]
                       ,[changeDate]
                       ,[changeType]
                       ,[changeFullInfo]
                       ,[definitionId])
             VALUES
                       ($userId
                       ,getdate()
                       ,'Zmiana nazwy'
                       ,'$lastTaskInfo > $newTaskInfo'
                       ,$definitionId)
        ");

        
        $script= "$('#edName$taskId').css('border-color','#008000')";
        echo htmlJavaScript($script);
        return;
    }

    // criticalProcess
    if($type=="critical")
    {
        /*
        if($value=="")
        {
            $script= "$('#edCritical_$taskId').css('border-color','#FF0000')";
            echo htmlJavaScript($script);
            return;
        }
        */
        
        $sql = "update dbo.processDefinitionTask set criticalProcess = N'$value' where taskId = $taskId";
        $dbM->execute($sql);
        $newTaskInfo = " Ustawiono: $value";     
        $dbM->execute("
            INSERT INTO [dbo].[processDefinitionTaskHistory]
                       ([userId]
                       ,[changeDate]
                       ,[changeType]
                       ,[changeFullInfo]
                       ,[definitionId])
             VALUES
                       ($userId
                       ,getdate()
                       ,'Zmiana czy krytyczny'
                       ,'$lastTaskInfo > $newTaskInfo'
                       ,$definitionId)
        ");

        /*
        $script= "$('#edCritical_$taskId').css('border-color','#008000')";
        echo htmlJavaScript($script);
        */
        return;
    }
    
    // CUT OFF
    if($type=="time")
    {
        if($value=="")
        {
            $sql = "update dbo.processDefinitionTask set cutOffTime = null where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: []";     
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Cut-off [godzina]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");
            
            $script= "$('#edTime$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }

        if(strlen($value)==5 && stringContainAllowedChars($value, "0123456789:")  )
        {
            $sql = "update dbo.processDefinitionTask set cutOffTime = '$value' where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: $value";     
            $newTaskInfo = prepareSQLParam($newTaskInfo);
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Cut-off [godzina]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");
            
            $script= "$('#edTime$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }
        $script= "$('#edTime$taskId').css('border-color','#FF0000')";
        echo htmlJavaScript($script);
        return;
    }

    if($type=="date")
    {
        if($value=="")
        {
            $sql = "update dbo.processDefinitionTask set cutOffDate = null, cutOffDateKalendaz = 0 where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: [] ";     
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Cut-off [dzień]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");
            
            $script= "$('#edDate$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }

        if(strlen($value)>=1 && strlen($value)<=3 && stringContainAllowedChars($value,"-0123456789") && $value <= 99)
        {
            $value = prepareSQLParam($value,"int");
            $kalendarz = getRequestValue("kalendarz");
            $sql = "update dbo.processDefinitionTask set cutOffDate = '$value', cutOffDateKalendaz = ".($kalendarz=="on"?"1":"0")." where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: $value ";     
            $newTaskInfo = prepareSQLParam($newTaskInfo);
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Cut-off [dzień]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");
            
            $script= "$('#edDate$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }
        $script= "$('#edDate$taskId').css('border-color','#FF0000')";
        echo htmlJavaScript($script);
        return;
    }

    // PRZYPOMNIENIE
    if($type=="notifytime")
    {
        if($value=="")
        {
            $sql = "update dbo.processDefinitionTask set notifyTime = null where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: [] ";     
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Przypomnienie [godzina]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");

            $script= "$('#edNotifyTime$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }

        if(strlen($value)==5 && stringContainAllowedChars($value, "0123456789:")  )
        {
            $sql = "update dbo.processDefinitionTask set notifyTime = '$value' where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: $value";     
            $newTaskInfo = prepareSQLParam($newTaskInfo);
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Przypomnienie [godzina]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");
            
            $script= "$('#edNotifyTime$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }
        $script= "$('#edNotifyTime$taskId').css('border-color','#FF0000')";
        echo htmlJavaScript($script);
        return;
    }
    
    if($type=="notifydate")
    {
        if($value=="")
        {
            $sql = "update dbo.processDefinitionTask set notifyDate = null, notifyDateKalendaz = 0 where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: [] ";     
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Przypomnienie [dzień]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");
            
            $script= "$('#edNotifyDate$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }

        if(strlen($value)>=1 && strlen($value)<=3 && stringContainAllowedChars($value,"-0123456789") && $value <= 99)
        {
            $value = prepareSQLParam($value,"int");
            $kalendarz = getRequestValue("kalendarz");
            $sql = "update dbo.processDefinitionTask set notifyDate = '$value', notifyDateKalendaz = ".($kalendarz=="on"?"1":"0")." where taskId = $taskId";
            $dbM->execute($sql);
            $newTaskInfo = " Ustawiono: $value ";     
            $newTaskInfo = prepareSQLParam($newTaskInfo);
            $dbM->execute("
		INSERT INTO [dbo].[processDefinitionTaskHistory]
			   ([userId]
			   ,[changeDate]
			   ,[changeType]
			   ,[changeFullInfo]
			   ,[definitionId])
		 VALUES
			   ($userId
			   ,getdate()
			   ,'Zmiana Przypomnienie [dzień]'
                           ,'$lastTaskInfo > $newTaskInfo'
			   ,$definitionId)
            ");
            
            $script= "$('#edNotifyDate$taskId').css('border-color','#008000')";
            echo htmlJavaScript($script);
            return;
        }
        $script= "$('#edNotifyDate$taskId').css('border-color','#FF0000')";
        echo htmlJavaScript($script);
        return;
    }


    echo htmlFormErrorMessage("Task type not supported: $type");
?>
