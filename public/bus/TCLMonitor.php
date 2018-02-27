<?php

class TCLMonitor
{
    var $openListInstances = array();
    
    
    function TCLMonitor()
    {
        
    }
    
    function init()
    {
        $dbM = new TSQLMachine();
        $dbM = getDatabaseObject();
        
        dbSystemLog("CheckList control", "-------- START ----------");        
        dbSystemLog("CheckList control", "Pobranie list do kontroli");        
        
        $sql = "
            select 
                pi.*
            from 
                dbo.processInstance pi
                join processDefinition pd on pd.definitionId = pi.definitionId
            where
                status = 0
                and	businessDate <= getdate()
            order by
                pi.businessDate
        ";  
        
        $dbM->execute($sql);
        
        $rows = $dbM->numRows();
        for($i=0;$i<$rows;$i++)
        {
            $this->openListInstances[] = $dbM->getData($i, "instanceId");
        }
        
        return count($this->openListInstances);
    }

    function initNotify()
    {
        $dbM = new TSQLMachine();
        $dbM = getDatabaseObject();

        // listy dla których należy wysłać przypomnienie w tej chwili
        $sql = "
            select 
                distinct pi.instanceId
            from 
                dbo.processInstanceTask t
                join ProcessInstance pi on pi.instanceId = t.instanceId
                join Processdefinition pd on pd.definitionid = pi.definitionid
            where 
                pi.status = 0
		and pi.businessDate <= getdate()
                and isnull(t.notifyTime, isnull(cast(t.notifyDate as varchar), '')) <> ''
                and isnull(t.isGroupName,0) = 0
                and t.makerDate is null
                and dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.notifyTime, t.notifyDate) < getdate()
        ";  
        
        $dbM->execute($sql);
        
        $rows = $dbM->numRows();
        for($i=0;$i<$rows;$i++)
        {
            $this->openListInstances[] = $dbM->getData($i, "instanceId");
        }
        
