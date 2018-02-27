<?php

class TDashBoard {

    var $tpl = null;
    var $separatedTeam = array();

    function TDashBoard() {
        $this->tpl = new TTemplate();
        $this->tpl->loadFromFile(APP_ROOT_DIR . "templates\\dashboard.html");
    }

    function setSeparation($team) {
        $tmp = explode(";", $team);

        foreach ($tmp as $t) {
            $t = trim(strtoupper($t));
            if ($t == "")
                continue;
            $this->separatedTeam[] = $t;
        }
    }

    function loadData() {
        $db = new TSQLMachine();
        $db = getDatabaseObject();
        $tpl = new TTemplate();
        $tpl = $this->tpl;

        $db->execute("select  getdate() as currentDate");
        $currentDate = $db->getData(0, "currentDate");
        $tpl->setParam("LAST_REFRESH", $currentDate);


        $db->execute("exec dbo.dashBoardGetMainData");
        $rows = $db->numRows();

        $tbl = "<table id=\"uniTable\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "No" . "</td>";
        $tbl .= "<td>" . "Team" . "</td>";
        $tbl .= "<td>" . "Date" . "</td>";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Total" . "</td>";
        $tbl .= "<td>" . "Completed" . "</td>";
        $tbl .= "<td>" . "Delayed" . "</td>";
        $tbl .= "<td>" . "Completed With Delay" . "</td>";
        $tbl .= "<td>" . "Progress [%]" . "</td>";
        $tbl .= "</tr>";


        $delayedList = array();
        $delayedList[] = -33333333;
        for ($i = 0; $i < $rows; $i++) {

            $instanceId = $db->getData($i, "instanceId");
            $delayed = $db->getData($i, "delayed");
            $delayedwc = $db->getData($i, "delayedwc");
            $percentCompleted = $db->getData($i, "percentCompleted");
            $team = $db->getData($i, "responsibleteam");
            $team = strtoupper($team);
            //debug($team);
            //debug($this->separatedTeam);

            if (count($this->separatedTeam) > 0) {
//                debug("$team");
                if (!in_array($team, $this->separatedTeam))
                    continue;
            }

            $rowClass = "uniTableCell";
            if ($delayed > 0) {
                $rowClass = "uniTableCellAlert";
                $delayedList[] = $instanceId;
            }

            if ($percentCompleted == 100)
                $rowClass = "uniTableCellCompleted";

            $instanceName = $db->getData($i, "instancename");
            $instanceName = str_replace("[D]", "", $instanceName);
            $instanceName = trim($instanceName);

            $tbl .= "<tr  class=\"$rowClass\">";
            $tbl .= "<td>" . ($i + 1) . "</td>";
            $tbl .= "<td>" . validTableCell($team) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "businessdate")) . "</td>";
            $tbl .= "<td>" . validTableCell($instanceName) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "total")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "completed")) . "</td>";
            $tbl .= "<td>" . validTableCell($delayed) . "</td>";
            $tbl .= "<td>" . validTableCell($delayedwc) . "</td>";

            $percentCompleted = round($percentCompleted) . "%";
            $tbl .= "<td ><div class=\"tdProgress\" style=\"width:$percentCompleted\">" . validTableCell($percentCompleted) . "<div></td>";
            $tbl .= "</tr>";
        }

        $tbl .= "</table>";

        $tpl->setParam("PROCESS_LIST", $tbl);

        $instancesList = implode(",", $delayedList);

        $sql = "
                select
                        pd.responsibleteam,
                        pd.processname,
                        t.taskname,
                        t.cutofftime
                from
                        dbo.processInstanceTask t
                                join dbo.processInstance pi on pi.instanceId = t.instanceId
                                join dbo.processDefinition pd on pd.definitionId = pi.definitionId 
                                                --and pd.periodtype = 'day'
                where
                        t.instanceId in ( $instancesList)
                        and t.makerUserId is null 
                        and getdate() > convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime  
                        order by convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime
            ";
        $db->execute($sql);
        $rows = $db->numRows();

        $tbl = "<table class=\"uniTable\" id=\"uniTableSecond\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Task" . "</td>";
        $tbl .= "<td>" . "CutOff" . "</td>";
        $tbl .= "</tr>";

        for ($i = 0; $i < $rows; $i++) {

            $tbl .= "<tr  class=\"uniTableCellAlertTxt\">";
            $tbl .= "<td>" . validTableCell($db->getData($i, "processname")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "taskname")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "cutofftime")) . "</td>";
            $tbl .= "</tr>";
        }

        
        $tbl .= "</table>";

        if ($rows > 0)
            $tpl->setParam("DELAYED_TASKS", $tbl);
        else {
            $smile = "<div id=\"smile\"><img src=\"../../apps/CheckList_PL/img/smile.png\"/></div>";
            $tpl->setParam("DELAYED_TASKS", $smile);
        }
        
        ///start week
        
        $db->execute("exec dbo.dashBoardGetMainDataWeek");
        $rows = $db->numRows();
        
        $tbl = "<table class=\"uniTable\" id=\"weektable\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "No" . "</td>";
        $tbl .= "<td>" . "Team" . "</td>";
        $tbl .= "<td>" . "Date" . "</td>";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Total" . "</td>";
        $tbl .= "<td>" . "Completed" . "</td>";
        $tbl .= "<td>" . "Delayed" . "</td>";
        $tbl .= "<td>" . "Completed With Delay" . "</td>";
        $tbl .= "<td>" . "Progress [%]" . "</td>";
        $tbl .= "</tr>";


        $delayedList = array();
        $delayedList[] = -33333333;
        for ($i = 0; $i < $rows; $i++) {

            $instanceId = $db->getData($i, "instanceId");
            $delayed = $db->getData($i, "delayed");
            $delayedwc = $db->getData($i, "delayedwc");
            $percentCompleted = $db->getData($i, "percentCompleted");
            $team = $db->getData($i, "responsibleteam");
            $team = strtoupper($team);
            //debug($team);
            //debug($this->separatedTeam);

            if (count($this->separatedTeam) > 0) {
//                debug("$team");
                if (!in_array($team, $this->separatedTeam))
                    continue;
            }

            $rowClass = "uniTableCell";
            if ($delayed > 0) {
                $rowClass = "uniTableCellAlert";
                $delayedList[] = $instanceId;
            }

            if ($percentCompleted == 100)
                $rowClass = "uniTableCellCompleted";

            $instanceName = $db->getData($i, "instancename");
            $instanceName = str_replace("[T]", "", $instanceName);
            $instanceName = trim($instanceName);

            $tbl .= "<tr  class=\"$rowClass\">";
            $tbl .= "<td>" . ($i + 1) . "</td>";
            $tbl .= "<td>" . validTableCell($team) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "businessdate")) . "</td>";
            $tbl .= "<td>" . validTableCell($instanceName) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "total")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "completed")) . "</td>";
            $tbl .= "<td>" . validTableCell($delayed) . "</td>";
            $tbl .= "<td>" . validTableCell($delayedwc) . "</td>";

            $percentCompleted = round($percentCompleted) . "%";
            $tbl .= "<td ><div class=\"tdProgress\" style=\"width:$percentCompleted\">" . validTableCell($percentCompleted) . "<div></td>";
            $tbl .= "</tr>";
        }

        $tbl .= "</table>";

        $tpl->setParam("PROCESS_LIST_WEEK", $tbl);
        //$smile = "<div id=\"smile_week\"><img src=\"../../apps/CheckList_PL/img/0355623.jpg\"/></div>";
        $instancesList = implode(",", $delayedList);
        
        $tbl = "<table class=\"uniTable\" id=\"weektableSecond\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Task" . "</td>";
        $tbl .= "<td>" . "CutOff" . "</td>";
        $tbl .= "</tr>";
        
        $sql = "
                select
                        pd.responsibleteam,
                        pd.processname,
                        t.taskname,
                        t.cutofftime
                from
                        dbo.processInstanceTask t
                                join dbo.processInstance pi on pi.instanceId = t.instanceId
                                join dbo.processDefinition pd on pd.definitionId = pi.definitionId 
                                                --and pd.periodtype = 'day'
                where
                        t.instanceId in ( $instancesList)
                        and t.makerUserId is null 
                        and getdate()> dbo.maxBusinessDate('week', pi.businessDate, t.cutOffTime, t.cutOffDate)
                        --and getdate() > convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime  
                        order by convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime
            ";
        $db->execute($sql);
        $rows = $db->numRows();

        for ($i = 0; $i < $rows; $i++) {

            $tbl .= "<tr  class=\"uniTableCellAlertTxt\">";
            $processname = str_replace("[T]", "", $db->getData($i, "processname"));
            $tbl .= "<td>" . validTableCell("[T] ".$processname) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "taskname")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "cutofftime")) . "</td>";
            $tbl .= "</tr>";
        }
        $tbl .= "</table>";
        //$tpl->setParam("DELAYED_TASKS_WEEK", $tbl);//$smile
        if ($rows > 0)
            $tpl->setParam("DELAYED_TASKS_WEEK", $tbl);
        else {
            $smile = "<div id=\"smile_week\"><img src=\"../../apps/CheckList_PL/img/smile.png\"/></div>";
            $tpl->setParam("DELAYED_TASKS_WEEK", $smile);
        }

        ///start month
        $db->execute("exec dbo.dashBoardGetMainDataMonth");
        $rows = $db->numRows();
        
        $tbl = "<table class=\"uniTable\" id=\"monthtable\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "No" . "</td>";
        $tbl .= "<td>" . "Team" . "</td>";
        $tbl .= "<td>" . "Date" . "</td>";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Total" . "</td>";
        $tbl .= "<td>" . "Completed" . "</td>";
        $tbl .= "<td>" . "Delayed" . "</td>";
        $tbl .= "<td>" . "Completed With Delay" . "</td>";
        $tbl .= "<td>" . "Progress [%]" . "</td>";
        $tbl .= "</tr>";


        $delayedList = array();
        $delayedList[] = -33333333;
        for ($i = 0; $i < $rows; $i++) {

            $instanceId = $db->getData($i, "instanceId");
            $delayed = $db->getData($i, "delayed");
            $delayedwc = $db->getData($i, "delayedwc");
            $percentCompleted = $db->getData($i, "percentCompleted");
            $team = $db->getData($i, "responsibleteam");
            $team = strtoupper($team);

            if (count($this->separatedTeam) > 0) {
                if (!in_array($team, $this->separatedTeam))
                    continue;
            }

            $rowClass = "uniTableCell";
            if ($delayed > 0) {
                $rowClass = "uniTableCellAlert";
                $delayedList[] = $instanceId;
            }

            if ($percentCompleted == 100)
                $rowClass = "uniTableCellCompleted";

            $instanceName = $db->getData($i, "instancename");
            $instanceName = str_replace("[M]", "", $instanceName);
            $instanceName = trim($instanceName);

            $tbl .= "<tr  class=\"$rowClass\">";
            $tbl .= "<td>" . ($i + 1) . "</td>";
            $tbl .= "<td>" . validTableCell($team) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "businessdate")) . "</td>";
            $tbl .= "<td>" . validTableCell($instanceName) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "total")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "completed")) . "</td>";
            $tbl .= "<td>" . validTableCell($delayed) . "</td>";
            $tbl .= "<td>" . validTableCell($delayedwc) . "</td>";

            $percentCompleted = round($percentCompleted) . "%";
            $tbl .= "<td ><div class=\"tdProgress\" style=\"width:$percentCompleted\">" . validTableCell($percentCompleted) . "<div></td>";
            $tbl .= "</tr>";
        }

        $tbl .= "</table>";

        $tpl->setParam("PROCESS_LIST_MONTH", $tbl);
        //$smile = "<div id=\"smile_month\"><img src=\"../../apps/CheckList_PL/img/calendar-520x245.jpg\"/></div>";
        $instancesList = implode(",", $delayedList);

        $tbl = "<table class=\"uniTable\" id=\"monthtableSecond\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Task" . "</td>";
        $tbl .= "<td>" . "CutOff" . "</td>";
        $tbl .= "</tr>";
        
        $sql = "
                select
                        pd.responsibleteam,
                        pd.processname,
                        t.taskname,
                        t.cutofftime
                from
                        dbo.processInstanceTask t
                                join dbo.processInstance pi on pi.instanceId = t.instanceId
                                join dbo.processDefinition pd on pd.definitionId = pi.definitionId 
                                                --and pd.periodtype = 'day'
                where
                        t.instanceId in ( $instancesList)
                        and t.makerUserId is null 
                        --and getdate() > convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime  
                        and getdate()> dbo.maxBusinessDate('month', pi.businessDate, t.cutOffTime, t.cutOffDate)
                        order by convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime
            ";
        $db->execute($sql);
        $rows = $db->numRows();

        for ($i = 0; $i < $rows; $i++) {

            $tbl .= "<tr  class=\"uniTableCellAlertTxt\">";
            $processname = str_replace("[M]", "",$db->getData($i, "processname"));
            $tbl .= "<td>" . validTableCell("[M] ".$processname) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "taskname")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "cutofftime")) . "</td>";
            $tbl .= "</tr>";
        }
        $tbl .= "</table>";
        //$tpl->setParam("DELAYED_TASKS_MONTH", $tbl);//$smile
        if ($rows > 0)
            $tpl->setParam("DELAYED_TASKS_MONTH", $tbl);
        else {
            $smile = "<div id=\"smile_month\"><img src=\"../../apps/CheckList_PL/img/smile.png\"/></div>";
            $tpl->setParam("DELAYED_TASKS_MONTH", $smile);
        }
        
        $tpl->setParam("TEAM", getRequestValue("team"));
        $activePage = getRequestValue("act");
        if(empty($activePage))
            $activePage = 0;
        $tpl->setParam("ACT", $activePage);
    }
    
    function getUpdateData() {
        $db = new TSQLMachine();
        $db = getDatabaseObject();
       
        $result = array('result'=>'no','spozn'=>'no');
        
        $db->execute("exec dbo.dashBoardGetMainData");
        $rows = $db->numRows();

        $tbl = "<table id=\"uniTable\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "No" . "</td>";
        $tbl .= "<td>" . "Team" . "</td>";
        $tbl .= "<td>" . "Date" . "</td>";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Total" . "</td>";
        $tbl .= "<td>" . "Completed" . "</td>";
        $tbl .= "<td>" . "Delayed" . "</td>";
        $tbl .= "<td>" . "Completed With Delay" . "</td>";
        $tbl .= "<td>" . "Progress [%]" . "</td>";
        $tbl .= "</tr>";


        $delayedList = array();
        $delayedList[] = -33333333;
        for ($i = 0; $i < $rows; $i++) {

            $instanceId = $db->getData($i, "instanceId");
            $delayed = $db->getData($i, "delayed");
            $delayedwc = $db->getData($i, "delayedwc");
            $percentCompleted = $db->getData($i, "percentCompleted");
            $team = $db->getData($i, "responsibleteam");
            $team = strtoupper($team);
            //debug($team);
            //debug($this->separatedTeam);

            if (count($this->separatedTeam) > 0) {
//                debug("$team");
                if (!in_array($team, $this->separatedTeam))
                    continue;
            }

            $rowClass = "uniTableCell";
            if ($delayed > 0) {
                $rowClass = "uniTableCellAlert";
                $delayedList[] = $instanceId;
            }

            if ($percentCompleted == 100)
                $rowClass = "uniTableCellCompleted";

            $instanceName = $db->getData($i, "instancename");
            $instanceName = str_replace("[D]", "", $instanceName);
            $instanceName = trim($instanceName);

            $tbl .= "<tr  class=\"$rowClass\">";
            $tbl .= "<td>" . ($i + 1) . "</td>";
            $tbl .= "<td>" . validTableCell($team) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "businessdate")) . "</td>";
            $tbl .= "<td>" . validTableCell($instanceName) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "total")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "completed")) . "</td>";
            $tbl .= "<td>" . validTableCell($delayed) . "</td>";
            $tbl .= "<td>" . validTableCell($delayedwc) . "</td>";

            $percentCompleted = round($percentCompleted) . "%";
            $tbl .= "<td ><div class=\"tdProgress\" style=\"width:$percentCompleted\">" . validTableCell($percentCompleted) . "<div></td>";
            $tbl .= "</tr>";
        }

        $tbl .= "</table>";
        
        $result["maintable"] = $tbl;
        
        $instancesList = implode(",", $delayedList);

        $sql = "
                select
                        pd.responsibleteam,
                        pd.processname,
                        t.taskname,
                        t.cutofftime
                from
                        dbo.processInstanceTask t
                                join dbo.processInstance pi on pi.instanceId = t.instanceId
                                join dbo.processDefinition pd on pd.definitionId = pi.definitionId 
                                                --and pd.periodtype = 'day'
                where
                        t.instanceId in ( $instancesList)
                        and t.makerUserId is null 
                        and getdate() > convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime  
                        order by convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime
            ";
        $db->execute($sql);
        $rows = $db->numRows();

        $tbl = "<table class=\"uniTable\" id=\"uniTableSecond\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Task" . "</td>";
        $tbl .= "<td>" . "CutOff" . "</td>";
        $tbl .= "</tr>";

        for ($i = 0; $i < $rows; $i++) {

            $tbl .= "<tr  class=\"uniTableCellAlertTxt\">";
            $processname = str_replace("[D]", "", $db->getData($i, "processname"));
            $tbl .= "<td>" . validTableCell("[D] ".$processname) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "taskname")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "cutofftime")) . "</td>";
            $tbl .= "</tr>";
        }

        $alerttbl = $tbl;
        $tbl .= "</table>";

        if ($rows > 0){
            $result["secondtable"] = $tbl;
            $result["spozn"] = "yes";
        }
        else {
            $smile = "<div id=\"smile\"><img src=\"../../apps/CheckList_PL/img/smile.png\"/></div>";
            $result["secondtable"] = $smile;
        }
        
        // start week
        $db->execute("exec dbo.dashBoardGetMainDataWeek");
        $rows = $db->numRows();
        
        $tbl = "<table class=\"uniTable\" id=\"weektable\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "No" . "</td>";
        $tbl .= "<td>" . "Team" . "</td>";
        $tbl .= "<td>" . "Date" . "</td>";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Total" . "</td>";
        $tbl .= "<td>" . "Completed" . "</td>";
        $tbl .= "<td>" . "Delayed" . "</td>";
        $tbl .= "<td>" . "Completed With Delay" . "</td>";
        $tbl .= "<td>" . "Progress [%]" . "</td>";
        $tbl .= "</tr>";
        
        $delayedList = array();
        $delayedList[] = -33333333;
        for ($i = 0; $i < $rows; $i++) {
            $instanceId = $db->getData($i, "instanceId");
            $delayed = $db->getData($i, "delayed");
            $delayedwc = $db->getData($i, "delayedwc");
            $percentCompleted = $db->getData($i, "percentCompleted");
            $team = $db->getData($i, "responsibleteam");
            $team = strtoupper($team);
            if (count($this->separatedTeam) > 0) {
                if (!in_array($team, $this->separatedTeam))
                    continue;
            }
            $rowClass = "uniTableCell";
            if ($delayed > 0) {
                $rowClass = "uniTableCellAlert";
                $delayedList[] = $instanceId;
            }
            
            if ($percentCompleted == 100)
                $rowClass = "uniTableCellCompleted";

            $instanceName = $db->getData($i, "instancename");
            $instanceName = str_replace("[T]", "", $instanceName);
            $instanceName = trim($instanceName);

            $tbl .= "<tr  class=\"$rowClass\">";
            $tbl .= "<td>" . ($i + 1) . "</td>";
            $tbl .= "<td>" . validTableCell($team) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "businessdate")) . "</td>";
            $tbl .= "<td>" . validTableCell($instanceName) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "total")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "completed")) . "</td>";
            $tbl .= "<td>" . validTableCell($delayed) . "</td>";
            $tbl .= "<td>" . validTableCell($delayedwc) . "</td>";

            $percentCompleted = round($percentCompleted) . "%";
            $tbl .= "<td ><div class=\"tdProgress\" style=\"width:$percentCompleted\">" . validTableCell($percentCompleted) . "<div></td>";
            $tbl .= "</tr>";
        }
        $tbl .= "</table>";
        $result["weektable"] = $tbl;
        $instancesList = implode(",", $delayedList);

        $sql = "
                select
                        pd.responsibleteam,
                        pd.processname,
                        t.taskname,
                        t.cutofftime
                from
                        dbo.processInstanceTask t
                                join dbo.processInstance pi on pi.instanceId = t.instanceId
                                join dbo.processDefinition pd on pd.definitionId = pi.definitionId 
                                                --and pd.periodtype = 'day'
                where
                        t.instanceId in ( $instancesList)
                        and t.makerUserId is null 
                        and getdate()> dbo.maxBusinessDate('week', pi.businessDate, t.cutOffTime, t.cutOffDate)
                        --and getdate() > convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime  
                        order by convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime
            ";
        $db->execute($sql);
        $rows = $db->numRows();

        $tbl = "<table class=\"uniTable\" id=\"weektableSecond\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Task" . "</td>";
        $tbl .= "<td>" . "CutOff" . "</td>";
        $tbl .= "</tr>";
        
        for ($i = 0; $i < $rows; $i++) {

            $tbl .= "<tr  class=\"uniTableCellAlertTxt\">";
            $processname = str_replace("[T]", "", $db->getData($i, "processname"));
            $tbl .= "<td>" . validTableCell("[T] ".$processname) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "taskname")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "cutofftime")) . "</td>";
            $tbl .= "</tr>";
        }
        $tbl .= "</table>";
        //$result["weektableSecond"] = $tbl;
        if ($rows > 0)
            $result["weektableSecond"] = $tbl;
        else {
            $smile = "<div id=\"smile_week\"><img src=\"../../apps/CheckList_PL/img/smile.png\"/></div>";
            $result["weektableSecond"] = $smile;
        }
        
        if ($rows > 0){
            $result["spozn"] = "yes";
        }
        // end week
        // start month
        $db->execute("exec dbo.dashBoardGetMainDataMonth");
        $rows = $db->numRows();
        
        $tbl = "<table class=\"uniTable\" id=\"monthtable\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "No" . "</td>";
        $tbl .= "<td>" . "Team" . "</td>";
        $tbl .= "<td>" . "Date" . "</td>";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Total" . "</td>";
        $tbl .= "<td>" . "Completed" . "</td>";
        $tbl .= "<td>" . "Delayed" . "</td>";
        $tbl .= "<td>" . "Completed With Delay" . "</td>";
        $tbl .= "<td>" . "Progress [%]" . "</td>";
        $tbl .= "</tr>";
        
        $delayedList = array();
        $delayedList[] = -33333333;
        for ($i = 0; $i < $rows; $i++) {
            $instanceId = $db->getData($i, "instanceId");
            $delayed = $db->getData($i, "delayed");
            $delayedwc = $db->getData($i, "delayedwc");
            $percentCompleted = $db->getData($i, "percentCompleted");
            $team = $db->getData($i, "responsibleteam");
            $team = strtoupper($team);
            if (count($this->separatedTeam) > 0) {
                if (!in_array($team, $this->separatedTeam))
                    continue;
            }
            $rowClass = "uniTableCell";
            if ($delayed > 0) {
                $rowClass = "uniTableCellAlert";
                $delayedList[] = $instanceId;
            }

            if ($percentCompleted == 100)
                $rowClass = "uniTableCellCompleted";

            $instanceName = $db->getData($i, "instancename");
            $instanceName = str_replace("[M]", "", $instanceName);
            $instanceName = trim($instanceName);

            $tbl .= "<tr  class=\"$rowClass\">";
            $tbl .= "<td>" . ($i + 1) . "</td>";
            $tbl .= "<td>" . validTableCell($team) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "businessdate")) . "</td>";
            $tbl .= "<td>" . validTableCell($instanceName) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "total")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "completed")) . "</td>";
            $tbl .= "<td>" . validTableCell($delayed) . "</td>";
            $tbl .= "<td>" . validTableCell($delayedwc) . "</td>";

            $percentCompleted = round($percentCompleted) . "%";
            $tbl .= "<td ><div class=\"tdProgress\" style=\"width:$percentCompleted\">" . validTableCell($percentCompleted) . "<div></td>";
            $tbl .= "</tr>";
        }
        $tbl .= "</table>";
        $result['monthtable'] = $tbl;
        $instancesList = implode(",", $delayedList);

        $sql = "
                select
                        pd.responsibleteam,
                        pd.processname,
                        t.taskname,
                        t.cutofftime
                from
                        dbo.processInstanceTask t
                                join dbo.processInstance pi on pi.instanceId = t.instanceId
                                join dbo.processDefinition pd on pd.definitionId = pi.definitionId 
                                                --and pd.periodtype = 'day'
                where
                        t.instanceId in ( $instancesList)
                        and t.makerUserId is null 
                        --and getdate() > convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime  
                        and getdate()> dbo.maxBusinessDate('month', pi.businessDate, t.cutOffTime, t.cutOffDate)
                        order by convert(varchar(10),pi.businessdate,120) + ' '+t.cutOffTime
            ";
        $db->execute($sql);
        $rows = $db->numRows();
        
        $tbl = "<table class=\"uniTable\" id=\"monthtableSecond\"  width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
        $tbl .= "<tr  class=\"uniTableHdr\">";
        $tbl .= "<td>" . "Process" . "</td>";
        $tbl .= "<td>" . "Task" . "</td>";
        $tbl .= "<td>" . "CutOff" . "</td>";
        $tbl .= "</tr>";
        
        for ($i = 0; $i < $rows; $i++) {

            $tbl .= "<tr  class=\"uniTableCellAlertTxt\">";
            $processname = str_replace("[M]", "",$db->getData($i, "processname"));
            $tbl .= "<td>" . validTableCell("[M] ".$processname) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "taskname")) . "</td>";
            $tbl .= "<td>" . validTableCell($db->getData($i, "cutofftime")) . "</td>";
            $tbl .= "</tr>";
        }
        $tbl .= "</table>";
        
        //$result["monthtableSecond"] = $tbl;
        if ($rows > 0)
            $result["monthtableSecond"] = $tbl;
        else {
            $smile = "<div id=\"smile_month\"><img src=\"../../apps/CheckList_PL/img/smile.png\"/></div>";
            $result["monthtableSecond"] = $smile;
        }
        
        if ($rows > 0){
            $result["spozn"] = "yes";
        }
        if($result["spozn"] == "yes"){
            $result["alerttable"] = "";//$alerttbl;
        } else {
            $result["alerttable"] = "";
        }
        // end month
        
        $result["result"] = "yes";
        return $result;
    }

    function getHtmlData() {
        $this->loadData();
        return $this->tpl->getData();
    }

}
