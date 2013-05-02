<?php
//global $option;
function pengineFunc() {
   global $cont;
   global $user;
   print "<H2>Policy Engine</H2><br>\n";
   if($user['unityid'] != "admin") {
     assessmentEngine();
     return;
   }
   if($user['unityid'] == "admin")
   	print "<H3>Select the policy engine module:</H3>\n";

   print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
   $cont = addContinuationsEntry('assessment');
   print "<INPUT type=radio name=continuation value=\"$cont\" checked ";
   print "id=\"assessment\"><label for=\"assessment\">";
   print "<font size=2>Assessment Engine</font></label><br><br>\n";

   if($user['unityid'] == "admin") {
	$cont = addContinuationsEntry('advising');
	print "<INPUT type=radio name=continuation value=\"$cont\"";
	print "id=\"advisor\"><label for=\"advisor\"><font size=2>Advising Engine</font>";
	print "</label><br><br>\n";

	$cont = addContinuationsEntry('compliance');
	print "<INPUT type=radio name=continuation value=\"$cont\"";
	print "id=\"compliance\"><label for=\"compliance\"><font size=2>Compliance Engine</font>";
	print "</label><br>\n";
   }
   print "<br><INPUT type=submit value=Submit>\n";
   print "</FORM>\n";
}

function assessmentEngine() {
    global $user;
    # get a count of images user can administer
    $tmp = getUserResources(array("imageAdmin"), array("administer"));
    $imgAdminCnt = count($tmp['image']);

    $tmp = getUserResources(array("computerAdmin"), array("administer"));
    $compAdminCnt = count($tmp['image']);

    $tmp = getUserResources(array("scheduleAdmin"), array("administer"));
    $schedAdminCnt = count($tmp['image']);

    print "<div align=center>\n";
    print "<H2>Assessment Module</H2>";
    if(checkUserHasPerm('User Lookup (global)')) {
        print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
        print "<table>";

        print "<TR><TD>";
        $cont = addContinuationsEntry('viewUserAssessment');
        print "<INPUT type=radio name=continuation value=\"$cont\" id=\"";
        print "user\"><label for=\"user\">View User Policies";
        print "</label>\n";
        print "</TD></TR>";

        if($imgAdminCnt) {
            print "<TR><TD>";
            $cont = addContinuationsEntry('viewImageAssessment');
            print "<INPUT type=radio name=continuation value=\"$cont\" id=\"";
            print "image\"><label for=\"image\">View Image Policies";
            print "</label><br>\n";
            print "</TD></TR>";
        }

        if($compAdminCnt) {
            print "<TR><TD>";
            $cont = addContinuationsEntry('viewCompAssessment');	
	     print "<INPUT type=radio name=continuation value=\"$cont\" id=\"";
            print "comp\"><label for=\"comp\">View Computer Policies";
            print "</label><br>\n";
            print "</TD></TR>";
        }

        if($schedAdminCnt) {
            print "<TR><TD>";
            $cont = addContinuationsEntry('viewSchedAssessment');
            print "<INPUT type=radio name=continuation value=\"$cont\" id=\"";
            print "sched\"><label for=\"sched\">View Schedule Policies";
            print "</label><br>\n";
            print "</TD></TR>";
        }

        print "  <TR>\n";
        print "    <TD colspan=3 align=center><INPUT type=submit value=Submit>\n";
        print "  </TR>\n";
        print "</table>";
        print "</FORM>\n";
    }
    else {
        print "<H2>Policies</H2>\n";
        printPolicies($user);
    }

    print "</div>";  	
}	

function viewUserAssessment() {
    global $user;
    print "<div align=center>\n";
    print "<H2>Assessment Module</H2>";

    $userid = processInputVar("userid", ARG_STRING);
    if(get_magic_quotes_gpc())
        $userid = stripslashes($userid);
    $affilid = processInputVar('affiliationid', ARG_NUMERIC, $user['affiliationid']);
    $resourceid = processInputVar("resourceid", ARG_STRING);
    $resourcetype = processInputVar("resourcetype", ARG_STRING);
    print "<H2>User Assessment</H2>\n";
    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "<TABLE>\n";
    print "  <TR>\n";
    print "    <TH style='padding: 7px 5px 0px 0px'>Name (last, first) or User ID:</TH>\n";
    print "    <TD><INPUT type=text name=userid value=\"$userid\" size=25></TD>\n";
    if(checkUserHasPerm('User Lookup (global)')) {
        $affils = getAffiliations();
        print "    <TD>\n";
        print "@";
        printSelectInput("affiliationid", $affils, $affilid);
        print "    </TD>\n";
    }
    print "  </TR>\n";
    print "  <TR>\n";
    print "    <TD colspan=3 align=center><INPUT type=submit value=Submit>\n";
    print "  </TR>\n";
    print "</TABLE>\n";
    $cont = addContinuationsEntry('submitUserAssessment');
    print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
    print "</FORM><br>\n";

    if (!empty($userid) and ! empty($resourceid)) {
        print "<font color=red>Error: cannot use both fields at same time</font><br>";
    }
    else if(! empty($userid)) {
        $esc_userid = mysql_real_escape_string($userid);
        if(preg_match('/,/', $userid)) {
            $mode = 'name';
	     $force = 0;
        }
        else
            $mode = 'userid';
        if(! checkUserHasPerm('User Lookup (global)') &&
           $user['affiliationid'] != $affilid) {
            print "<font color=red>$userid not found</font><br>\n";
            return;
        }
        if($mode == 'userid') {
            $query = "SELECT id "
                   . "FROM user "
                   . "WHERE unityid = '$esc_userid' AND "
                   .       "affiliationid = $affilid";
            $affilname = getAffiliationName($affilid);
            $userid = "$userid@$affilname";
            $esc_userid = "$esc_userid@$affilname";
        }
        else {
            $tmp = explode(',', $userid);
            $last = mysql_real_escape_string(trim($tmp[0]));
            $first = mysql_real_escape_string(trim($tmp[1]));
            $query = "SELECT CONCAT(u.unityid, '@', a.name) AS unityid "
                   . "FROM user u, "
                   .      "affiliation a "
                   . "WHERE u.firstname = '$first' AND "
                   .       "u.lastname = '$last' AND "
                   .       "u.affiliationid = $affilid AND "
                   .       "a.id = $affilid";
        }
        $qh = doQuery($query, 101);
        if(! mysql_num_rows($qh)) {
            print "<font color=red>User not found</font><br>\n";
            return;
        }
        elseif($mode == 'name') {
            $row = mysql_fetch_assoc($qh);
            $userid = $row['unityid'];
            $esc_userid = $row['unityid'];
        }
	$userdata = getUserInfo($esc_userid);
        if(is_null($userdata)) {
            $userdata = getUserInfo($esc_userid, 1);
            if(is_null($userdata)) {
                print "<font color=red>$userid not found in any known systems</font><br>\n";
                return;
            }
        }

        printPolicies($userdata);
    }
    print "</div>\n";
}

