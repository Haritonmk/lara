<?
    verifyUserAccess("");

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    
    $escFun = getJSEscapeFunction();
    
//------------------------------------------------------------------------------
function getCurrentLists()
{
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();
    $dataSource = getApplicationDataSource();
    //ladowanie dep z bazy danych
    if(getSessionValue("lastDepartmentLoadedFromDB")=="")
    {
        $currentUser = getCurrentUserId();

        //aktualizacja dep z bazy Wnioski urlopowe - tylko dla operatora bez oznaczenia '--'
        $dbM->execute("
            update u set lastDepartment = d.departmentShortName
            from frmSystemUser u 
            join HRManager_PL..frmSystemUser m on m.useridn = u.useridn
            left outer join HRManager_PL..frmSystemUser n on n.userid = m.lastSelectedManagerId
            join departmentsList d on charindex(isnull(m.costCode, n.costCode), d.costCode) <> 0
            where u.userType = 'operator'
                and isnull(u.lastDepartment, 0) <> d.departmentShortName
                and u.userId = $currentUser
                ");
        
        $dbM->execute("select * from dbo.frmSystemUser where userId = $currentUser");
        $lastDep = $dbM->getData(0, "lastDepartment");
        setSessionValue("lastDepartment", "$lastDep");
        setSessionValue("lastDepartmentLoadedFromDB","1");
    }
    
    
    $setDep = getRequestValue("setDep");
    $setDep = prepareSQLParam($setDep);
    if($setDep!="")
    {
        setSessionValue("lastSelectedInstance","");
        if($setDep=="--")
        {
            setSessionValue("lastDepartment", "");
        }
        else
        {
            setSessionValue("lastDepartment", $setDep);
        }
        $currentUser = getCurrentUserId();
        $dbM->execute("update dbo.frmSystemUser set lastDepartment = '$setDep' where userId = $currentUser");
        
    }
    
    $lastDepartment = getSessionValue("lastDepartment");
    $lastDepartment = prepareSQLParam($lastDepartment);
    if($lastDepartment=="--")
        $lastDepartment = "";
    $depSql = "";
    if($lastDepartment!="")
        $depSql = " and  responsibleTeam = '$lastDepartment' ";
    
    $sql = "
                select 
                        i.instanceId,
                        d.processName,
                        i.createDate,
                        i.businessdate,
                        i.instanceName,
                        d.periodType,
                        case 
                                when i.businessdate = convert(varchar(10),getdate(),120) then 'current'
                                when i.businessdate < convert(varchar(10),getdate(),120) then 'old'
                                when i.businessdate > convert(varchar(10),getdate(),120) then 'future'
                        end as 'listStatus',
                        ( 
                                select 
                                        count(*) 
                                from  
                                        dbo.processInstanceTask t 
                                where 
                                        t.instanceId = i.instanceId
                                        and t.makerDate is null
                                        and isnull(t.isgroupname,0)=0
                        ) as 'openTasks',
                        ( 
                                select 
                                        count(*) 
                                from  
                                        dbo.processInstanceTask t 
                                where 
                                        t.instanceId = i.instanceId
                                        and t.makerDate is not null
                                        and isnull(t.isgroupname,0)=0
                        ) as 'closedTasks'
                from 
                        dbo.processInstance i
                                join dbo.processDefinition d on d.definitionId = i.definitionId
                                $depSql
                where
                        i.status = 0
                order by 
                        i.businessDate + case d.periodType
                                when 'day' then 1
                                when 'week' then 7
                                when 'month' then 30
                                when 'quarter' then 90
                                else 365 end,
                        d.periodType desc,
                        i.businessDate,
                        d.processName
            ";
    
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->setAsHeader();
    $row->addCell( new TTableCell( getPageContent("fmrCheckList.lp", "Lp.")  ) );
    $row->addCell( new TTableCell(getPageContent("fmrCheckList.name", "Nazwa")) );
    $row->addCell( new TTableCell(getPageContent("fmrCheckList.prcCompl", "%")) );
    $row->addCell( new TTableCell(getPageContent("fmrCheckList.date", "Date")) );
    $tbl->addRow($row);
    
    for($i=0;$i<$rows;$i++)
    {
        $listStatus = $dbM->getData($i, "listStatus");
        $listName = $dbM->getData($i, "processName");
        $instanceName = $dbM->getData($i, "instanceName");
        $instanceId = $dbM->getData($i, "instanceId");
        $listLink = htmlAjaxLink($instanceName, "SelectedListDetails", "checklist.showDetails", "'instanceId=$instanceId'");

        $closedTasks = $dbM->getData($i, "closedTasks");
        $openTasks = $dbM->getData($i, "openTasks");
        $periodType = $dbM->getData($i, "periodType");

        
        $style = "";
        if($listStatus=="current")
            $style="background-color:#D6FCBF;";
        if($listStatus=="future")
            $style="background-color:#F4F2F2;";
        if($listStatus=="old")
            $style="background-color:#FCD6D6;";
        
        if($periodType=="week" || $periodType == "month" || $periodType == "quarter")
            $style="background-color:rgb(215,235,255)";
        
        $row = new TTableRow();

        $cell = new TTableCell($i+1 );
        $cell->setStyle($style);
        $row->addCell( $cell );
        
        $cell = new TTableCell($listLink );
        $cell->setStyle($style);
        $row->addCell( $cell );

        if(($openTasks+$closedTasks) == 0)
            $info = "0.00%";
        else
        {
            $info = $closedTasks / ($openTasks+$closedTasks);
            $info = round(100* $info,0)."%" ;
        }
        $cell = new TTableCell($info );
        $cell->setStyle($style);
        $row->addCell( $cell );

        $cell = new TTableCell(substr($dbM->getData($i, "businessdate"), 0,10));
        $cell->setStyle($style);
        $row->addCell( $cell );
        $tbl->addRow($row);
    }
    
    $currLists = $tbl->getHtmlData();

    //lista wydzialow
    $currentUser = getCurrentUserId();
    $depSql = "";
    if (getCurrentUserType() == 'operator') // gdy operator ograniczamy listę departamentów
        $depSql = " join dbo.frmSystemUser u on u.userId = $currentUser
                     and u.lastDepartment = departmentsList.departmentShortName";
    $depList = "";
    $sql = "
            select 
                departmentShortName as 'idn', 
                departmentShortName + ' - ' +departmentFullName as 'desc' 
            from 
                dbo.departmentsList 
                $depSql
            order by 1";
    
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    if (getCurrentUserType() != 'operator') // gdy nie operator - nie ograniczamy listy departamentów
    {
        $idn = array("--");
        $desc = array("-- Wszystkie --");
    }
    for($i=0;$i<$rows;$i++)
    {
        $idn[] = $dbM->getData($i, "idn");
        $desc[] = $dbM->getData($i, "desc");
    }
    
    $list = htmlDropDownList("lstDepName", "width:200px", "$lastDepartment", $desc, $idn,
            "onchange=\"changeDepartment() \"");
    
    $tbl = new TTable();
    $tbl->setWidth("100%");

    $row = new TTableRow();
    $row->addCell( new TTableCell( getPageContent("fmrCheckList.depName", "Wydział:")  ) );
    $row->addCell( new TTableCell($list) );
    $tbl->addRow($row);
    $depList = $tbl->getHtmlData();
    $depList .= htmlEmptyArea(5);
    
    return $depList.$currLists;
}    
//------------------------------------------------------------------------------
function historicalListForm()
{
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();

    $currentUser = getCurrentUserId();
    $depSql = "";
    if (getCurrentUserType() == 'operator') // gdy operator ograniczamy listę departamentów
        $depSql = "join processDefinition pd on pd.definitionId = p.definitionId
		   join dbo.frmSystemUser u on u.userId = $currentUser
                    and u.lastDepartment = pd.responsibleTeam
                  ";
    $sql = "
            select distinct instanceName from processInstance p
            $depSql
            order by instanceName        
            ";
    $dbM->execute($sql);
    $rows = $dbM->numRows();
    $listValue = array();
    $listName = array();
    for($i=0;$i<$rows;$i++)
    {
        $listValue[] = "".$dbM->getData($i, "instanceName")."";
        $listName[] = $dbM->getData($i, "instanceName");
    }
    $templateList = htmlDropDownList("edTemplate", "width:200px", array(), $listName, $listValue);
    
    
    $form = new TForm();
    $form->setCaption(getPageContent("fmrCheckList.histCaption", "Listy historyczne"));
    
    $tbl = new TTable();
    $tbl->setWidth("100%");
    
    $row = new TTableRow();
        $row->addCell(new TTableCell(getPageContent("fmrCheckList.histListName", "Nazwa listy")));
        $row->addCell(new TTableCell($templateList));
    $tbl->addRow($row);

        $row = new TTableRow();
        $row->addCell(new TTableCell(getPageContent("fmrCheckList.dateFrom", "Data od:")));
        $row->addCell(new TTableCell(htmlDateSelector("edDateFrom", "", "") ));
    $tbl->addRow($row);

    $row = new TTableRow();
        $row->addCell(new TTableCell(getPageContent("fmrCheckList.dateTo", "Data do:")));
        $row->addCell(new TTableCell( htmlDateSelector("edDateTo", "", "") ));
    $tbl->addRow($row);

    $form->addContent($tbl->getHtmlData());

    $escFun = getJSEscapeFunction();
    $ajaxValues = "'edTemplate=' + $escFun($('#edTemplate').val()) + ";
    $ajaxValues .= "'&edDateFrom=' + $escFun($('#edDateFrom').val()) + ";
    $ajaxValues .= "'&edDateTo=' + $escFun($('#edDateTo').val())";
    $form->addContent("<p align=\"center\">");
    $form->addContent(htmlAjaxLinkButton("btSave", "", getPageContent("fmrCheckListAdder.showHistBtn", "Pokaż"), "HistListDiv", "checklist.showHistoricalLists", "$ajaxValues"));
    $form->addContent("</p>");
    
    $form->addContent("<div id=\"HistListDiv\"></div>");
    
    return htmlEmptyArea(10) .$form->getHtmlData();
}

//------------------------------------------------------------------------------

    $form = new TForm();
    $form->setCaption( getPageContent("fmrCheckList.caption", "Listy kontrolne") );


    $tbl = new TTable();
    $tbl->setWidth("100%");
    
    $row = new TTableRow();
    $row->setAsHeader();
    $cell = new TTableCell(getPageContent("fmrCheckList.currentList", "Listy bieżące"));
    $cell->setWidth("30%");
    $row->addCell($cell);
    $cell = new TTableCell(getPageContent("fmrCheckList.details", "Szczegóły wybranej listy kontrolnej"));
    $row->addCell($cell);
    $tbl->addRow($row);

    
    $newListAdder = htmlAjaxLinkButton("btAdd", "", getPageContent("fmrCheckList.add", "Dodaj"), "AddContainer", "checklist.addNew", "''");
    $newListAdder .= htmlEmptyArea("10");
    $newListAdder .= "<div id=\"AddContainer\"></div>";
    
    $row = new TTableRow();
    
    $leftInfo = $newListAdder.getCurrentLists();
    $leftInfo .= historicalListForm();
    $cell = new TTableCell( $leftInfo );
    $cell->setvAlign("top");
    $row->addCell($cell);
    $info = htmlFormInfoMessage(getPageContent("fmrCheckList.selectList", "Wybierz listę kontrolną"));
    $cell = new TTableCell("<div id=\"SelectedListDetails\">$info</div>");
    $cell->setvAlign("top");
    $row->addCell($cell);
    $tbl->addRow($row);
    

    
    $form->addContent($tbl->getHtmlData());
    
    
    echo $form->getHtmlData();
    
    $page = cryptURLParam("checklist");

    $extraParam = getExtraParamValue();
    if($extraParam!="")
    {
        clearExtraParamValue();
        $extraParam = prepareSQLParam($extraParam,"int");
        setSessionValue("lastSelectedInstance", $extraParam);
    }
    $lastInstance = getSessionValue("lastSelectedInstance");
    if($lastInstance!="")
    {
        $action = cryptURLParam("checklist.showDetails");
        $script = "
            ajaxLoadData('SelectedListDetails','$action','instanceId=$lastInstance');

        ";
        echo htmlJavaScript($script);
    }

    
?>


    <script type="text/javascript">
    <!-- 
    
    function changeDepartment( )
    {
        var value = $('#lstDepName').val();
        window.location.href="?page=<? echo $page ?>&setDep="+value;
    }
    
    //-->
    </script>
