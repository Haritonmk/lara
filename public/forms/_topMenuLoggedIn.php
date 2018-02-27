<?
    verifyUserAccess("");

    $currentUserType = getCurrentUserType() ;

    $menu = new TMainMenu();
     
    if( $currentUserType == "admin" )
    {
        $menu->addMenuItem(getPageContent("menu.users"), "?page=". cryptURLParam("users"));
        $menu->addMenuItem(getPageContent("menu.cms"), "?page=". cryptURLParam("cms"));
        $item = $menu->addMenuItem(getPageContent("menu.config"), "#");
            $menu->addSubMenuItem($item, "Parametry ogólne aplikacji" ,"?page=". cryptURLParam("config"));
            $menu->addSubMenuItem($item, "Konfiguracja raportów" ,"?page=". cryptURLParam("configReports"));
            $menu->addSubMenuItem($item, "Konfiguracja powodów" ,"?page=". cryptURLParam("configOpoznionego"));
        $menu->addMenuItem(getPageContent("menu.reports"), "?page=". cryptURLParam("reports"));
    }
    if( $currentUserType == "manager" )
    {
        $menu->addMenuItem(getPageContent("menu.checkList","Check listy"), "?page=". cryptURLParam("checklist") );
        $item = $menu->addMenuItem(getPageContent("menu.clSettings","Ustawienia"), "#");
            $menu->addSubMenuItem($item, getPageContent("menu.clListTemplates","Szablony list") ,"?page=". cryptURLParam("listTemplates"));
        $menu->addMenuItem(getPageContent("menu.reports"), "?page=". cryptURLParam("reports"));
    }
    if( $currentUserType == "operator" )
    {
        $menu->addMenuItem(getPageContent("menu.checkList","Check listy"), "?page=". cryptURLParam("checklist") );
    }

    if(!isDomainIntegratedCurrentUser())
    {
        $menu->addMenuItem(getPageContent("menu.changePass"), "?page=". cryptURLParam("pass"));
    }
    $menu->addMenuItem(getPageContent("menu.logout"), "?page=". cryptURLParam("logout"));
    echo $menu->getHtmlData();
?>