function viewImageAssessment() {
    global $user;
    $imageid = processInputVar("imageid", ARG_STRING);
    print "<div align=center>\n";
    print "<H2>Assessment Module</H2>";
    print "<H2>Image Assessment</H2>\n";

    $userResources = getUserResources(array("imageCheckOut"), array("available"), 0, 0, $user['id']);

    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "<TABLE>\n";
    print "  <TR>\n";
    print "    <TH style='padding: 7px 5px 0px 0px'>Image:</TH>\n";
    print "    <TD>\n";
    print "        <SELECT name='imageid'>";
    foreach ($userResources['image'] as $id => $image) {
        if($image == 'No Image')
            continue;

        if ($imageid == $id)
            print "<option value='$id' selected='selected'>$image</option>";
        else
            print "<option value='$id'>$image</option>";
    }
    print "        </SELECT>";
    print "    <TD colspan=3 align=center><INPUT type=submit value=Submit>\n";
    print "  </TR>\n";
    print "</TABLE>\n";
    $cont = addContinuationsEntry('submitImageAssessment');
    print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
    print "</FORM><br>\n";

    if (! empty($imageid)) {
        $query = "select * from image i where i.id=$imageid";
        $qh = doQuery($query,101);
        $row = mysql_fetch_assoc($qh);

        if ($row) {
            print "<h3>Image Information</h3>";
            print "<table>";
            print "<tr><th align=right>Minimum Ram:</th><td>{$row['minram']}</td></tr>";
            print "<tr><th align=right>Minimum Number Of Processors:</th><td>{$row['minprocnumber']}</td></tr>";
            print "<tr><th align=right>Minimum Processor Speed:</th><td>{$row['minprocspeed']}</td></tr>";
            print "<tr><th align=right>Minimum Network Speed:</th><td>{$row['minnetwork']}</td></tr>";
            if ($row['maxconcurrent'])
                print "<tr><th align=right>Max Concurrent Reservations:</th><td>{$row['maxconcurrent']}</td></tr>";
            print "<tr><th align=right>Available for Checkout:</th><td>";
            if ($row['forcheckout'])
                print "Yes";
            else
                print "No";
            print "</td></tr>";

            if ($row['imagemetaid'] > 0) {
                $query = "select * from imagemeta i where i.id={$row['imagemetaid']}";
                $qh = doQuery($query,101);
                $row = mysql_fetch_assoc($qh);

                if ($row) {
                    print "<tr><th align=right>Reservation Should Time Out:</th><td>";
                    if ($row['checkuser'])
                        print "Yes";
                    else
                        print "No";
                    print "</td></tr>";
                    print "<tr><th align=right>Root Access:</th><td>";
		    if ($row['rootaccess'])
                        print "Yes";
                    else
                        print "No";
                    print "</td></tr>";
                }
            }

            print "</table>\n";
        }

        print "<table>";
        $query = "select distinct i.id, ug.name
                  from resourcepriv rp,usergroup ug,resourcegroupmembers rm,image i,userpriv up,resource r
                  where i.id=r.subid and r.id=rm.resourceid and rp.privnodeid=up.privnodeid and up.usergroupid=ug.id and i.id=$imageid;";
        $qh = doQuery($query,101);

        print "<h3>Resource Mappings</h3>";
        print "<tr><th align=right>User Groups:</th>\n";
        print "<td>";
        $count = 0;
        while($row = mysql_fetch_assoc($qh)) {
            print "{$row['name']}</br>\n";
            $count++;
        }
        if (!$count)
            print "None";
        print "</td></tr>";

        $query = "select i.id, rg.name
                  from resourcegroupmembers rm,resourcegroup rg,image i,resource r,resourcemap rmp
                  where i.id=r.subid and r.id=rm.resourceid and rmp.resourcegroupid1=rm.resourcegroupid and rmp.resourcegroupid2=rg.id and i.id=$imageid;";
        $qh = doQuery($query,101);
        print "<tr><th align=right>Computer Groups:</th>";
        print "<td>";
        $count = 0;
        while ($row = mysql_fetch_assoc($qh)) {
            print "{$row['name']}</br>\n";
            $count++;
        }
        if (!$count)
            print "None";
        print "</td></tr>";
        print "</table>\n";
    }

    print "</div>";
}

function viewCompAssessment() {
    global $user;
    $compid = processInputVar("computerid", ARG_STRING);
    print "<div align=center>\n";
    print "<H2>Assessment Module</H2>";
    print "<H2>Computer Assessment</H2>\n";

    $userResources = getUserResources(array("imageCheckOut"), array("available"), 0, 0, $user['id']);

    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "<TABLE>\n";
    print "  <TR>\n";
    print "    <TH style='padding: 7px 5px 0px 0px'>Computer:</TH>\n";
    print "    <TD>\n";
    print "        <SELECT name='computerid'>";
    foreach ($userResources['computer'] as $id => $comp) {
        if ($compid == $id)
            print "<option value='$id' selected='selected'>$comp</option>";
        else
            print "<option value='$id'>$comp</option>";
    }
    print "        </SELECT>";
    print "    <TD colspan=3 align=center><INPUT type=submit value=Submit>\n";
    print "  </TR>\n";
    print "</TABLE>\n";
    $cont = addContinuationsEntry('submitCompAssessment');
    print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
    print "</FORM><br>\n";

    if (! empty($compid)) {
        print "<h3>Resource Mappings</h3>";
         print "<table>";

        print "<tr><th align=right>Image Groups:</th>\n";
        print "<td>";
        $query = "select c.id,c.hostname, rg.name
                 from computer c,resource r, resourcegroupmembers rm,resourcegroup rg, resourcemap rmp
                 where c.id=r.subid and r.id=rm.resourceid and rmp.resourcegroupid2=rm.resourcegroupid and rmp.resourcegroupid1=rg.id
                 and c.id=$compid and rmp.resourcetypeid2=12 and rmp.resourcetypeid1=13;";
        $qh = doQuery($query,101);
        $count = 0;
        while($row = mysql_fetch_assoc($qh)) {
            print "{$row['name']}</br>\n";
            $count++;
        }
        if (!$count)
            print "None";
        print "</td></tr>";

        print "<tr><th align=right>Schedule:</th>\n";
        print "<td>";
        $query = "select c.id,s.name from computer c,schedule s where c.id=$compid and c.scheduleid=s.id;";
        $qh = doQuery($query,101);
        $count = 0;
        while($row = mysql_fetch_assoc($qh)) {
            print "{$row['name']}</br>\n";
            $count++;
        }
        if (!$count)
            print "None";
        print "</td></tr>";
        print "</table>\n";

        $query = "select c.id,s.name,st.start,st.end
                  from computer c,schedule s,scheduletimes st
                  where c.id=$compid and c.scheduleid=s.id and st.scheduleid=s.id;";
        $qh = doQuery($query,101);
        $row = mysql_fetch_assoc($qh);

        if ($row) {
            $start = formatMinOfWeek($row['start']);
	    $end = formatMinOfWeek($row['end']);

            print "<h3>Availability</h3>";
            print "<table>";
            print "<tr><th align=right>Start time:</th>\n";
            print "<td>$start</td>\n";
            print "</td></tr>";
            print "<tr><th align=right>End time:</th>\n";
            print "<td>$end</td>\n";
            print "</td></tr>";
            print "</table>\n";
        }

    }

    print "</div>";
}

