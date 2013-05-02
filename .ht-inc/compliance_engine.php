<?php

define("MAX_OVERLAP_RES", 1);
define("MAX_RES_TIME", 2);
define("MAX_CONC_RES", 3);
define("CHK_ACCESS", 4);
define("END", "finalend");
define("START", "start");
define("LOGID", "id");

function complianceEngine() {

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    //print "The option you selected was: Compliance Engine";
//            global $advisingCont;
    print "<H3>The Compliance Engine</H3>";
    $options = array(0 => "Default",
        MAX_OVERLAP_RES => "Check Maximum Overlapping Reservation",
        MAX_RES_TIME => "Check a User's Maximum Reservation time",
        MAX_CONC_RES => "Check Maximum Concurrent Reservations",
        CHK_ACCESS => "Check User accessibility & Resource mapping");
    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "Select one of these options:";
//    printSelectInput("theoption", $options);
    
    echo "<select name='theoption' onChange='resetContinutation()' id='theoption'>";
    foreach ($options as $id => $value) {
        print "        <option value='$id'>$value</option>";
    }
    echo "</select>";
    
    
    $advcont = addContinuationsEntry("advisingHomePage", $options);
    print "<INPUT type=hidden id=OPT_" . CHK_ACCESS . " value=\"$advcont\">";
    
    $origcont = addContinuationsEntry("renderPolicyForm", $options);
    print "<INPUT type=hidden id=original name=original value=\"$origcont\">\n";
    
    $cont = addContinuationsEntry("renderPolicyForm", $options);
    print "<INPUT type=hidden id=continuation name=continuation value=\"$cont\">\n";
    print "<INPUT type=submit value=Submit>\n";
    print "<br/><span id='advRedirectMsg' style='display:none;'>This policy is best verified through the Advising Module. You will be redirected there on submit.</span>";
    print "</FORM>\n";
}

function renderPolicyForm() {

    $policy_type = processInputVar("theoption", ARG_NUMERIC);
//print $theoption;

    if ($policy_type == MAX_OVERLAP_RES) {
        renderMaxOverlapResPolicy($policy_type);
    } elseif ($policy_type == MAX_RES_TIME) {
        renderMaxReservationTime($policy_type);
    } elseif ($policy_type == MAX_CONC_RES) {
        renderMaxConcurrentRes($policy_type);
    } else {
        print "Invalid Policy option selected";
    }
}

function renderMaxReservationTime($policy_type) {
    print "Maximum Reservation Time";

    $groups = getUserGroups();
    $grpList = array();
    foreach ($groups as $grp) {
        $grpList[$grp['id']] = $grp['name'];
    }
    
    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "<b>Enter User Name:</b> &nbsp;";
    printSelectInput("user_group", $grpList);
    print "<br/>";
//    print "<INPUT type=\"Text\" id=\"userGroup\" name=\"user_group\" value=\"\"> <br><br>\n";
    print "<b>Maximum Reservation Time:</b> &nbsp;";
    print "<INPUT type=\"Text\" id=\"maxResTime\" name=\"max_res_time\" value=\"5\"> ";
    print "<i>( In minutes )</i> <br><br>\n";

    $edate = '';
    $etime = '';
    //From
    print "<b>From:</b> &nbsp;";
    print "<div type=\"text\" dojoType=\"dijit.form.DateTextBox\" ";
    print "id=\"fromdate\" onChange=\"setCombinedDateTime('fromdate', 'fromtime', 'fromdatetime');\" ";
    print "style=\"width: 78px\" value=\"$edate\"></div>\n";
    print "<div type=\"text\" dojoType=\"dijit.form.TimeTextBox\" ";
    print "id=\"fromtime\" onChange=\"setCombinedDateTime('fromdate', 'fromtime', 'fromdatetime');\" ";
    print "style=\"width: 78px\" value=\"T$etime\"></div>\n";
    print "<small>(" . date('T') . ")</small>\n";
    print "<INPUT type=\"hidden\" name=\"fromdatetime\" id=\"fromdatetime\" value=\"$edate\">\n";

    //Till
    print "<b>Till:</b> &nbsp;";
    print "<div type=\"text\" dojoType=\"dijit.form.DateTextBox\" ";
    print "id=\"tilldate\" onChange=\"setCombinedDateTime('tilldate', 'tilltime', 'tilldatetime');\" ";
    print "style=\"width: 78px\" value=\"$edate\"></div>\n";
    print "<div type=\"text\" dojoType=\"dijit.form.TimeTextBox\" ";
    print "id=\"tilltime\" onChange=\"setCombinedDateTime('tilldate', 'tilltime', 'tilldatetime');\" ";
    print "style=\"width: 78px\" value=\"T$etime\"></div>\n";
    print "<small>(" . date('T') . ")</small>\n";
    print "<INPUT type=\"hidden\" name=\"tilldatetime\" id=\"tilldatetime\" value=\"$edate\">\n";

    $cont = addContinuationsEntry("complianceSub");
    print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
    print "<INPUT type=hidden name=policy_type value=\"$policy_type\">\n";
    print "<INPUT type=\"submit\" value=\"Check\"/>";
    print "</FORM>";
}

