<?
    verifyUserAccess("");

    require APP_ROOT_DIR."business\\clFunctions.php";

    $dbM = new TSQLMachine();
    $dbM = getDatabaseObject();

    $dataSource = getApplicationDataSource();
    $userId = getCurrentUserId();
    $applicationId = getCurrentApplicationId();
    $currentUser = getCurrentUserId();

    $type = getRequestValue("type");
    
    if($type=="")
        throw new Exception("Unknown request type");
    
    $recordId = getEncryptedRequestValue("record");
    $recordId = prepareSQLParam($recordId,"int");

    $list = $recordId;

    generateCLInstancePDF($list);
    
    exit;
    
?>