function viewSchedAssessment() {
    global $user;
    $schedid = processInputVar("scheduleid", ARG_STRING);
    print "<div align=center>\n";
    print "<H2>Assessment Module</H2>";
    print "<H2>Schedule Assessment</H2>\n";

    $userResources = getUserResources(array("imageCheckOut"), array("available"), 0, 0, $user['id']);

    print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
    print "<TABLE>\n";
    print "  <TR>\n";
    print "    <TH style='padding: 7px 5px 0px 0px'>Schedule:</TH>\n";
    print "    <TD>\n";
    print "        <SELECT name='scheduleid'>";
    foreach ($userResources['schedule'] as $id => $sched) {
        if ($schedid == $id)
            print "<option value='$id' selected='selected'>$sched</option>";
        else
            print "<option value='$id'>$sched</option>";
    }
    print "        </SELECT>";
     print "    <TD colspan=3 align=center><INPUT type=submit value=Submit>\n";
    print "  </TR>\n";
    print "</TABLE>\n";
    $cont = addContinuationsEntry('submitSchedAssessment');
    print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
    print "</FORM><br>\n";

    if (! empty($schedid)) {
        print "<h3>Resource Mappings</h3>";
        print "<table>";

        print "<tr><th align=right>Computers:</th>\n";
        print "<td>";
        $query = "select s.id,s.name,c.hostname from computer c, schedule s where s.id=$schedid and s.id=c.scheduleid;";
        $qh = doQuery($query,101);
        $count = 0;
        while($row = mysql_fetch_assoc($qh)) {
            print "{$row['hostname']}</br>\n";
            $count++;
        }
        if (!$count)
            print "None";

        print "</td></tr>";
        print "</table>";

        $query = "select s.name,st.start,st.end
                  from schedule s,scheduletimes st
                  where st.scheduleid=$schedid;";
        $qh = doQuery($query,101);
        $row = mysql_fetch_assoc($qh);

        if ($row) {
            $start = formatMinOfWeek($row['start']);
            $end = formatMinOfWeek($row['end']);

            print "<h3>Availability</h3>";
            print "<table>";
            print "<tr><th align=right>Start time:</th>\n";
            print "<td>$start</td>\n";
	     print "</td></tr>";
            print "<tr><th align=right>End time:</th>\n";
            print "<td>$end</td>\n";
            print "</td></tr>";
            print "</table>\n";
        }
    }

    print "</div>";
}

