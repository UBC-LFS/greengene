<?php
require_once('includes/config.php');
echo "<h3>LDAP query test</h3>";
echo "Connecting ...";
$ds=ldap_connect(LDAP_HOST);  // must be a valid LDAP server!
echo "connect result is " . $ds . "<br />";

if ($ds) {
    echo "Binding ...";
    $r=ldap_bind($ds, LDAP_DN, LDAP_PW);     // this is an "anonymous" bind, typically
                           // read-only access
    echo "Bind result is " . $r . "<br />";

    echo "Searching for (sn=S*) ...";
    // Search surname entry
    $base_dn = "ou=UBC,ou=ACADEMIC,dc=id,dc=ubc,dc=ca";
    $filter = "(&(objectClass=*)(cn=APBI_318_001_2019W))";
    $sr=ldap_search($ds, $base_dn, $filter );
    echo "Search result is " . $sr . "<br />";

    echo "Number of entries returned is " . ldap_count_entries($ds, $sr) . "<br />";

    echo "Getting entries ...<p>";
    $info = ldap_get_entries($ds, $sr);
   
    $uniquemember = $info[0]['uniquemember'];
    $result = [];
    for ($i = 0; $i < $uniquemember['count']; $i++){
	    $temp = substr($uniquemember[$i], 4);
	    $temp = explode(",", $temp)[0];
	    array_push($result, $temp);
    }

    var_dump($result);
    echo "Closing connection";
    ldap_close($ds);
}

?>