function renderMaxOverlapResPolicy($policy_type) {
    print "Maximum Overlapping Reservations";

    $groups = getUserGroups();
    $grpList = array();
    foreach ($groups as $grp) {
        $grpList[$grp['id']] = $grp['name'];
    }
    
    
    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "<b>User group:</b> &nbsp;";
    printSelectInput("user_group", $grpList);
    print "<br/>";
//    print "<INPUT type=\"Text\" id=\"userGroup\" name=\"user_group\" value=\"\"> <br><br>\n";
    print "<b>Maximum Overlapping Reservation:</b> &nbsp;";
    print "<INPUT type=\"Text\" id=\"maxOverlapRes\" name=\"max_overlap_res\" value=\"5\"> ";
    print "<i>( default 5 )</i> <br><br>\n";

    $edate = '';
    $etime = '';
    //From
    print "<b>From:</b> &nbsp;";
    print "<div type=\"text\" dojoType=\"dijit.form.DateTextBox\" ";
    print "id=\"fromdate\" onChange=\"setCombinedDateTime('fromdate', 'fromtime', 'fromdatetime');\" ";
    print "style=\"width: 78px\" value=\"$edate\"></div>\n";
    print "<div type=\"text\" dojoType=\"dijit.form.TimeTextBox\" ";
    print "id=\"fromtime\" onChange=\"setCombinedDateTime('fromdate', 'fromtime', 'fromdatetime');\" ";
    print "style=\"width: 78px\" value=\"T$etime\"></div>\n";
    print "<small>(" . date('T') . ")</small>\n";
    print "<INPUT type=\"hidden\" name=\"fromdatetime\" id=\"fromdatetime\" value=\"$edate\">\n";

    //Till
    print "<b>Till:</b> &nbsp;";
    print "<div type=\"text\" dojoType=\"dijit.form.DateTextBox\" ";
    print "id=\"tilldate\" onChange=\"setCombinedDateTime('tilldate', 'tilltime', 'tilldatetime');\" ";
    print "style=\"width: 78px\" value=\"$edate\"></div>\n";
    print "<div type=\"text\" dojoType=\"dijit.form.TimeTextBox\" ";
    print "id=\"tilltime\" onChange=\"setCombinedDateTime('tilldate', 'tilltime', 'tilldatetime');\" ";
    print "style=\"width: 78px\" value=\"T$etime\"></div>\n";
    print "<small>(" . date('T') . ")</small>\n";
    print "<INPUT type=\"hidden\" name=\"tilldatetime\" id=\"tilldatetime\" value=\"$edate\">\n";

    print "<noscript>";
    print _("(You must have javascript enabled to use the 'Until' option.)");
    print "<br></noscript>\n";

    $cont = addContinuationsEntry("complianceSub");
    print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
    print "<INPUT type=hidden name=policy_type value=\"$policy_type\">\n";
    print "<INPUT type=\"submit\" value=\"Check\"/>";
    print "</FORM>";
}