function printPolicies($userdata) {
    $userdata["groups"] = getUsersGroups($userdata["id"], 1, 1);
    print "<TABLE>\n";
    if(! empty($userdata['unityid'])) {
        print "  <TR>\n";
        print "    <TH align=right>User ID:</TH>\n";
        print "    <TD>{$userdata["unityid"]}</TD>\n";
        print "  </TR>\n";
    }

    $usergroups = getUsersGroups($userdata['id'],1,1);

    print "<TR>\n";
    print "<TH align=right>User Groups:</TH>\n";
    print "<TD>";
    foreach($usergroups as $id =>$group)
        print "$group</br>";
    print "</TD>";
    print " </TR>\n";

    $usertimes = getUserMaxTimes($userdata['id']);
    $initial = minToHourMin($usertimes['initial']);
    $total = minToHourMin($usertimes['total']);
    $extend = minToHourMin($usertimes['extend']);

    print "<TR>\n";
    print "<TH align=right>Initial Reservation Time:</TH>\n";
    print "<TD>$initial</TD>";
    print "</TR>\n";
    print "<TR>\n";
    print "<TH align=right>Total Reservation Time:</TH>\n";
    print "<TD>$total</TD>";
    print "</TR>\n";
    print "<TR>\n";
    print "<TH align=right>Extend Reservation Time:</TH>\n";
    print "<TD>$extend</TD>";
    print "</TR>\n";

    $ug = getUserGroups();
    $max = 0;
    foreach ($ug as $group) {
        $current = $group['overlapResCount'];
        if ($current > $max)
            $max = $group['overlapResCount'];
    }

    print "<TR>\n";
    print "<TH align=right>Max Concurrent Reservations:</TH>\n";
    print "<TD>$max</TD>";
    print "</TR>\n";
    print "</TABLE>\n";

    # get user's resources
    $userResources = getUserResources(array("imageCheckOut"), array("available"), 1, 0, $userdata['id']);
    $compMap = array(); # Maps computer group id to name
    $imgMap = array(); # Maps image group id to name
    $computerList = array(); # List of computers

    if (! empty($userResources['image'])) {
        $resources = getResourcesFromGroups($userResources['image'],"image",0);

        if (!empty($resources)) {
            print "<h3>Accessible Images By Group</h3>\n";
            print "<table>\n";
            foreach($userResources['image'] as $ig) {
                $iga = array($ig);
                $images = getResourcesFromGroups($iga,"image",0);

                if (! empty($images)) {
		      print "<tr>\n";
                    print "<th align='right'>$ig:</th>\n";

                    $imgMap[getResourceGroupId("image/$ig")] = $ig;

                    print "<td>\n";
                    foreach($images as $im)
                        print "$im<br>\n";
                    print "</td>\n";
                    print "</tr>\n";
                }
            }
            print "</table>\n";
        }
    }

    if (! empty($userResources['computer'])) {
        $resources = getResourcesFromGroups($userResources['computer'],"computer",0);

        if (!empty($resources)) {
            print "<h3>Accessible Computers By Group</h3>\n";
            print "<table>\n";
            foreach($userResources['computer'] as $cg) {
                $cga = array($cg);
                $computers = getResourcesFromGroups($cga,"computer",0);

                if (! empty($computers)) {
                    print "<tr>\n";
                    print "<th align='right'>$cg:</th>\n";

                    $compMap[getResourceGroupID("computer/$cg")] = $cg;

                    foreach($computers as $cid => $cname)
                        $computerList[$cid] = $cname;

                    print "<td>\n";
                    foreach($computers as $comp)
                        print "$comp<br>\n";
                    print "</td>\n";
                    print "</tr>\n";
		     }
            }
            print "</table>\n";
        }
    }

    if (! empty($userResources['schedule'])) {
        $resources = getResourcesFromGroups($userResources['schedule'],"schedule",0);

        if (!empty($resources)) {
            print "<h3>Accessible Schedules By Group</h3>\n";
            print "<table>\n";
            foreach($userResources['schedule'] as $sg) {
                $sga = array($sg);
                $schedules = getResourcesFromGroups($sga,"schedule",0);

                if (! empty($schedules)) {
                    print "<tr>\n";
                    print "<th align='right'>$sg:</th>\n";

                    print "<td>\n";
                    foreach($schedules as $sched)
                        print "$sched<br>\n";
                    print "</td>\n";
                    print "</tr>\n";
                }
            }
            print "</table>\n";
        }
    }

    if (! empty($userResources['managementnode'])) {
        $resources = getResourcesFromGroups($userResources['managementnode'],"managementnode",0);

        if (!empty($resources)) {
            print "<h3>Accessible Management Nodes By Group</h3>\n";
            print "<table>\n";
            foreach($userResources['managementnode'] as $mng) {
                $mnga = array($mng);
                $mnodes = getResourcesFromGroups($mnga,"managementnode",0);
                 if (! empty($mnodes)) {
                    print "<tr>\n";
                    print "<th align='right'>$mng:</th>\n";

                    print "<td>\n";
                    foreach($mnodes as $mn)
                        print "$mn<br>\n";
                    print "</td>\n";
                    print "</tr>\n";
                }
            }
            print "</table>\n";
        }
    }

    if (! empty($userResources['serverprofile'])){
        $resources = getResourcesFromGroups($userResources['serverprofile'],"serverprofile",0);

        if (!empty($resources)) {
            print "<h3>Accessible Server Profiles By Group</h3>\n";
            print "<table>\n";
            foreach($userResources['serverprofile'] as $spg) {
                $spga = array($spg);
                $sprofiles = getResourcesFromGroups($spga,"serverprofile",0);

                if (! empty($sprofiles)) {
                    print "<tr>\n";
                    print "<th align='right'>$spg:</th>\n";

                    print "<td>\n";
                    foreach($sprofiles as $sp)
                        print "$sp<br>\n";
                    print "</td>\n";
                    print "</tr>\n";
                }
            }
            print "</table>\n";
        }
     }

    $imgGroupSet = "'" . implode("','", array_keys($imgMap)) . "'";
    $compGroupSet = "'" . implode("','", array_keys($compMap)) . "'";
    $map = getResourceMapping("image","computer",$imgGroupSet,$compGroupSet);

    if (! empty($map)) {
        print "<h3>Image To Computer Mapping</h3>\n";
        print "<table border='1'>\n";
        print "<tr>\n";
        print "<td style='text-align:center'>Image</td>\n";
        print "<td style='text-align:center'>Computer</td>\n";
        print "</tr>\n";

        foreach ($map as $id => $compids) {
            print "<tr>\n";
            print "<td style='text-align:center'>$imgMap[$id]</td>\n";
            print "<td style='text-align:center'>\n";
            foreach ($compids as $cid)
                print "$compMap[$cid]</br>";

            print "</td>\n";
            print "</tr>\n";
        }
        print "</table>\n";
    }

    if (! empty($userResources['computer'])) {
        print "<h3>Computer To Schedule Mapping</h3>\n";
        print "<table border='1'>\n";
        print "<tr>\n";
        print "<td style='text-align:center'>Computer</td>\n";
        print "<td style='text-align:center'>Schedule</td>\n";
        print "</tr>\n";
        $computerIds = "'" . implode("','", array_keys($computerList)) . "'";
        $query = "SELECT c.id, s.name
               FROM schedule s, computer c
               WHERE c.scheduleid = s.id";
        $qh = doQuery($query,101);
         while($row = mysql_fetch_assoc($qh)) {
         if (array_key_exists($row['id'],$computerList)) {
             print "<tr>\n";
             print "<td style='text-align:center'>{$computerList[$row['id']]}</td>\n";
             print "<td style='text-align:center'>{$row['name']}</td>\n";
             print "</tr>\n";
         }   
     }
	print "</table>\n";
    }
}
     	       	 
function advisingEngine() {
	global $advisingCont;

	print "<H2>The Advising Engine</H2><br>";
	print "<H3>Check all the general policies that you want to get advise for:</H3>\n";
	$advisingCont = addContinuationsEntry("advisingHomePageWrapper");
	print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
        print "<TABLE>\n";
        print "<TR nowrap>\n";
        print "<TD><INPUT type=checkbox id=\"showUserPolicies\" name=\"showUserPolicies\"";
        print "value=\"0\"><label for=\"showUserPolicies\"><font size=2>User Policies</font></label></TD></TR>\n";
	print "<TR><TD></TD></TR>\n";
 	print "<TR nowrap>\n";
        print "<TD><INPUT type=checkbox id=\"showImagePolicies\" name=\"showImagePolicies\"";
	print "value=\"1\"><label for=\"showImagePolicies\"><font size=2>Image Policies</font></label></TD></TR>\n";
        print "<TR><TD></TD></TR>\n";
	print "<TR nowrap>\n";
	print "<TD><INPUT type=checkbox id=\"showResourceMapping\" name=\"showResourceMapping\"";
        print "value=\"2\"><label for=\"showResourceMapping\"><font size=2>Resource Mapping for a User</font></label></TD></TR>\n";
	print "</TABLE>\n";
	/*print "<font size=2><b>Select one of these policies:</b></font>";
	printSelectInput("theoption", $policies);
	$advisingCont = addContinuationsEntry("advisingHomePage", $policies);*/
	print "<br>";
	print "<INPUT type=hidden name=continuation value=\"$advisingCont\"><br>\n";
	print "<INPUT type=submit value=Submit>\n";
	print "</FORM>\n";
}

