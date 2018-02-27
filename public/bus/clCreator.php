<?php
    try
    {
        $currentTime = time();
        $dayNumber = date("w",$currentTime);
        $hourNumber = date("G",$currentTime);
        $minutsNumber = date("i",$currentTime);
        
        //tworzenie list od poniedzialku do piatku 0 6-tej rano
        
        if($dayNumber == 0 || $dayNumber == 6)
            return;

        if($hourNumber != 6)
            return;

        if($minutsNumber >20)
            return;
    
        $pageManager = new TPageManager();
        $pageManager->init();
        
        $dbM = new TSQLMachine();
        $dbM = getDatabaseObject();

        dbSystemLog("CheckList creator", "Utworzenie nowych list");        
        $dbM->execute("exec [dbo].[automaticDailyListCreator]");
        $dbM->execute("exec [dbo].[automaticWeeklyListCreator]");
        $dbM->execute("exec [dbo].[automaticMonthlyListCreator]");
        $dbM->execute("exec [dbo].[automaticQuerterlyListCreator]");

        dbSystemLog("CheckList creator", "Zamknięcie zakonczonych");        
        $dbM->execute("exec [dbo].[automaticListClose]");
        
        dbSystemLog("CheckList creator", "Koniec procesu");        
        
        $pageManager->close();
    }
    catch( Exception $e )
    {
        logAndShowApplicationException( $e );
    }
?>
