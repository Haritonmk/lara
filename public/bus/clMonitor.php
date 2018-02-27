<?php
    try
    {
        require  APP_ROOT_DIR."business/TCLMonitor.php";

        //nie ma sensu spamowac 24/7, wiec ograniczamy sie do pon...piatek
        //od 8:00 do 20:00

        $currentTime = time();
        $dayNumber = date("w",$currentTime);
        $hourNumber = date("G",$currentTime);
        debug($hourNumber);
        
        if($dayNumber == 0 || $dayNumber == 6)
            return;

        if($hourNumber < 8 || $hourNumber > 19)
            return;
    
        $pageManager = new TPageManager();
        $pageManager->init();

        
        $monitor = new TCLMonitor();
        $monitor->init();
        //$monitor->notify();  // sprawdzenie co 2-3 minuty
        $monitor->alert();
        $monitor->escalation();
        $monitor->finish();
        
        $pageManager->close();
    }
    catch( Exception $e )
    {
        logAndShowApplicationException( $e );
    }
?>