function advisingHomePageWrapperFunc() {

	global $advisingContWrapper;
	$policies = array(0 => "Edit Maximum Overlapping Reservation",
                          1 => "Image access to a User/Visiting Scholar",
                          2 => "Create an Image and grant access to a User",
                          3 => "Create a bunch of WinXP reservations for a Class",
                          4 => "Check User accessibility & Resource mapping",
                          5 => "Build a Composite Multi-Image Environment" );
	$policyMapping = array(	0 => array(0, 2, 3),
				1 => array(2, 3, 5),
				2 => array(1, 4));

	$displayPolicies = array();
        $j=0;
	print "<font size=2.5><b>The generic category of policies that you selected was:</b></font>\n";
	print "<ul>";
	if( isset( $_POST["showUserPolicies"] ) ) {
		print "<li><font size=2>User Policies</font></li><br>";
		foreach($policyMapping[0] as $index) {
                        if( !in_array( $policies[$index], $displayPolicies )) {
                                $displayPolicies[$j] = $policies[$index];
                                $j++;
                        }
                }

	}
	if( isset( $_POST["showImagePolicies"] ) ) {
		print "<li><font size=2>Image Policies</font></li><br>";
                foreach($policyMapping[1] as $index) {
                        if( !in_array( $policies[$index], $displayPolicies )) {
                                $displayPolicies[$j] = $policies[$index];
                                $j++;
                        }
                }

        }
	if( isset( $_POST["showResourceMapping"] ) ) {
		print "<li><font size=2>Resource Mapping for a User</font></li><br>";
                foreach($policyMapping[2] as $index) {
                        if( !in_array( $policies[$index], $displayPolicies )) {
                                $displayPolicies[$j] = $policies[$index];
                                $j++;
                        }
                }
        }
	print "</ul><br>";
	print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
	print "<font size=2><b>Select the \"specific policy\" pertaining to the above general policies:</b></font><br><br>";
        printSelectInput("theoption", $displayPolicies);
        $advisingContWrapper = addContinuationsEntry("advisingHomePage", $displayPolicies);
	print "<br><br>";
        print "<INPUT type=hidden name=continuation value=\"$advisingContWrapper\"><br>\n";
        print "<INPUT type=submit value=Submit>\n";
        print "</FORM>\n";
	

}


function advisingHomePageFunc() {
	$data = getContinuationVar();
	$theoption = processInputVar("theoption", ARG_NUMERIC);
	if(! array_key_exists($theoption, $data)) {
		print "invalid option submitted\n";
		return;
	}
	print "<H3><b>Chosen Policy: </b><i>&nbsp;{$data[$theoption]}</i></h3><br>\n";	
	$imgnames=array();
	$compnames=array();
	$usrgroups=array(); 
	$query = "select prettyname  from image;";
	$qh = doQuery($query,101);
	$i=0;
	while($row = mysql_fetch_assoc($qh)) {
		$imgnames[$i] = $row['prettyname'];
		$i = $i + 1;	
        }
	$query = "select hostname  from computer;";
        $qh = doQuery($query,101);
        $i=0;
        while($row = mysql_fetch_assoc($qh)) {
                $compnames[$i] = $row['hostname'];
                $i = $i + 1;
        }
	$query = "select name from usergroup;";
        $qh = doQuery($query,101);
        $i=0;
        while($row = mysql_fetch_assoc($qh)) {
                $usrgroups[$i] = $row['name'];
                $i = $i + 1;
        }
	if(strpos($data[$theoption], "Maximum Overlapping")) {
		print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
		$advisingCont = addContinuationsEntry("maxOverlapRes");
		print "<table>";
		print "<tr><th align=\"left\"><h3>Input Parameters:</h3></th></tr>";
		print "<tr><th align=\"left\"><b>Select one of these user groups:</b></th>";
                print "<th align=\"left\">";
                print "<select name=\"usrgroups\">";
                foreach($usrgroups as $ug) {
                	print "<option>$ug</option>";
                }
                print "</select></th></tr>";
		print "<tr><th align=\"left\"><b>Maximum Overlapping Reservation:</b></th>";
		print "<th><INPUT type=\"Text\" id=\"maxOverlapRes\" name=\"max_overlap\" value=\"\"></th>";
		print "<th align=\"left\"><i>( default 5 )</i> <br><br></th></tr>\n";
		print "<tr><th align=\"left\"><INPUT type=\"submit\" name=\"continuation\" align=\"center\" value=\"Advise\"/></th></tr></table>";
		print "<INPUT type=hidden name=continuation value=\"$advisingCont\">\n";
		print "</FORM>\n";
	}
	else if(strpos($data[$theoption], "Visiting Scholar")) {
		print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
		print "<table>";	
		print "<tr><th align=\"left\"><b>Select one of these images:</b></th>";
                print "<th align=\"left\">";
                print "<select name=\"images\">";
                foreach($imgnames as $in) {
                        if($in != "No Image")
                                print "<option>$in</option>";
                }
                print "</select></th></tr>";
		print "<br>\n";
		$advisingCont = addContinuationsEntry("visitingScholar");
		print "<tr><th align=\"left\"><INPUT type=hidden name=continuation value=\"$advisingCont\"></th></tr>\n";
		print "<br>";
		print "<tr><th align=\"left\"><INPUT type=submit value=Submit></th></tr>\n";
		print "</table>";
		print "</FORM>\n";
        }
	else if(strpos($data[$theoption], "bunch of WinXP")) {
		print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
                $advisingCont = addContinuationsEntry("blockWinxpRes");
                print "<table>";
                print "<tr><th align=\"left\"><h3>Input Parameters</h3></th>";
                print "<tr><th align=\"left\"><b>Name:</b></th>";
                print "<th><INPUT type=\"Text\" id=\"nameRes\" name=\"name\" value=\"\"> <br></th></tr>\n";
		print "<tr><th align=\"left\"><b>Owner:</b></th>";
                print "<th><INPUT type=\"Text\" id=\"ownerRes\" name=\"owner\" value=\"\"> <br></th></tr>\n";
                print "<tr><th align=\"left\"><b>Seats:</b></th>";
                print "<th><INPUT type=\"Text\" id=\"seatsRes\" name=\"seats\" value=\"\"></th>";
		print "<tr><th align=\"left\"><b>Select one of these user groups:</b></th>";
                print "<th align=\"left\">";
                print "<select name=\"usergroups\">";
                foreach($usrgroups as $ug) {
                        print "<option>$ug</option>";
                }
                print "</select></th></tr>";
                print "<tr><th align=\"left\"><INPUT type=\"submit\" name=\"continuation\" align=\"center\" value=\"Advise\"/></th></tr></table>";
                print "<INPUT type=hidden name=continuation value=\"$advisingCont\">\n";
                print "</FORM>\n";		 
	}
	else if(strpos($data[$theoption], "grant access")) {
                print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
                print "<table>\n";
	 	print "<tr><th align=\"left\"><b>Select one of these base images:</b></th>";
                print "<th align=\"left\">";
                print "<select name=\"images\">";
                foreach($imgnames as $in) {
                        if($in != "No Image")
                                print "<option>$in</option>";
                }
                print "</select></th></tr>";
                $advisingCont = addContinuationsEntry("grantImgAccess");
                print "<tr><th><INPUT type=hidden name=continuation value=\"$advisingCont\"></th></tr>\n";
                print "<br>";
                print "<tr><th align=\"left\"><INPUT type=submit value=Submit></th></tr>\n";
                print "</table></FORM>\n";
	}
	else if(strpos($data[$theoption], "User accessibility & Resource mapping")) {
                print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
                $advisingCont = addContinuationsEntry("accessAndMapping");
                print "<table>";
                print "<tr><th align=\"left\"><h3>Input Parameters<i> (<font size=0.8>All input parameters are mandatory</font>)</i></h3></th>";
                print "<tr><th align=\"left\"><b>User ID (unityid):</b></th>";
                print "<th align=\"left\"><INPUT type=\"Text\" id=\"name\" name=\"username\" value=\"\"> <br></th></tr>\n";
		print "<tr><th align=\"left\"><b>Select one of these base images:</b></th>";
		print "<th align=\"left\">";
		print "<select name=\"images\">";
		foreach($imgnames as $in) {
			if($in != "No Image")
                		print "<option>$in</option>";
		}
		print "</select></th>";
		print "<tr><th align=\"left\"><b>Select one of these computers:</b></th>";
		print "<th align=\"left\"><select name=\"computers\">";
		foreach($compnames as $cn) {
                        if($in != "No Image")
                                print "<option>$cn</option>";
                }
		print "</select>";
                print "</th></tr>";
                print "<tr><th align=\"left\"><INPUT type=\"submit\" name=\"continuation\" align=\"center\" value=\"Advise\"/></th></tr></table>";
                print "<INPUT type=hidden name=continuation value=\"$advisingCont\">\n";
                print "</FORM>\n";		
	}
	else if(strpos($data[$theoption], "Composite Multi-Image")) {
		print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
                $advisingCont = addContinuationsEntry("compositeMultiImage");
                print "<table>";
                print "<tr><th align=\"left\"><h3>Input Parameters</h3></th>";
		print "<tr><th align=\"left\"><b>Select one of these base images:</b></th>";
                print "<option value=\"WinXP Base (32 bit VM)\">WinXP Base (32 bit VM)</option></select>";
		print "<th align=\"left\">";
		print "<select name=\"baseimages\">";
                foreach($imgnames as $in) {
                        if($in != "No Image")
                                print "<option>$in</option>";
                }
                print "</th></select></tr>";
		print "<tr><th align=\"left\"><b>Select one of these sub-images:</b></th>";
		print "<th align=\"left\">";
		print "<select name=\"subimages\">";
                foreach($imgnames as $in) {
                        if($in != "No Image")
                                print "<option>$in</option>";
                }
                print "</th></select></tr>";
                print "<tr><th align=\"left\"><INPUT type=\"submit\" name=\"continuation\" align=\"center\" value=\"Advise\"/></th></tr></table>";
                print "<INPUT type=hidden name=continuation value=\"$advisingCont\">\n";
                print "</FORM>\n";		
	} 
        else {
		print "Query didn't match";
        }
}

