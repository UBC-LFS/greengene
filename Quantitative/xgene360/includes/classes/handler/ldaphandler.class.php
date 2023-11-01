<?php
class LDAPHandler
{
    // payload should be a nested object keys: subjectCode, courseNumber, section, year, session
    function importClassList($payload) 
    {
        $result = [];
        $cn = self::getCommonName($payload);
		
		$ds = ldap_connect(LDAP_HOST);
		ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, 3);

		if ($ds) {
			// remove warning when bind fails
			set_error_handler(function() {});
			$r=ldap_bind($ds, LDAP_DN, LDAP_PW);
			
			restore_error_handler();

			if ($r){
				$base_dn = "ou=UBC,ou=ACADEMIC,dc=id,dc=ubc,dc=ca"; 
				$filter = "(&(objectClass=*)(cn=".$cn."))";

				$sr=ldap_search($ds, $base_dn, $filter);
				$info = ldap_get_entries($ds, $sr);
				$uniquemember = $info[0]['uniquemember'];
				for ($i = 0; $i < $uniquemember['count']; $i++) {
					$temp = substr($uniquemember[$i], 4);
					$temp = explode(",", $temp)[0];
					array_push($result, $temp);
				}
			} else {
				$result = null;
				echo "<h2 style=\"color:red;\"> Failed to retrieve student records. Please make sure you are connected to UBC VPN</h2>";
			};
		}
		ldap_close($ds);
        return $result;
    }

    function getCommonName($payload) {
		$result = "";
		$result = $payload['subjectCode']."_";
		$result = $result.$payload['courseNumber']."_";
		$result = $result.$payload['section']."_";
		$result = $result.$payload['year'].$payload['session'];
		return $result;
	}

	function createUserFromLDAPResult($classList) {
		global $g_obj_student_manager;

		if ($classList === null) {
			return null;
		}

		$arr_success = array();
		$arr_fail = array();
		for ($i = 0; $i < count($classList); $i++) {
			$userId = $classList[$i];

			// add the user to the database
			if ( $g_obj_student_manager->create_user( $classList[$i],  UP_STUDENT,  '',  '') )
			{
				array_push( $arr_success, $userId );
			}				
			else
			{
				array_push( $arr_fail, $userId );
			}
		}

		if ( count( $arr_success ) != 0 )
		{
			$str_message = (new PageHandler) -> display_users_cwl( 'Successfully created user with CWL Username', $arr_success );
		
			(new MessageHandler) ->  add_message( MSG_SUCCESS, $str_message );
		}
		
		if ( count( $arr_fail ) != 0 )
		{
			$str_message = (new PageHandler) -> display_users_cwl( 'Failed to create user with CWL Username', $arr_fail );
		
			(new MessageHandler) ->  add_message( MSG_FAIL, $str_message );
		}
	}

}
/*
echo ' List of students for APBI318 001 2019W: ';
echo "\n";
$payload = ['subjectCode' => 'APBI',
			'courseNumber' => '318',
			'section' => '001',
			'year' => '2019',
			'session' => 'W'];
$result = LDAPHandler::importClassList($payload);
var_dump($result);
echo "\n";
*/
?>