        return count($this->openListInstances);
    }
    
    function finish()
    {
        dbSystemLog("CheckList control", "-------- STOP -----------");        
    }
    
    function escalation()
    {
        // eskalacja - że zadanie niewykonane i były 2 alerty
        $dbM = new TSQLMachine();
        $dbM = getDatabaseObject();

        for($i=0;$i<count($this->openListInstances);$i++)
        {
            $currentInstance = $this->openListInstances[$i];
            debug("Escalate checklist instance: $currentInstance");
            
            $sql = "select * from dbo.processInstance where instanceId = $currentInstance";
            $dbM->execute($sql);
            $businessDate = $dbM->getData(0,"businessDate");
            $businessDate = substr($businessDate, 0, 10);
            $instanceName = $dbM->getData(0,"instanceName");
            $definitionId = $dbM->getData(0,"definitionId");
            
            debug("$instanceName $businessDate");
            
            $sql = "
                    select 
                            dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) as maxDate,
                            t.* 
                    from 
                            dbo.processInstanceTask t
                                    join ProcessInstance pi on pi.instanceId = t.instanceId
                                    join Processdefinition pd on pd.definitionid = pi.definitionid
                    where 
                            t.instanceId = $currentInstance
                            and isnull(t.cutOffTime, isnull(cast(t.cutOffDate as varchar), '')) <> ''
                            and isnull(t.isGroupName,0) = 0
                            and t.makerDate is null
                            and dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) < getdate()
                            and t.alertscount>2
                    order by
                            dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate)
            ";
            
            $dbM->execute($sql);
            $rows = $dbM->numRows();
            if($rows==0)
                continue;

            $html = "";
            
            $delayedTasks = array();
            for($x=0;$x<$rows;$x++)
            {
                $delayedTasks[] = $dbM->getData($x,"instanceTaskId");
                $html .= "<tr> ";
                $html .= "    <td >".($x+1)."</td> ";
                $html .= "    <td >".$dbM->getData($x,"taskName")."</td> ";
                $html .= "    <td >".$dbM->getData($x,"maxDate")."</td> ";
                $html .= "</tr> ";
            }

            $tpl = new TTemplate();
            $tpl->loadFromFile(APP_ROOT_DIR."templates\\taskAlert.html");
            $tpl->setParam("LIST_LINK", APPLICATION_LINK."?extraParam=$currentInstance");
            $tpl->setParam("LIST_NAME", "$instanceName");
            $tpl->setParam("LIST_DATE", "$businessDate");
            $tpl->setParam("BODY", "$html");
            $sender = new TEmailSender();
            
            $sql = "
                select 
                    * 
                from 
                    dbo.processDefinitionAlerts 
                where 
                    processDefinitionId = $definitionId
                    and contactType = 'escalation'
                ";
            $dbM->execute($sql);
            $rows2 = $dbM->numRows();
            for($x=0;$x<$rows2;$x++)
            {
                $emailAddress = $dbM->getData($x,"emailAddress");
                if($emailAddress!="")
                {
                    debug("Alert to: $emailAddress");
                    $sender->addAddress("$emailAddress");                    
                }
            } 
            
            $sender->sendMessage("", "*** ESKALACJA *** $instanceName $businessDate - niewykonane zadania", $tpl->getData());
        }
        dbSystemLog("CheckList control", "Zakonczenie kontroli");        
    }
    
    
    function alert()
    {
        // alert - że zadanie niewykonane - aż zostanie wykonane
        $dbM = new TSQLMachine();
        $dbM = getDatabaseObject();

        for($i=0;$i<count($this->openListInstances);$i++)
        {
            $currentInstance = $this->openListInstances[$i];
            debug("Analyze checklist instance: $currentInstance");
            
            $sql = "select * from dbo.processInstance where instanceId = $currentInstance";
            $dbM->execute($sql);
            $businessDate = $dbM->getData(0,"businessDate");
            $businessDate = substr($businessDate, 0, 10);
            $instanceName = $dbM->getData(0,"instanceName");
            $definitionId = $dbM->getData(0,"definitionId");
            
            debug("$instanceName $businessDate");
            
            $sql = "
                    select 
                            dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) as maxDate,
                            t.* 
                    from 
                            dbo.processInstanceTask t
                                    join ProcessInstance pi on pi.instanceId = t.instanceId
                                    join Processdefinition pd on pd.definitionid = pi.definitionid
                    where 
                            t.instanceId = $currentInstance
                            and isnull(t.cutOffTime, isnull(cast(t.cutOffDate as varchar), '')) <> ''
                            and isnull(t.isGroupName,0) = 0
                            and t.makerDate is null
                            and dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) < getdate()
                    order by
                            dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate)
            ";
            
            $dbM->execute($sql);
            $rows = $dbM->numRows();
            if($rows==0)
                continue;

            $html = "";
            
            $delayedTasks = array();
            for($x=0;$x<$rows;$x++)
            {
                $delayedTasks[] = $dbM->getData($x,"instanceTaskId");
                $html .= "<tr> ";
                $html .= "    <td >".($x+1)."</td> ";
                $html .= "    <td >".$dbM->getData($x,"taskName")."</td> ";
                $html .= "    <td >".$dbM->getData($x,"maxDate")."</td> ";
                $html .= "</tr> ";
            }

            // teraz update licznika opoznien
            for($x=0;$x<count($delayedTasks);$x++)
            {
                $taskId = $delayedTasks[$x];
                $sql = "update dbo.processInstanceTask set alertsCount = isnull(alertsCount,0)+1
                    where instanceTaskId = $taskId";
                $dbM->execute($sql);
            }
            
            $tpl = new TTemplate();
            $tpl->loadFromFile(APP_ROOT_DIR."templates\\taskAlert.html");
            $tpl->setParam("LIST_LINK", APPLICATION_LINK."?extraParam=$currentInstance");
            $tpl->setParam("LIST_NAME", "$instanceName");
            $tpl->setParam("LIST_DATE", "$businessDate");
            $tpl->setParam("BODY", "$html");
            $sender = new TEmailSender();
            
            $sql = "
                select 
                    * 
                from 
                    dbo.processDefinitionAlerts 
                where 
                    processDefinitionId = $definitionId
                    and contactType = 'basic'
                ";
            $dbM->execute($sql);
            $rows2 = $dbM->numRows();
            for($x=0;$x<$rows2;$x++)
            {
                $emailAddress = $dbM->getData($x,"emailAddress");
                if($emailAddress!="")
                {
                    debug("Alert to: $emailAddress");
                    $sender->addAddress("$emailAddress");                    
                }
            } 
                              
            $sender->sendMessage("", "$instanceName $businessDate - niewykonane zadania", $tpl->getData());
        }
    }


    function notify()
    {
        // przypomnienie - tylko raz informacja wysyłana
        // logi zakomentowane bo będzie to uruchamiane co 2-3 minuty
        $dbM = new TSQLMachine();
        $dbM = getDatabaseObject();

        //dbSystemLog("CheckList control", "Rozpoczecie kontroli");        
        
        for($i=0;$i<count($this->openListInstances);$i++)
        {
            $currentInstance = $this->openListInstances[$i];
            //debug("Analyze checklist instance: $currentInstance");
            
            $sql = "select * from dbo.processInstance where instanceId = $currentInstance";
            $dbM->execute($sql);
            $businessDate = $dbM->getData(0,"businessDate");
            $businessDate = substr($businessDate, 0, 10);
            $instanceName = $dbM->getData(0,"instanceName");
            $definitionId = $dbM->getData(0,"definitionId");
            
            //debug("$instanceName $businessDate");
            
            $sql = "
                    select 
                            dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate) as maxDate,
                            t.* 
                    from 
                            dbo.processInstanceTask t
                                    join ProcessInstance pi on pi.instanceId = t.instanceId
                                    join Processdefinition pd on pd.definitionid = pi.definitionid
                    where 
                            t.instanceId = $currentInstance
                            and isnull(t.notifyTime, isnull(cast(t.notifyDate as varchar), '')) <> ''
                            and isnull(t.isGroupName,0) = 0
                            and t.makerDate is null
                            and dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.notifyTime, t.notifyDate) < getdate()
                    order by
                            dbo.maxBusinessDate(pd.periodtype, pi.businessDate, t.cutOffTime, t.cutOffDate)
            ";
            
            $dbM->execute($sql);
            $rows = $dbM->numRows();
            if($rows==0)
                continue;

            $html = "";
            
            $delayedTasks = array();
            for($x=0;$x<$rows;$x++)
            {
                $delayedTasks[] = $dbM->getData($x,"instanceTaskId");
                $html .= "<tr> ";
                $html .= "    <td >".($x+1)."</td> ";
                $html .= "    <td >".$dbM->getData($x,"taskName")."</td> ";
                $html .= "    <td >".$dbM->getData($x,"maxDate")."</td> ";
                $html .= "</tr> ";
            }

            // teraz update licznika alertów (czyli pola notify*)
            for($x=0;$x<count($delayedTasks);$x++)
            {
                $taskId = $delayedTasks[$x];
                $sql = "update dbo.processInstanceTask set notifyTime = null, notifyDate = null
                    where instanceTaskId = $taskId";
                $dbM->execute($sql);
            }
            
            $tpl = new TTemplate();
            $tpl->loadFromFile(APP_ROOT_DIR."templates\\taskAlert.html");
            $tpl->setParam("LIST_LINK", APPLICATION_LINK."?extraParam=$currentInstance");
            $tpl->setParam("LIST_NAME", "$instanceName");
            $tpl->setParam("LIST_DATE", "$businessDate");
            $tpl->setParam("BODY", "$html");
            $sender = new TEmailSender();
            
            $sql = "
                select 
                    * 
                from 
                    dbo.processDefinitionAlerts 
                where 
                    processDefinitionId = $definitionId
                    and contactType = 'basic'
                ";
            $dbM->execute($sql);
            $rows2 = $dbM->numRows();
            for($x=0;$x<$rows2;$x++)
            {
                $emailAddress = $dbM->getData($x,"emailAddress");
                if($emailAddress!="")
                {
                    debug("Alert to: $emailAddress");
                    $sender->addAddress("$emailAddress");                    
                }
            } 
                           
            $sender->sendMessage("", "$instanceName $businessDate - przypomnienie o zadaniach", $tpl->getData());
        }
    }
    
}


?>