function maxOverlapResFunc() {
	$userGroup = $_POST["usrgroups"];
	$maxOverlapRes = $_POST["max_overlap"];
	if( $maxOverlapRes == null ) {
		$maxOverlapRes = 5;
	}
	print "<H3><b>Policy:</b></H3><i>Edit Maximum Overlapping Reservation</i><br>\n";
	print "<H3>User Group:</H3>$userGroup<br>\n"; 
        print "<H3>Maximum Overlapping Reservation:</H3>$maxOverlapRes<br>\n";
    	print "<br><br> <H3>Follow these steps to get the policy working</H3>\n";
    	print "<ol>\n";
   	print "<li>Goto <b><i>Manage Groups</i></b> tab</li>\n";
    	print "<li>Search for the user group - <b>$userGroup@local</b> and click on <b><i>Edit</i></b></li>\n";
    	print "<li>Enter the value <b>$maxOverlapRes</b> in the Max Overlapping Reservation field</li>\n";
    	print "<li>Click <b><i>Confirm Changes</i></b> </li>\n";
    	print "</ol>\n";
    	print "<H4>You are done!</H4>\n";
}

function blockWinxpResFunc(){
	print "<H3><b>Policy:</b></H3><i>Create a bunch of winXP reservations for a class</i><br>\n";
	print "<br><br> <H3>Follow these steps to get the policy working</H3>";
	print "<ol>";
	print "<li>Goto <b><i>Block Allocations</i></b> tab</li>";
	print "<li>Click on <i>Create New Block Allocation</i> button</i></b> tab</li>";
	print "<li>Enter the value <b>" . $_POST["name"] . "</b> for name</li>";
	print "<li>Enter the value <b>" . $_POST["owner"] . "</b> for owner</li>";
	print "<li>Enter the value <b>" . $_POST["usergroups"] . "</b> for usergroup</li>";
	print "<li>Enter the value <b>" . $_POST["seats"] . "</b> for seats</li>";
	print "<li>Choose any of the radio options for dates/time</li>";
	print "<li>Select the <i>First Date of Usage</i> & <i>Last Date of Usage</i> and click on <i>Submit New Block Allocation</i></li>";
	print "<li>Click <b><i>Confirm Changes</i></b> </li>";
	print "</ol>";
	print "<H4>You are done!</H4>";
}