function renderMaxConcurrentRes($policy_type) {
    print "Maximum Overlapping Reservations";

    $images = getImages(1);
    $imgList = array();
    foreach ($images as $image) {
        $imgList[$image['id']] = $image['prettyname'] != '' ? $image['prettyname'] : $image['name'];
    }
    
    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "<b>Image Name:</b> &nbsp;";
    
    printSelectInput("image_id", $imgList);
    print "<br/>";
    print "<b>Maximum Concurrent Reservations:</b> &nbsp;";
    print "<INPUT type=\"Text\" id=\"maxConcRes\" name=\"max_conc_res\" value=\"5\"> ";
    print "<i>( default 5 )</i> <br><br>\n";

    $edate = '';
    $etime = '';
    //From
    print "<b>From:</b> &nbsp;";
    print "<div type=\"text\" dojoType=\"dijit.form.DateTextBox\" ";
    print "id=\"fromdate\" onChange=\"setCombinedDateTime('fromdate', 'fromtime', 'fromdatetime');\" ";
    print "style=\"width: 78px\" value=\"$edate\"></div>\n";
    print "<div type=\"text\" dojoType=\"dijit.form.TimeTextBox\" ";
    print "id=\"fromtime\" onChange=\"setCombinedDateTime('fromdate', 'fromtime', 'fromdatetime');\" ";
    print "style=\"width: 78px\" value=\"T$etime\"></div>\n";
    print "<small>(" . date('T') . ")</small>\n";
    print "<INPUT type=\"hidden\" name=\"fromdatetime\" id=\"fromdatetime\" value=\"$edate\">\n";

    //Till
    print "<b>Till:</b> &nbsp;";
    print "<div type=\"text\" dojoType=\"dijit.form.DateTextBox\" ";
    print "id=\"tilldate\" onChange=\"setCombinedDateTime('tilldate', 'tilltime', 'tilldatetime');\" ";
    print "style=\"width: 78px\" value=\"$edate\"></div>\n";
    print "<div type=\"text\" dojoType=\"dijit.form.TimeTextBox\" ";
    print "id=\"tilltime\" onChange=\"setCombinedDateTime('tilldate', 'tilltime', 'tilldatetime');\" ";
    print "style=\"width: 78px\" value=\"T$etime\"></div>\n";
    print "<small>(" . date('T') . ")</small>\n";
    print "<INPUT type=\"hidden\" name=\"tilldatetime\" id=\"tilldatetime\" value=\"$edate\">\n";

    print "<noscript>";
    print _("(You must have javascript enabled to use the 'Until' option.)");
    print "<br></noscript>\n";

    $cont = addContinuationsEntry("complianceSub");
    print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
    print "<INPUT type=hidden name=policy_type value=\"$policy_type\">\n";
    print "<INPUT type=\"submit\" value=\"Check\"/>";
    print "</FORM>";
}

function cmp_startend($a, $b) {
    if ($a['time'] == $b['time'] && $a['type'] != $b['type'])
        return $a['type'] == END ? -1 : +1;
    return $a['time'] > $b['time'];
}

function exceedsMaxResTime($logData, $maxResTime, &$returnLogIds = Array()) {
    $noViolation = false;
    foreach ($logData as $le) {
        if($maxResTime < (strtotime($le[END]) - strtotime($le[START])) / 60) {
            $noViolation = true;
            $returnLogIds[$le[LOGID]] = $le[LOGID];
        }
    }
    return $noViolation;
}

function exceedsOverlap($logData, $maxOverlap, &$returnLogIds = Array()) {
    $startEnd = Array();
    foreach ($logData as $le) {
        array_push($startEnd, array("time" => $le[START], 'type' => START, LOGID => $le[LOGID]));
        array_push($startEnd, array("time" => $le[END], 'type' => END, LOGID => $le[LOGID]));
    }
    usort($startEnd, "cmp_startend");
//            print_r($startEnd);
    echo '<br/>';
    $intervals = 0;

    foreach ($startEnd as $le) {
        if ($le['type'] == START) {
            $intervals++;
            $returnLogIds[$le[LOGID]] = $le[LOGID];
        } else {
            $intervals--;
            unset($returnLogIds[$le[LOGID]]);
        }
        if ($intervals > $maxOverlap)
            return true;
    }
    return false;
}

