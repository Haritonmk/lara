<?php

function generateCLInstancePDF( $instanceId )
{
    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();

    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    $applicationId = getCurrentApplicationId();
    $currentUser = getCurrentUserId();

    $list = $instanceId;
    
    $sql = "select * from dbo.processInstanceTask where instanceId = $list";
    $dbM->execute($sql);
    $showMaker = $dbM->getData(0,"showMaker") == "1" ? TRUE : FALSE;
    $showChecker = $dbM->getData(0,"showChecker") == "1" ? TRUE : FALSE;
    $showSignOff = $dbM->getData(0,"showSignoff") == "1" ? TRUE : FALSE;
    $makerName = $dbM->getData(0,"makerName");
    $checkerName = $dbM->getData(0,"checkerName");
    $signoffName = $dbM->getData(0,"signoffName");
    $showActionDates = $dbM->getData(0,"showActionDates") == "1" ? TRUE : FALSE;
    
    $sql = "select * from [processInstance] where instanceId = $list";
    $dbM->execute($sql);
    $listName = $dbM->getData(0,"instanceName");
    $businessDate = $dbM->getData(0,"businessDate");
    
    
    $sql = "
            select
                    t.*,
                    dbo.ChangePolishChar(t.taskName) as taskName_2,
                    m.userDesc 'MakerName',
                    c.userDesc 'CheckerName',
                    s.userDesc 'SignOffName'
            from
                    dbo.processInstanceTask t
                        left outer join frmSystemUser m on m.userId = t.makerUserId
                        left outer join frmSystemUser c on c.userId = t.checkerUserId
                        left outer join frmSystemUser s on s.userId = t.signoffUserId
            where
                    t.instanceId = $list
            order by
                    taskSequence
        ";
    $dbM->execute($sql);
    
    $tplClHeader = new TTemplate();
    $tplClHeader->loadFromFile(APP_ROOT_DIR."templates\\print_CheckListPDF.html");

    $tplClHeader->setParam("PRINTED_BY", getCurrentUserName() );
    $tplClHeader->setParam("PRINTED_DATE", date("Y-m-d H:i:s",time() ));
    $tplClHeader->setParam("BUSINESS_DATE", $businessDate);
    $tplClHeader->setParam("LIST_NAME", $listName);

    
    
    $infoHeader = "";
    $infoHeader .="            <tr>";
    $infoHeader .="                <td align=\"left\"><b>No.</b></td>";
    $infoHeader .="                <td align=\"left\"><b>Task name</b></td>";
    if($showMaker===TRUE)
        $infoHeader .="                <td align=\"left\" width=\"170px\"><b>$makerName</b></td>";
    if($showChecker===TRUE)
        $infoHeader .="                <td align=\"left\"><b>$checkerName</b></td>";
    if($showSignOff===TRUE)
        $infoHeader .="                <td align=\"left\"><b>$signoffName</b></td>";
    $infoHeader .="            </tr>";
    
    
    
    $infoBody = "";
    $rows = $dbM->numRows();
    for($i=0;$i<$rows;$i++)
    {
        $taskName = $dbM->getData($i,"taskName_2");  // polskie litery pozbawione ogonków
        if($showActionDates===TRUE)
        {
            $infoBody .= "<tr>";
            $infoBody .= "  <td >".($i+1)."</td>";
            $infoBody .= "  <td align=\"left\" width=\"430px;\">".$taskName."</td>";
            if($showMaker===TRUE)
                $infoBody .= "  <td align=\"left\" >".$dbM->getData($i,"MakerName")."<br/>".$dbM->getData($i,"makerDate")."</td>";
            if($showChecker===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"CheckerName")."<br/>".$dbM->getData($i,"checkerDate")."</td>";
            if($showSignOff===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"SignOffName")."<br/>".$dbM->getData($i,"signOffDate")."</td>";
            $infoBody .= "</tr>";
        }
        else
        {
            $infoBody .= "<tr>";
            $infoBody .= "  <td >".($i+1)."</td>";
            $infoBody .= "  <td align=\"left\">".$taskName."</td>";
            if($showMaker===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"MakerName")."</td>";
            if($showChecker===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"CheckerName")."</td>";
            if($showSignOff===TRUE)
                $infoBody .= "  <td align=\"left\">".$dbM->getData($i,"SignOffName")."</td>";
            $infoBody .= "</tr>";
        }
    }
    
    
    
    

    $pdfContent = "";
    ob_end_clean();
    ob_start();

    $templateHdr = new TTemplate();
    $templateHdr->loadFromFile( APP_ROOT_DIR."templates\\pdf_header.html" );
    

    $mainContent = "";
    $templateHdr->setParam("REPORT_NAME", "Check List Details");
    
    $templateFooter = new TTemplate();
    $templateFooter->loadFromFile( APP_ROOT_DIR."templates\\pdf_footer.html" );
    $templateFooter->setParam("GENERATED_BY", getCurrentUserName() );
    $templateFooter->setParam("GENERATION_DATE", date("Y-m-d H:i:s",time()) );

    
    
    
    
    
    
    
    
    
    
    
    
    $pdfContent = $templateHdr->getData();
    $pdfContent .= $tplClHeader->getData();
    $pdfContent .= "<br/>";
    $pdfContent .= "<br/>";
    $pdfContent .= "<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\">";
    $pdfContent .= $infoHeader;
    $pdfContent .= $infoBody;
    $pdfContent .= "</table>";
    $pdfContent .= $mainContent;
    $pdfContent .= $templateFooter->getData();


    $fileName = "CHECKLIST ".$listName;    
    $fileName = str_replace("[", "", $fileName);
    $fileName = str_replace("]", "", $fileName);
    $fileName .= " ". substr($businessDate,0,10);
    $fileName = str_replace("-", "", $fileName);
    $fileName .= ".pdf";

    $html2pdf = new HTML2PDF('P', 'A4', 'en');
    $html2pdf->setDefaultFont('Arial');
    $html2pdf->writeHTML($pdfContent);
    $html2pdf->Output($fileName);
}

?>