function visitingScholarFunc(){
        print "<H3><b>Policy:</b></H3>Image access to a User/Visiting Scholar<br>\n"; 
	$img = $_POST['images'];
        print "<H3>Image selected:</H3>$img<br>\n";
	global $user;
	$privs = array("imageCheckout");
	$rpriv = array("available");
	$resources = getUserResources($privs,$rpriv,1);
	print "<br><br><H3>Follow these steps to get the policy working</H3>\n";
	print "<ol>\n";
	print "<li>The image - <i>$img</i> has the following image groups:<br>\n";
	print "<ul>\n";
	
	$imggrouparray = array();
	$i=0;
     	foreach ($resources["image"] as $ig) {
    		$iga = array($ig);
    		$images = getResourcesFromGroups($iga,"image",0);
    		foreach($images as $im) {
        		if($im==$img) {
            			print "<li>$ig</li>";
				$imggrouparray[$i] = $ig;
				$i++;
				break;
        		}
    		}
	}
 
	print "</ul></li><br>\n";
	print "<li>The Image groups are mapped to the following Computer Groups respectively:</li>\n";
	print "<ul>\n"; 

        $tmp = getUserResources(array("imageAdmin"), array("manageMapping"), 1);
        $imageGroups = $tmp["image"];
        uasort($imageGroups, "sortKeepIndex");
	
        $resources2 = getUserResources(array('computerAdmin'), array('manageMapping'), 1);
	$compGroups = $resources2["computer"];
	uasort($compGroups, "sortKeepIndex");
	
	$imageCompMapping = getResourceMapping("image", "computer");
        $k = 0;
        $cg = array();
	$flag = 0;
	foreach($imageGroups as $imgid => $imgname) {
		$imagename = getResourceGroupName($imgid);
    		foreach($compGroups as $compid => $compname) {
        		$name = $imagename . " -> " . getResourceGroupName($compid);
       		 	if(array_key_exists($imgid, $imageCompMapping) && in_array($compid, $imageCompMapping[$imgid])) {
				if( ! in_array( $compid, $cg ) ){
					$cg[$k] = $compid;
					$k++;
				}
				if( in_array( $imagename, $imggrouparray )) {
					print "<li>$name</li>\n";
					$flag = 1;
				}
			}
		}
	}
	print "</ul><br>\n";
	if( $flag== 1 ) {
		$userGroups = getUserGroupsMapCompGroups($cg);
		print "<li>The User groups mapped to the above computer groups are:\n";
		print "<ul>\n"; 
		for($j=0; $j<count($userGroups); $j++) {
			print "<li>$userGroups[$j]</li>\n";
		}
		print "</ul></li><br>";
		print "<li>Add the visiting scholar to the User groups listed above</li><br>\n";
	} else {
		print "<b>NULL:</b> No mapping present between Image Groups and Computer Groups<br>\n";
		print "<li>To map Image Groups to Computer Groups, do the following:<br>";
		print "<ul>\n";
		print "<li> Goto \"Manage Images\" -> Edit Image Mapping -> Checkbox Grid Tab</li>\n";
		print "<li> Check the required mappings</li>\n";
		print "</ul></li><br>\n";
		print "<li> To find the owning user groups for the computer groups that you have selected:\n";
		print "<ul><li> Goto \"Manage Groups\" and scroll down to view the Resource Groups </li></ul></li><br>\n";
		print "<li>Add the visiting scholar to the User groups that you viewed in the above step</li><br>\n";
	}
	print "<li>To add a user to a User Group, do the following:</li><br>\n";
        print "<table border=\"1\"><tr><td>\n";
        print "Goto Manage Groups -> Edit the required User Group -> Goto Group Membership section -> Type the username -> Click Add\n";
        print "</td></tr></table>\n";
	print "</ol>\n";
	print "<H4>You are done!</H4>\n";
}

function grantImgAccessFunc() {
	$img = $_POST['images'];
        print "<H3>Policy:</H3>Create an Image and grant access to a User<br>\n";
	print "<br><br><H3>Follow these steps to get the policy working</H3>\n";
  	print "<ol>\n";
	print "<li> Creating a new image from the base image <b><i>$img</i></b>\n";	
  	print "<ul>";
	print "<li>Goto Manage Images -> Create/Update an image & then click \"submit\" button</li>";
	print "<li>Select <i>$img</i> as the base image and other required parameters and click on \"Create Imaging Reservation\" button</li>\n";
	print "<li>Wait till the reservation is ready & then click \"Connect\" button</li>\n";
        print "<li>Login (ssh or RDP) to the system using the ip and credentails listed on the page</li>\n"; 	
	print "<li>Install any applications if required and then logout of the system</li>\n";
	print "<li>Goto Current Reservations -> more options & select \"End reservation & Create Image\"\n";
	print "<li>Select Create New Image & click Submit button</li>\n";
	print "<li>Fill in all the Image details - Name, Description etc and hit Confirm Image button</li>\n";
	print "<li>Confirm all the details and click on Add Image button and Agreee to the Installer Agreement</li>\n";
	print "<li>Please wait for a while for the process to be completed</li>\n";
	print "</ul></li><br>";
        $tmp = getUserResources(array("imageAdmin"), array("manageMapping"), 1);
	$flag = 0;
        $imageGroups = $tmp["image"];
	if(count($imageGroups) == 0) {
		$flag = 1;	
	}		
	print "<li> Adding the image to an Image group\n";
	print "<ul>";
	print "<li>You can now look up the new image under Manage Images -> Edit Image Profiles</li>\n";
	if($flag == 1) 
	{
		print "<li>No Image groups are present. </li>\n";
		print "<li>To add a new image group, goto Manage Groups -> Add New Resource Group -> Select type as image and Add Group</li>\n";	
		
	}
	print "<li>Goto Manage Images -> Edit Image Grouping -> Checkbox Grib tab</li>\n";
	print "<li>Select the desired Image Group to associate the new image with and click Submit Changes button</li>\n";
	print "</ul></li><br>";
 	print "<li>Mapping the Image group to a computer group";
	print "<ul>";
	print "<li>Goto Manage Images -> Edit Image Mapping -> checkbox grid tab</li>\n";
	print "<li>Select the desired mapping & hit submit </li>\n";
	print "</ul></li><br>";
	print "<li>Verifying if the user belongs to any of the owning User groups</li>\n";
	print "<ul>";
	print "<li>Goto Manage Groups -> Resources Groups -> Check for owning groups for the above selected Computer Groups</li>";	
	print "<li>If the user is not a part of any of the Owning groups, add him to the groups by Editing the User Group and adding the user to it</li>";
	print "</ol><br>\n";
	print "<H4>You are done!</H4>\n";
}

function compositeMultiImageFunc() {	
	$baseimg = $_POST['baseimages'];
	$subimg = $_POST['subimages'];	
	print "<H3>Policy:</H3>Build a composite multi-image environment<br>\n";
	print "<H3>Base Image:</H3>$baseimg<br>\n";
	print "<H3>Sub Image:</H3>$subimg<br>\n";
        print "<br><br><H3>Follow these steps to get the policy working:</H3>\n";
	print "<ol>";
	print "<li>Goto Manage Images -> Edit Image Profiles</li>";
	print "<li>Click on edit button to the left of <i>$baseimg</i></li>";
	print "<li>Click on Advanced Options -> Manage Subimages & select the <i> $subimg</i> image from the drop down</li>";
	print "<li>Click on Add SubImage</li>";
	print "</ol>";
	print "<h4>You are done!</h4>";
}