function complianceSub() {

    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    $policy_type = processInputVar("policy_type", ARG_NUMERIC);
    //print "my policy :" . $policy_type;

    switch ($policy_type) {
        case MAX_OVERLAP_RES:
            checkMaxOverlappingRes();
            break;
        case MAX_RES_TIME:
            checkMaxResTime();
            break;
        case MAX_CONC_RES:
            checkMaxConcReservations();
            break;
        default:
            print "Invalid Policy Type detected!! <br/>Please repeat compliance process";
            break;
    }
}

function checkMaxOverlappingRes() {
    $ugid = processInputVar("user_group", ARG_STRING);
    $maxOverlap = processInputVar("max_overlap_res", ARG_NUMERIC);

    $fromDateTime = processInputVar("fromdatetime", ARG_STRING);
    $tillDateTime = processInputVar("tilldatetime", ARG_STRING);

//    $parts = explode("@", $ug_aff);
//    $afflid = count($parts) > 1 ? getAffiliationID($parts[1]) : DEFAULT_AFFILID;
//    $ugid = getUserGroupID($parts[0], $afflid);
    $ug_all = getUserGroups();
    $ug = $ug_all[$ugid];

    if ($ug['overlapResCount'] != $maxOverlap) {
        echo "<h4>Maximum Overlap Reservation Count is currently set to " . $ug['overlapResCount'] . ".</h4><br/>";
        echo "The <a href='//localhost/index.php?mode=pengine'>Advising module</a> can guide you in setting it to $maxOverlap.<br/>";
        echo "The following section lists scenarios that would have been violations (if any exist), if Maximum Overlap Reservation Count was set to $maxOverlap. <br/>";
    }
    $users = getUserGroupMembers($ugid);
    

    $noViolation = true;
    foreach ($users as $userid => $username) {
        $user = getUserInfo($userid, 0, 1);
        $filters = array(START => $fromDateTime, END => $tillDateTime, 'userid' => $userid);
        $logData = getLogData($filters);
        $violatingLogEntries = Array();
        if (exceedsOverlap($logData, $maxOverlap, $violatingLogEntries)) {
            $noViolation = false;
            echo "<h4>Possible Violation for $username.<br/></h4>";
            printLogEntries($logData, $violatingLogEntries, $user['unityid']);
        }
    }
    if ($noViolation) {
        echo "<h4>No Violations found for Maximum Overlap Reservation Count as $maxOverlap</h4><br/>";
    }
}

function checkMaxResTime() {
    $ugid = processInputVar("user_group", ARG_STRING);
    $inp_res_time = processInputVar("max_res_time", ARG_NUMERIC);

    $fromDateTime = processInputVar("fromdatetime", ARG_STRING);
    $tillDateTime = processInputVar("tilldatetime", ARG_STRING);

//    $parts = explode("@", $ugname_aff);
//    $afflid = count($parts) > 1 ? getAffiliationID($parts[1]) : DEFAULT_AFFILID;
//    $ugid = getUserGroupID($parts[0], $afflid);
    $ug_all = getUserGroups();
    $ug = $ug_all[$ugid];

    if ($inp_res_time != $ug['totalmaxtime']) {
        echo "<h4>Maximum Reservation Time is currently set to " . $ug['totalmaxtime'] . " for " . $ug['name'] . "</h4><br/>";
        echo "The <a href='//localhost/index.php?mode=pengine'>Advising module</a> can guide you in setting it to $inp_res_time " . ($inp_res_time == 1 ? "minute" : "minutes") . ".<br/>";
        echo "The following section lists scenarios that would have been violations (if any exist), if Maximum Reservation Time was set to $inp_res_time " . ($inp_res_time == 1 ? "minute" : "minutes") . ". <br/>";
    }
    
    $users = getUserGroupMembers($ugid);

    $noViolation = true;
    foreach ($users as $userid => $username) {
        $user = getUserInfo($userid, 0, 1);
        $filters = array(START => $fromDateTime, END => $tillDateTime, 'userid' => $userid);
        $logData = getLogData($filters);
        $violatingLogEntries = Array();
        if(exceedsMaxResTime($logData, $inp_res_time, &$violatingLogEntries)) {  
            $noViolation = false;
            echo "<h4>Possible Violation for $username.</h4><br/>";
            printLogEntries($logData, $violatingLogEntries, $user['unityid']);
        }
        
    }
    
    if ($noViolation) {
        echo "<h4>No Violations found for Maximum Reservation Time as $inp_res_time " . ($inp_res_time == 1 ? "minute" : "minutes") . ". </h4><br/>";
    }
}

