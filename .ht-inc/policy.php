<?php

function policy() {
	global $user;
	
   	print "<H2>Policy.php Assessment Module</H2>";
	print "User Groups </br>";
	$usergroups = getUsersGroups($user['id'],1,1);
	foreach($usergroups as $id =>$group)
		print "$id: $group</br>";
	print "</br>";
	
	$usertimes = getUserMaxTimes($user['id']);	
	print "User Max Times </br>";
	print "Initial: ";
	print minToHourMin($usertimes['initial']);
	print "</br>Total: ";
	print minToHourMin($usertimes['total']);
	print "</br>Extend: ";
	print minToHourMin($usertimes['extend']);
	print "</br></br>";

	$ug = getUserGroups();
	$max = 0;
	foreach ($ug as $group) {
		$current = $group['overlapResCount'];
		if ($current > $max)
			$max = $group['overlapResCount'];
	}
	
	print "Max Concurrent Reservations:</br>";	
	print $max;
	print "</br>";
	
	print "<h3>User Resources:</h3>";
	$privs = array("imageCheckout");
	$rpriv = array("available");
	$resources = getUserResources($privs,$rpriv,1);
	
	$compMap = array(); # Maps computer group id to name
	$imgMap = array(); # Maps image group id to name
	$computerList = array(); # List of computers
	print "<h4>Computer Groups:</h4>";
	foreach ($resources["computer"] as $cg) {
		print "$cg (";
		$cg2 = "computer/$cg";
		print getResourceGroupID($cg2);
		print ")</br>";	
		
		$compMap[getResourceGroupID($cg2)] = $cg;

		$cga = array($cg);
		$computers = getResourcesFromGroups($cga,"computer",0);

		foreach($computers as $cid => $cname) 	
			$computerList[$cid] = $cname;
/*		
		foreach($computers as $comp) {
			print $comp;
			print "</br>";
		}
		print "</br>";
*/
	}
	
        print "<h4>Image Groups:</h4>";
        foreach ($resources["image"] as $ig) {
                print "$ig (";
		$ig2 = "image/$ig";
		print getResourceGroupId($ig2);
		print ")</br>";
		
		$imgMap[getResourceGroupId($ig2)] = $ig;
/*         
                $iga = array($ig);
                $images = getResourcesFromGroups($iga,"image",0);
                
                foreach($images as $im) {
                        print $im;
                        print "</br>";
                }
                print "</br>";
*/
        }

	 print "<h4>Management Nodes:</h4>";
        foreach ($resources["managementnode"] as $mn) {
                print $mn;
                print "</br>";
        }
	
	 print "<h4>Schedule Groups:</h4>";
        foreach ($resources["schedule"] as $sc) {
                print "$sc</br>";
/*
                $sca = array($sc);
                $schedules = getResourcesFromGroups($sca,"schedule",0);

                foreach($schedules as $sch) {
                        print $sch;
                        print "</br>";
                }
                print "</br>";
*/
        }

	
	  print "<h4>Server Profiles:</h4>";
        foreach ($resources["serverprofile"] as $sp) {
                print "$sp</br>";
/*
                $spe = array($sp);
                $sps = getResourcesFromGroups($spe,"serverprofile",0);

                foreach($sps as $spr) {
                        print $spr;
                        print "</br>";
                }
                print "</br>";

*/        }
 	  print "<h4>Image to Computer Mapping:</h4>";
	  $imgGroupSet = "'" . implode("','", array_keys($imgMap)) . "'";
	  $compGroupSet = "'" . implode("','", array_keys($compMap)) . "'";
	  $map = getResourceMapping("image","computer",$imgGroupSet,$compGroupSet);
	  
	  foreach ($map as $id => $compids) {
		print "$imgMap[$id] -> ";
		
		foreach ($compids as $cid)
			print "$compMap[$cid], ";
	 	print "</br>"; 
	 }

	 print "<h4> Computer to Schedule Mapping; </h4>";
	 $computerIds = "'" . implode("','", array_keys($computerList)) . "'";
	 $query = "SELECT c.id, s.name 
		   FROM schedule s, computer c
		   WHERE c.scheduleid = s.id";
	 $qh = doQuery($query,101);
	 while($row = mysql_fetch_assoc($qh)) {
		print $computerList[$row['id']];
		print " -> ";
		print $row['name'];
		print "</br>";
	 } 
print "------------------------------------------</br>";	  
}
/*
#----------------------------------------------------------------
# Function for the Button

global $cont;
   print "<H3>Select what you want to perform</H3>\n";
   print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
   print "<INPUT type=radio  name=continuation value=\"$cont\" checked ";
   print "id=\"assessment\"><label for=\"policy\">";
   print "Assessment Engine</label><br>\n";

   print "<INPUT type=radio name=continuation value=\"$cont\" id=\"";
   print "id=\"advisor\"><label for=\"advisingFunc\">Advising Engine";
   print "</label><br>\n";
   print "<INPUT type=radio name=continuation value=\"$cont\" id=\"";
   print "id=\"complaince\"><label for=\"complianceFunc\">Compliance Engine";
   print "</label><br>\n";

   print "<br><INPUT type=submit value=Submit>\n";
   print "</FORM>\n";

# ----------------------------------------------------------------------

#----------------

$options = array(0 => "option1",
                 1 => "option2");
print "<FORM action=\"" . BASEURL . SCRIPT . "\" method=post>\n";
print "Select one of these options:";
printSelectInput("theoption", $options);
$cont = addContinuationsEntry("submitFunc1Form", $options);
print "<INPUT type=hidden name=continuation value=\"$cont\">\n";
print "<INPUT type=submit value=Submit>\n";
print "</FORM>\n";

function submitFunc1Form() 
{   $data = getContinuationVar();
   $theoption = processInputVar("theoption", ARG_NUMERIC);
   if(! array_key_exists($theoption, $data)) {
      print "invalid option submitted\n";
      return;
   }
   print "The option you selected was: ";
   print "{$data\[$theoption\]}<br>\n";

}
*/
/*
$ownerid=1;
$query=sprintf("SELECT id,prettyname FROM image WHERE ownerid='%s'",mysql_real_escape_string($ownerid));

$result = mysql_query($query);
#---Printing result of query----------
while ($row = mysql_fetch_assoc($result))
{
    echo $row['id'];
	print "</br>";
    echo $row['prettyname']; 
	
}
}
*/
?>