function accessAndMappingFunc(){
        print "<H3>Policy:</H3> Check User accessibility & Resource mapping<br>\n";
	$user = $_POST['username'];
	if($user == null) {
		print "<H3>Username:</H3>NULL<br>\n";
	}
	else {
		print "<H3>Username:</H3>$user<br>\n";
	}
	$img = $_POST['images'];
        print "<H3>Image selected:</H3>$img<br>\n";
	$comp = $_POST['computers'];
        print "<H3>Computer selected:</H3>$comp<br>\n";
	if($user == null) {
		print "<br><br><H3>Please enter a valid <i>\"User ID\"</i> to get advised on the chosen policy</H3>\n";
		return;
	}

	$privs = array("imageCheckout");
        $rpriv = array("available");
        $resources = getUserResources($privs,$rpriv,1);
        print "<br><br><H3>Follow these steps to get the policy working</H3>\n";
        print "<ol>\n";
	print "<li>The user - <b><i>$user</i></b> has the following user groups:<br>\n";
	print "<ul>\n";
	$query = "SELECT id FROM user WHERE unityid = \"$user\";";
        $qresult = doQuery($query,101);
        $userID = "";
        while($row = mysql_fetch_assoc($qresult)){
                $userID = $row["id"];
        }
	$usergroupsarray = getUsersGroups($userID);
	foreach( $usergroupsarray as $grpelement) {
		print "<li>$grpelement@Local</li>";
	}
	if( empty($usergroupsarray)) {
		print "<li><b>NONE</b></li>";
	}
	print "</ul></li><br>\n";
        print "<li>The image - <b><i>$img</i></b> has the following image groups:<br>\n";
        print "<ul>\n";
	$imggrouparray = array();
	$i=0;
        foreach ($resources["image"] as $ig) {
                $iga = array($ig);
                $images = getResourcesFromGroups($iga,"image",0);
                foreach($images as $im) {
                        if($im==$img) {
                                print "<li>$ig</li>";
				$imggrouparray[$i] = $ig;
				$i++;
                                break;
                        }
                }
        }
	print"</ul></li><br>\n";
	print "<li>The computer - <b><i>$comp</i></b> has the following computer groups:<br>\n";
        print "<ul>\n";
	$compgrouparray = array();
	$k=0;
        foreach ($resources["computer"] as $cmpgrp) {
                $cga = array($cmpgrp);
                $computers = getResourcesFromGroups($cga,"computer",0);
                foreach($computers as $machine) {
                        if($machine==$comp) {
                                print "<li>$cmpgrp</li>";
				$compgrouparray[$k] = $cmpgrp;
				$k++;			
                                break;
                        }
                }
        }
	if( empty($compgrouparray) ) {
		print "<li><b>NULL</b></li>\n";
	}
        print "</ul></li><br>\n";
	print "<li>The Image groups are mapped to the following Computer Groups respectively:</li><br>\n";
        print "<table border=\"1\">\n";
	print "<tr><th><b>Image Group</b></th><th><b>Computer Group</b></th></tr>\n";
        $tmp = getUserResources(array("imageAdmin"), array("manageMapping"), 1);
        $imageGroups = $tmp["image"];
        uasort($imageGroups, "sortKeepIndex");

        $resources2 = getUserResources(array('computerAdmin'), array('manageMapping'), 1);
        $compGroups = $resources2["computer"];
        uasort($compGroups, "sortKeepIndex");

        $imageCompMapping = getResourceMapping("image", "computer");
        $j = 0;
        $cg = array();
        $flag = 0;
        foreach($imageGroups as $imgid => $imgname) {
                $imagename = getResourceGroupName($imgid);
		foreach($compGroups as $compid => $compname) {
			$computername = getResourceGroupName($compid);
                        if(array_key_exists($imgid, $imageCompMapping) && in_array($compid, $imageCompMapping[$imgid])) {
                                if( ! in_array( $compid, $cg ) ){
                                        $cg[$j] = $compid;
                                        $j++;
                                }
				if( in_array($imagename, $imggrouparray) && in_array($computername, $compgrouparray)) {
					print "<tr><td>$imagename</td>\n";
					print "<td>$computername</td></tr>\n";
                                	$flag = 1;
				}
                        }
                }
        }
	print "</table><br>\n";
	if( $flag==1 ) {
		$userGroups = getUserGroupsMapCompGroups($cg);
        	print "<li>The User groups mapped to the above computer groups are:\n";
		print "<ul>\n";
        	for($j=0; $j<count($userGroups); $j++) {
                        print "<li>$userGroups[$j]</li>\n";
        	}
		print "</ul></li><br>\n";
		$useraccess = 0;
		foreach($userGroups as $ug) {
			if( in_array($ug, $usergroupsarray) ) {
				$useraccess = 1;
			}
		}
		if ( $useraccess == 0 ) {
			print "<li>Add User:<i>$user</i> to any of the user groups listed above in step 5</li><br>\n";
			print "<li>To add a user to a User Group, do the following:</li><br>\n";
                	print "<table border=\"1\"><tr><td>\n";
                	print "Goto Manage Groups -> Edit the required user Group -> Goto Group Membership section -> Type the username -> Click Add\n";
                	print "</td></tr></table>\n";
		}
		else {
			print "<br><H3>User:<i>$user</i> already have access to image:<i>$img</i> and machine:<i>$comp</i></H3>\n";
		}
	}
	else {
                print "<b>NULL:</b> No mapping present between Image Groups and Computer Groups<br>\n";
                print "<li>To map Image Groups to Computer Groups, do the following:<br>";
                print "<ul>\n";
                print "<li> Goto \"Manage Images\" -> Edit Image Mapping -> Checkbox Grid Tab</li>\n";
                print "<li> Check the required mappings</li>\n";
                print "</ul></li><br>\n";
                print "<li>To find the owning user groups for the computer groups that you have selected:\n";
                print "<ul><li> Goto \"Manage Groups\" and scroll down to view the Resource Groups </li></ul></li>\n";
                print "<li>Add the User:<i>$user</i> to any of the User groups that you viewed in the above step</li><br>\n";
		print "<li>To add a user to a User Group, do the following:</li><br>\n";
		print "<table border=\"1\"><tr><td>\n";
		print "Goto Manage Groups -> Edit the required user Group -> Goto Group Membership section -> Type the username -> Click Add\n";
		print "</td></tr></table>\n";

        }
	print "</ol><br>\n";
	print "<h4>You are done!</h4>";
}


?>