function checkMaxConcReservations() {
    $image_id = processInputVar("image_id", ARG_NUMERIC);
    $maxConcurrent = processInputVar("max_conc_res", ARG_NUMERIC);

    $fromDateTime = processInputVar("fromdatetime", ARG_STRING);
    $tillDateTime = processInputVar("tilldatetime", ARG_STRING);
//
//    $parts = explode("@", $ug_aff);
//    $afflid = count($parts) > 1 ? getAffiliationID($parts[1]) : DEFAULT_AFFILID;
//    $ugid = getUserGroupID($parts[0], $afflid);
//    $ug_all = getUserGroups();
//    $ug = $ug_all[$ugid];
    
    $img_w_id = getImages(1, $image_id);
    $img = $img_w_id[$image_id];
    
    if ($img['maxconcurrent'] != $maxConcurrent) {
        echo "<h4>Maximum Concurrent Reservation Count is currently set to " . ($img['maxconcurrent'] == NULL ? "NULL" : $img['maxconcurrent'] ). ".</h4><br/>";
        echo "The <a href='//localhost/index.php?mode=pengine'>Advising module</a> can guide you in setting it to $maxConcurrent.<br/>";
        echo "The following section lists scenarios that would have been violations (if any exist), if Maximum Concurrent Reservation Count was set to $maxConcurrent. <br/>";
    }
//    $users = getUserGroupMembers($ugid);
    

    $noViolation = true;
//    foreach ($users as $userid => $username) {
//        $user = getUserInfo($userid, 0, 1);
        $filters = array(START => $fromDateTime, END => $tillDateTime, 'imageid' => $image_id);
        $imagename = $img['prettyname'];
        $logData = getLogData($filters);
        $violatingLogEntries = Array();
        if (exceedsOverlap($logData, $maxConcurrent, $violatingLogEntries)) {
            $noViolation = false;
            echo "<h4>Possible Violation for $imagename.<br/></h4>";
            printLogEntries($logData, $violatingLogEntries, $imagename, "Image");
        }
//    }
    if ($noViolation) {
        echo "<h4>No Violations found for Maximum Concurrent Reservations as $maxConcurrent</h4><br/>";
    }
}

function printLogEntries($logData, $selectedKeys, $readable_id, $type = "User") {
    echo "<table border=1>";

    echo "<tr>";

    echo "<th>Log Id</th>";
    echo "<th>User Id</th>";
    echo "<th>$type Name</th>";
    echo "<th>Image Id</th>";
    echo "<th>Computer Id</th>";
    echo "<th>Management Node Id</th>";
    echo "<th>Host Computer Id</th>";
    echo "<th>IP Address</th>";
    echo "<th>Started</th>";
    echo "<th>Ended</th>";

    echo "</tr>";
    foreach ($selectedKeys as $lkey) {
        echo "<tr>";

        $logEntry = $logData[$lkey];

        echo "<td>" . $logEntry['id'] . "</td>";
        echo "<td>" . $logEntry['userid'] . "</td>";
        echo "<td>" . $readable_id . "</td>";
        echo "<td>" . $logEntry['imageid'] . "</td>";
        echo "<td>" . $logEntry['computerid'] . "</td>";
        echo "<td>" . $logEntry['managementnodeid'] . "</td>";
        echo "<td>" . $logEntry['hostcomputerid'] . "</td>";
        echo "<td>" . $logEntry['IPaddress'] . "</td>";
        echo "<td>" . $logEntry['start'] . "</td>";
        echo "<td>" . $logEntry['finalend'] . "</td>";

        echo "</tr>";
    }
    echo "</table>";
}

?>
