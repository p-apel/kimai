<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) 2006-2009 Kimai-Development-Team
 *
 * Kimai is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * Kimai is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// =============================================================
// = various functions for working with the kimai database     =
// =============================================================

// checked 

function clean_data($data) {
    global $kga;   
    foreach ($data as $key => $value) {
        if ($key != "pw") { 
            $return[$key] = urldecode(strip_tags($data[$key]));
    		$return[$key] = str_replace('"','_',$data[$key]);
    		$return[$key] = str_replace("'",'_',$data[$key]);
    		$return[$key] = str_replace('\\','',$data[$key]);
        } else {
            $return[$key] = $data[$key];
        }
		if ($kga['utf8']) $return[$key] = utf8_decode($return[$key]);
    }
    
    return $return;
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Adds a new customer
 *
 * @param array $data  name, address and other data of the new customer
 * @global array $kga  kimai-global-array
 * @return int         the knd_ID of the new customer, false on failure
 * @author th
 */
 
// checked 
 
function knd_create($data) {
    global $kga, $conn;
    
    $data = clean_data($data);

    $values     ['knd_name']        =     MySQL::SQLValue($data   ['knd_name']          );
    $values     ['knd_comment']     =     MySQL::SQLValue($data   ['knd_comment']       );
    $values     ['knd_company']     =     MySQL::SQLValue($data   ['knd_company']       );
    $values     ['knd_street']      =     MySQL::SQLValue($data   ['knd_street']        );
    $values     ['knd_zipcode']     =     MySQL::SQLValue($data   ['knd_zipcode']       );
    $values     ['knd_city']        =     MySQL::SQLValue($data   ['knd_city']          );
    $values     ['knd_tel']         =     MySQL::SQLValue($data   ['knd_tel']           );
    $values     ['knd_fax']         =     MySQL::SQLValue($data   ['knd_fax']           );
    $values     ['knd_mobile']      =     MySQL::SQLValue($data   ['knd_mobile']        );
    $values     ['knd_mail']        =     MySQL::SQLValue($data   ['knd_mail']          );
    $values     ['knd_homepage']    =     MySQL::SQLValue($data   ['knd_homepage']      );
    $values     ['knd_logo']        =     MySQL::SQLValue($data   ['knd_logo']          );
    
    $values['knd_visible'] = MySQL::SQLValue($data['knd_visible'] , MySQL::SQLVALUE_NUMBER  );
    $values['knd_filter']  = MySQL::SQLValue($data['knd_filter']  , MySQL::SQLVALUE_NUMBER  );
 
    $table = $kga['server_prefix']."knd";
    $result = $conn->InsertRow($table, $values);
    
    logfile($result);

    if (! $result) {
    	return false;
    } else {
    	return $conn->GetLastInsertID();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns the data of a certain customer
 *
 * @param array $knd_id  knd_id of the customer
 * @global array $kga    kimai-global-array
 * @return array         the customer's data (name, address etc) as array, false on failure
 * @author th
 */
 
// checked 
  
function knd_get_data($knd_id) {
    global $kga, $conn;

    $filter['knd_ID'] = MySQL::SQLValue($knd_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."knd";
    $result = $conn->SelectRows($table, $filter);
    
    if (! $result) {
    	return false;
    } else {
        // return  $conn->getHTML();
        return $conn->RowArray(0,MYSQL_ASSOC);
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Edits a customer by replacing his data by the new array
 *
 * @param array $knd_id  knd_id of the customer to be edited
 * @param array $data    name, address and other new data of the customer
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author ob/th
 */
 
// checked 
 
function knd_edit($knd_id, $data) {
    global $kga, $conn;
    
    $data = clean_data($data);
    
    if (! $conn->TransactionBegin()) $conn->Kill();

    $original_array = knd_get_data($knd_id);
    $new_array = array();
    
    foreach ($original_array as $key => $value) {
        if (isset($data[$key]) == true) {
            $new_array[$key] = $data[$key];
        } else {
            $new_array[$key] = $original_array[$key];
        }
    }

    $values['knd_name']     = MySQL::SQLValue($new_array['knd_name']    );
    $values['knd_comment']  = MySQL::SQLValue($new_array['knd_comment'] );
    $values['knd_company']  = MySQL::SQLValue($new_array['knd_company'] );
    $values['knd_street']   = MySQL::SQLValue($new_array['knd_street']  );
    $values['knd_zipcode']  = MySQL::SQLValue($new_array['knd_zipcode'] );
    $values['knd_city']     = MySQL::SQLValue($new_array['knd_city']    );
    $values['knd_tel']      = MySQL::SQLValue($new_array['knd_tel']     );
    $values['knd_fax']      = MySQL::SQLValue($new_array['knd_fax']     );
    $values['knd_mobile']   = MySQL::SQLValue($new_array['knd_mobile']  );
    $values['knd_mail']     = MySQL::SQLValue($new_array['knd_mail']    );
    $values['knd_homepage'] = MySQL::SQLValue($new_array['knd_homepage']);
    $values['knd_logo']     = MySQL::SQLValue($new_array['knd_logo']    );
    $values['knd_visible']  = MySQL::SQLValue($new_array['knd_visible'] , MySQL::SQLVALUE_NUMBER );
    $values['knd_filter']   = MySQL::SQLValue($new_array['knd_filter']  , MySQL::SQLVALUE_NUMBER );
    $filter['knd_ID']       = MySQL::SQLValue($knd_id, MySQL::SQLVALUE_NUMBER);
    
    $table = $kga['server_prefix']."knd";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    
    $success = true;
    
    if (! $conn->Query($query)) $success = false;
    
    if ($success) {
        if (! $conn->TransactionEnd()) $conn->Kill();
    } else {
        if (! $conn->TransactionRollback()) $conn->Kill();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Assigns a customer to 1-n groups by adding entries to the cross table
 *
 * @param int $knd_id         knd_id of the customer to which the groups will be assigned
 * @param array $grp_array    contains one or more grp_IDs
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob/th
 */

// checked  
 
function assign_knd2grps($knd_id, $grp_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();
    
    $table = $kga['server_prefix']."grp_knd";
    $filter['knd_ID'] = MySQL::SQLValue($knd_id, MySQL::SQLVALUE_NUMBER);
    $d_query = MySQL::BuildSQLDelete($table, $filter);
    $d_result = $conn->Query($d_query);
    
    if ($d_result == false) {
            $conn->TransactionRollback();
            return false;
    }

    foreach ($grp_array as $current_grp) {
        
        $filter['grp_ID'] = MySQL::SQLValue($current_grp , MySQL::SQLVALUE_NUMBER);
        $filter['knd_ID'] = MySQL::SQLValue($knd_id      , MySQL::SQLVALUE_NUMBER);
        $c_query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($c_query);
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID'] = MySQL::SQLValue($current_grp , MySQL::SQLVALUE_NUMBER);
            $values['knd_ID'] = MySQL::SQLValue($knd_id      , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);
            $result = $conn->Query($query);            

            if ($result == false) {
                    $conn->TransactionRollback();
                    return false;
            }
        }
    }

    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the groups of the given customer
 *
 * @param array $knd_id  knd_id of the customer
 * @global array $kga    kimai-global-array
 * @return array         contains the grp_IDs of the groups or false on error
 * @author th
 */
 
// checked 
  
function knd_get_grps($knd_id) {
    global $kga, $conn;

    $filter['knd_ID'] = MySQL::SQLValue($knd_id, MySQL::SQLVALUE_NUMBER);
    $columns[]        = "grp_ID";
    $table = $kga['server_prefix']."grp_knd";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }

    $return_grps = array();
    $counter     = 0;
    
    $rows = $conn->RecordsArray(MYSQL_ASSOC);
    
    if ($conn->RowCount()) {
        foreach ($rows as $current_grp) {
            $return_grps[$counter] = $current_grp['grp_ID'];
            $counter++;   
        }
        return $return_grps;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * deletes a customer
 *
 * @param array $knd_id  knd_id of the customer
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author th
 */

// not implemented yet 

function knd_delete($knd_id) {
    global $kga, $conn;

    $values['knd_trash'] = 1;    
    $filter['knd_ID'] = MySQL::SQLValue($knd_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."knd";
        
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    return $conn->Query($query);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Adds a new project
 *
 * @param array $data  name, comment and other data of the new project
 * @global array $kga  kimai-global-array
 * @return int         the pct_ID of the new project, false on failure
 * @author th
 */
 
// checked 

function pct_create($data) {
    global $kga, $conn;
    
    $data = clean_data($data);
        
    $values['pct_name']    = MySQL::SQLValue($data['pct_name']    );
    $values['pct_comment'] = MySQL::SQLValue($data['pct_comment'] );
    $values['pct_logo']    = MySQL::SQLValue($data['pct_logo']    );    
    $values['pct_kndID']   = MySQL::SQLValue($data['pct_kndID']   , MySQL::SQLVALUE_NUMBER );
    $values['pct_visible'] = MySQL::SQLValue($data['pct_visible'] , MySQL::SQLVALUE_NUMBER );
    $values['pct_filter']  = MySQL::SQLValue($data['pct_filter']  , MySQL::SQLVALUE_NUMBER );
    
    $table = $kga['server_prefix']."pct";
    $result = $conn->InsertRow($table, $values);
     
    if (! $result) {
    	return false;
    } else {
    	return $conn->GetLastInsertID();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns the data of a certain project
 *
 * @param array $pct_id  pct_id of the project
 * @global array $kga    kimai-global-array
 * @return array         the project's data (name, comment etc) as array, false on failure
 * @author th
 */
 
// checked 
  
function pct_get_data($pct_id) {
    global $kga, $conn;

    $filter['pct_ID'] = MySQL::SQLValue($pct_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."pct";
    $result = $conn->SelectRows($table, $filter);

    if (! $result) {
    	return false;
    } else {
        return $conn->RowArray(0,MYSQL_ASSOC);
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Edits a project by replacing its data by the new array
 *
 * @param array $pct_id   pct_id of the project to be edited
 * @param array $data     name, comment and other new data of the project
 * @global array $kga     kimai-global-array
 * @return boolean        true on success, false on failure
 * @author ob/th
 */

// checked 

function pct_edit($pct_id, $data) {
    global $kga, $conn;
    
    $data = clean_data($data);
    
    if (! $conn->TransactionBegin()) $conn->Kill();

    $original_array = pct_get_data($pct_id);
    $new_array = array();
    
    foreach ($original_array as $key => $value) {
        if (isset($data[$key]) == true) {
            $new_array[$key] = $data[$key];
        } else {
            $new_array[$key] = $original_array[$key];
        }
    }

    $values ['pct_name']    = MySQL::SQLValue($new_array ['pct_name']      );
    $values ['pct_comment'] = MySQL::SQLValue($new_array ['pct_comment']   );
    $values ['pct_logo']    = MySQL::SQLValue($new_array ['pct_logo']      );
    $values ['pct_kndID']   = MySQL::SQLValue($new_array ['pct_kndID']   , MySQL::SQLVALUE_NUMBER  );
    $values ['pct_visible'] = MySQL::SQLValue($new_array ['pct_visible'] , MySQL::SQLVALUE_NUMBER  );
    $values ['pct_filter']  = MySQL::SQLValue($new_array ['pct_filter']  , MySQL::SQLVALUE_NUMBER  );

    $filter ['pct_ID'] = MySQL::SQLValue($pct_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."pct";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    
    $success = true;
    
    if (! $conn->Query($query)) $success = false;
    
    if ($success) {
        if (! $conn->TransactionEnd()) $conn->Kill();
    } else {
        if (! $conn->TransactionRollback()) $conn->Kill();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Assigns a project to 1-n groups by adding entries to the cross table
 *
 * @param int $pct_id        pct_id of the project to which the groups will be assigned
 * @param array $grp_array    contains one or more grp_IDs
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob/th
 */
 
// checked 

function assign_pct2grps($pct_id, $grp_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();

    $table = $kga['server_prefix']."grp_pct";
    $filter['pct_ID'] = MySQL::SQLValue($pct_id, MySQL::SQLVALUE_NUMBER);
    $d_query = MySQL::BuildSQLDelete($table, $filter);
    $d_result = $conn->Query($d_query);    
    
    if ($d_result == false) {
            $conn->TransactionRollback();
            return false;
    }

    foreach ($grp_array as $current_grp) {
        
        $filter['grp_ID'] = MySQL::SQLValue($current_grp , MySQL::SQLVALUE_NUMBER);
        $filter['pct_ID'] = MySQL::SQLValue($pct_id      , MySQL::SQLVALUE_NUMBER);
        $c_query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($c_query);
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID']   = MySQL::SQLValue($current_grp , MySQL::SQLVALUE_NUMBER);
            $values['pct_ID']   = MySQL::SQLValue($pct_id      , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);
            $result = $conn->Query($query);
            
            if ($result == false) {
                    $conn->TransactionRollback();
                    return false;
            }
        }
    }

    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the groups of the given project
 *
 * @param array $pct_id  pct_id of the project
 * @global array $kga    kimai-global-array
 * @return array         contains the grp_IDs of the groups or false on error
 * @author th
 */
 
// checked 
  
function pct_get_grps($pct_id) {
    global $kga, $conn;

    $filter['pct_ID'] = MySQL::SQLValue($pct_id, MySQL::SQLVALUE_NUMBER);
    $columns[]        = "grp_ID";
    $table = $kga['server_prefix']."grp_pct";

    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }

    $return_grps = array();
    $counter     = 0;

    $rows = $conn->RecordsArray(MYSQL_ASSOC);

    if ($conn->RowCount()) {
        foreach ($rows as $current_grp) {
            $return_grps[$counter] = $current_grp['grp_ID'];
            $counter++;   
        }
        return $return_grps;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * deletes a project
 *
 * @param array $pct_id  pct_id of the project
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author th
 */

// not implemented yet 

function pct_delete($pct_id) {
    global $kga, $conn;

    $values['pct_trash'] = 1;    
    $filter['pct_ID'] = MySQL::SQLValue($pct_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."pct";
        
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    return $conn->Query($query);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Adds a new event
 *
 * @param array $data   name, comment and other data of the new event
 * @global array $kga   kimai-global-array
 * @return int          the evt_ID of the new project, false on failure
 * @author th
 */

// checked 

function evt_create($data) {
    global $kga, $conn;

    $data = clean_data($data);
    
    $values['evt_name']    = MySQL::SQLValue($data['evt_name']    );
    $values['evt_comment'] = MySQL::SQLValue($data['evt_comment'] );
    $values['evt_logo']    = MySQL::SQLValue($data['evt_logo']    );
    $values['evt_visible'] = MySQL::SQLValue($data['evt_visible'] , MySQL::SQLVALUE_NUMBER );
    $values['evt_filter']  = MySQL::SQLValue($data['evt_filter']  , MySQL::SQLVALUE_NUMBER );

    $table = $kga['server_prefix']."evt";
    $result = $conn->InsertRow($table, $values);

    if (! $result) {
    	return false;
    } else {
    	return $conn->GetLastInsertID();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns the data of a certain task
 *
 * @param array $evt_id  evt_id of the project
 * @global array $kga    kimai-global-array
 * @return array         the event's data (name, comment etc) as array, false on failure
 * @author th
 */

// checked 

function evt_get_data($evt_id) {
    global $kga, $conn;

    $filter['evt_ID'] = MySQL::SQLValue($evt_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."evt";
    $result = $conn->SelectRows($table, $filter);

    if (! $result) {
    	return false;
    } else {
        return $conn->RowArray(0,MYSQL_ASSOC);
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Edits an event by replacing its data by the new array
 *
 * @param array $evt_id  evt_id of the project to be edited
 * @param array $data    name, comment and other new data of the event
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author th
 */

// checked 

function evt_edit($evt_id, $data) {
    global $kga, $conn;
    
    $data = clean_data($data);
    
    if (! $conn->TransactionBegin()) $conn->Kill();

    $original_array = evt_get_data($evt_id);
    $new_array = array();
    
    foreach ($original_array as $key => $value) {
        if (isset($data[$key]) == true) {
            $new_array[$key] = $data[$key];
        } else {
            $new_array[$key] = $original_array[$key];
        }
    }

    $values  ['evt_name']        =   MySQL::SQLValue($new_array   ['evt_name']      );
    $values  ['evt_comment']     =   MySQL::SQLValue($new_array   ['evt_comment']   );
    $values  ['evt_logo']        =   MySQL::SQLValue($new_array   ['evt_logo']      );
    $values  ['evt_visible']     =   MySQL::SQLValue($new_array   ['evt_visible'] , MySQL::SQLVALUE_NUMBER  );
    $values  ['evt_filter']      =   MySQL::SQLValue($new_array   ['evt_filter']  , MySQL::SQLVALUE_NUMBER  );

    $filter  ['evt_ID']          =   MySQL::SQLValue($evt_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."evt";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    
    $success = true;
    
    if (! $conn->Query($query)) $success = false;
    
    if ($success) {
        if (! $conn->TransactionEnd()) $conn->Kill();
    } else {
        if (! $conn->TransactionRollback()) $conn->Kill();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Assigns an event to 1-n groups by adding entries to the cross table
 *
 * @param int $evt_id         evt_id of the project to which the groups will be assigned
 * @param array $grp_array    contains one or more grp_IDs
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob/th
 */
 
// checked 

function assign_evt2grps($evt_id, $grp_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();        

    $table = $kga['server_prefix']."grp_evt";
    $filter['evt_ID'] = MySQL::SQLValue($evt_id, MySQL::SQLVALUE_NUMBER);
    $d_query = MySQL::BuildSQLDelete($table, $filter);
    $d_result = $conn->Query($d_query);    

    if ($d_result == false) {
        $conn->TransactionRollback();
        return false;
    }

    foreach ($grp_array as $current_grp) {
        
        $filter['grp_ID'] = MySQL::SQLValue($current_grp , MySQL::SQLVALUE_NUMBER);
        $filter['evt_ID'] = MySQL::SQLValue($evt_id      , MySQL::SQLVALUE_NUMBER);
        $c_query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($c_query);
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID'] = MySQL::SQLValue($current_grp , MySQL::SQLVALUE_NUMBER);
            $values['evt_ID'] = MySQL::SQLValue($evt_id      , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);
            $result = $conn->Query($query);            
            
            if ($result == false) {
                $conn->TransactionRollback();
                return false;
            }
        }
    }
    
    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the groups of the given event
 *
 * @param array $evt_id  evt_id of the project
 * @global array $kga    kimai-global-array
 * @return array         contains the grp_IDs of the groups or false on error
 * @author th
 */
 
// checked 
 
function evt_get_grps($evt_id) {
    global $kga, $conn;

    $filter ['evt_ID'] = MySQL::SQLValue($evt_id, MySQL::SQLVALUE_NUMBER);
    $columns[]         = "grp_ID";
    $table = $kga['server_prefix']."grp_evt";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }

    $return_grps = array();
    $counter     = 0;
    
    $rows = $conn->RecordsArray(MYSQL_ASSOC);
    
    if ($conn->RowCount()) {
        foreach ($rows as $current_grp) {
            $return_grps[$counter] = $current_grp['grp_ID'];
            $counter++;   
        }
        return $return_grps;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * deletes an event
 *
 * @param array $evt_id  evt_id of the event
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author th
 */

// not implemented yet 

function evt_delete($evt_id) {
    global $kga, $conn;

    $values['evt_trash'] = 1;    
    $filter['evt_ID'] = MySQL::SQLValue($evt_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."evt";
        
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    return $conn->Query($query);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Assigns a group to 1-n customers by adding entries to the cross table
 * (counterpart to assign_knd2grp)
 * 
 * @param array $grp_id        grp_id of the group to which the customers will be assigned
 * @param array $knd_array    contains one or more knd_IDs
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob/th
 */



function assign_grp2knds($grp_id, $knd_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();    

    $table = $kga['server_prefix']."grp_knd";
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $d_query = MySQL::BuildSQLDelete($table, $filter);

    $d_result = $conn->Query($d_query);    

    if ($d_result == false) {
            $conn->TransactionRollback();
            return false;
    }
    
    foreach ($knd_array as $current_knd) {
        
        $filter['grp_ID'] = MySQL::SQLValue($grp_id     , MySQL::SQLVALUE_NUMBER);
        $filter['knd_ID'] = MySQL::SQLValue($current_knd, MySQL::SQLVALUE_NUMBER);
        $c_query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($c_query);
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID']       = MySQL::SQLValue($grp_id      , MySQL::SQLVALUE_NUMBER);
            $values['knd_ID']       = MySQL::SQLValue($current_knd , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);
            $result = $conn->Query($query);            
            
            if ($result == false) {
                    $conn->TransactionRollback();
                    return false;
            }
            
        }
    }
    
    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

//-----------------------------------------------------------------------------------------------------------

/**
 * Assigns a group to 1-n projects by adding entries to the cross table
 * (counterpart to assign_pct2grp)
 * 
 * @param array $grp_id        grp_id of the group to which the projects will be assigned
 * @param array $pct_array    contains one or more pct_IDs
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob
 */
function assign_grp2pcts($grp_id, $pct_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();    

    $table = $kga['server_prefix']."grp_pct";
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $d_query = MySQL::BuildSQLDelete($table, $filter);
    $d_result = $conn->Query($d_query);    

    if ($d_result == false) {
            $conn->TransactionRollback();
            return false;
    }
    
    foreach ($pct_array as $current_pct) {
        
        $filter['grp_ID'] = MySQL::SQLValue($grp_id     , MySQL::SQLVALUE_NUMBER);
        $filter['pct_ID'] = MySQL::SQLValue($current_pct, MySQL::SQLVALUE_NUMBER);
        $c_query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($c_query);
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID'] = MySQL::SQLValue($grp_id      , MySQL::SQLVALUE_NUMBER);
            $values['pct_ID'] = MySQL::SQLValue($current_pct , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);
            $result = $conn->Query($query);            

            if ($result == false) {
                $conn->TransactionRollback();
                return false;
            }
        }
    }

    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

//-----------------------------------------------------------------------------------------------------------

/**
 * Assigns a group to 1-n events by adding entries to the cross table
 * (counterpart to assign_evt2grp)
 * 
 * @param array $grp_id        grp_id of the group to which the events will be assigned
 * @param array $evt_array    contains one or more evt_IDs
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob
 */
function assign_grp2evts($grp_id, $evt_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();   

    $table = $kga['server_prefix']."grp_evt";
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $d_query = MySQL::BuildSQLDelete($table, $filter);
    $d_result = $conn->Query($d_query);    

    if ($d_result == false) {
        $conn->TransactionRollback();
        return false;
    }

    foreach ($evt_array as $current_evt) {
        
        $filter['grp_ID'] = MySQL::SQLValue($grp_id     , MySQL::SQLVALUE_NUMBER);
        $filter['evt_ID'] = MySQL::SQLValue($current_evt, MySQL::SQLVALUE_NUMBER);
        $c_query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($c_query);        
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID'] = MySQL::SQLValue($grp_id      , MySQL::SQLVALUE_NUMBER);
            $values['evt_ID'] = MySQL::SQLValue($current_evt , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);
            $result = $conn->Query($query);            

            if ($result == false) {
                $conn->TransactionRollback();
                return false;
            }
        }
    }

    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the customers of the given group
 *
 * @param array $grp_id  grp_id of the group
 * @global array $kga    kimai-global-array
 * @return array         contains the knd_IDs of the groups or false on error
 * @author th
 */
 
// checked 

function grp_get_knds($grp_id) {
    global $kga, $conn;
    
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $columns[] = "knd_ID";
    $table = $kga['server_prefix']."grp_knd";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }
    
    $rows = $conn->RecordsArray(MYSQL_ASSOC);
    
    $return_knds = array();
    $counter     = 0;
    if ($conn->RowCount()) {
        foreach ($rows as $current_knd) {
            $return_knds[$counter] = $current_knd['knd_ID'];
            $counter++;   
        }
        return $return_knds;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the projects of the given group
 *
 * @param array $grp_id  grp_id of the group
 * @global array $kga    kimai-global-array
 * @return array         contains the pct_IDs of the groups or false on error
 * @author th
 */
 
// checked 

function grp_get_pcts($grp_id) {
    global $kga, $conn;
    
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $columns[]        = "pct_ID";
    $table = $kga['server_prefix']."grp_pct";

    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }

    $return_pcts = array();
    $counter     = 0;

    $rows = $conn->RecordsArray(MYSQL_ASSOC);

    if ($conn->RowCount()) {
        foreach ($rows as $current_pct) {
            $return_pcts[$counter] = $current_pct['pct_ID'];
            $counter++;
        }
        return $return_pcts;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the events of the given group
 *
 * @param array $grp_id  grp_id of the group
 * @global array $kga    kimai-global-array
 * @return array         contains the evt_IDs of the groups or false on error
 * @author th
 */
 
// checked 
  
function grp_get_evts($grp_id) {
    global $kga, $conn;

    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $columns[]        = "evt_ID";
    $table = $kga['server_prefix']."grp_evt";

    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }

    $return_evts = array();
    $counter     = 0;

    $rows = $conn->RecordsArray(MYSQL_ASSOC);

    if ($conn->RowCount()) {
        foreach ($rows as $current_evt) {
            $return_evts[$counter] = $current_evt['evt_ID'];
            $counter++;   
        }
        return $return_evts;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Adds a new user
 *
 * @param array $data  username, email, and other data of the new user
 * @global array $kga  kimai-global-array
 * @return boolean     true on success, false on failure
 * @author th
 */
 
// checked (cleanup!!!)

function usr_create($data) {
    global $kga, $conn;
    
    $data = clean_data($data);

    $values ['usr_name']     =  MySQL::SQLValue($data ['usr_name']  );
    $values ['skin']         =  MySQL::SQLValue($data ['skin']      );
    $values ['rowlimit']     =  MySQL::SQLValue($data ['rowlimit']    , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_ID']       =  MySQL::SQLValue($data ['usr_ID']      , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_grp']      =  MySQL::SQLValue($data ['usr_grp']     , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_sts']      =  MySQL::SQLValue($data ['usr_sts']     , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_active']   =  MySQL::SQLValue($data ['usr_active']  , MySQL::SQLVALUE_NUMBER  );
                                                      
    $table  = $kga['server_prefix']."usr";
    $result = $conn->InsertRow($table, $values);


/*

$usr_name     =  MySQL::SQLValue($data ['usr_name']  );
$usr_mail     =  "";
$pw           =  "";
$skin         =  MySQL::SQLValue($data ['skin']      );
                                         
$rowlimit     =  MySQL::SQLValue($data ['rowlimit']    , MySQL::SQLVALUE_NUMBER  );
$usr_ID       =  MySQL::SQLValue($data ['usr_ID']      , MySQL::SQLVALUE_NUMBER  );
$usr_grp      =  MySQL::SQLValue($data ['usr_grp']     , MySQL::SQLVALUE_NUMBER  );
$usr_sts      =  MySQL::SQLValue($data ['usr_sts']     , MySQL::SQLVALUE_NUMBER  );
$usr_active   =  MySQL::SQLValue($data ['usr_active']  , MySQL::SQLVALUE_NUMBER  );
*/

/*
    
    $p = $kga['server_prefix'];

$query=<<<EOD
    INSERT INTO ${p}usr 
    (`usr_ID`,`usr_name`,`usr_grp`,`usr_sts`,`usr_active`,`rowlimit`,`skin`) VALUES
    ( $usr_ID,$usr_name,$usr_grp,$usr_sts,$usr_active,$rowlimit,$skin );
EOD;

    
    $result = $conn->Query($query);

*/


/*   
    logfile("create:".$query);
    logfile("create:".$result);
    
    logfile("create:".$values ['usr_name']   );
    logfile("create:".$values ['usr_mail']   );
    logfile("create:".$values ['pw']         );
    logfile("create:".$values ['skin']       );
                                             
    logfile("create:".$values ['rowlimit']   );
    logfile("create:".$values ['usr_ID']     );
    logfile("create:".$values ['usr_grp']    );
    logfile("create:".$values ['usr_sts']    );
    logfile("create:".$values ['usr_active'] );
*/




    if (! $result) {
    	return false;
    } else {
    	return true;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns the data of a certain user
 *
 * @param array $usr_id  knd_id of the user
 * @global array $kga    kimai-global-array
 * @return array         the user's data (username, email-address, status etc) as array, false on failure
 * @author th
 */

// checked 

function usr_get_data($usr_id) {
    global $kga, $conn;

    $filter['usr_ID'] = MySQL::SQLValue($usr_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."usr";
    $result = $conn->SelectRows($table, $filter);

    if (! $result) {
    	return false;
    } else {
        // return  $conn->getHTML();
        return $conn->RowArray(0,MYSQL_ASSOC);
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Edits a user by replacing his data by the new array
 *
 * @param array $usr_id  usr_id of the user to be edited
 * @param array $data    username, email, and other new data of the user
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author ob/th
 */
function usr_edit($usr_id, $data) {
    global $kga, $conn;
    
    $data = clean_data($data);
    
    if (! $conn->TransactionBegin()) $conn->Kill();
    
    $original_array = usr_get_data($usr_id);
    $new_array = array();
    
    foreach ($original_array as $key => $value) {
        if (isset($data[$key]) == true) {
            $new_array[$key] = $data[$key];
        } else {
            $new_array[$key] = $original_array[$key];
        }
    }

    $values ['usr_name']          = MySQL::SQLValue($new_array ['usr_name']  );
    $values ['usr_mail']          = MySQL::SQLValue($new_array ['usr_mail']  );
    $values ['usr_alias']          = MySQL::SQLValue($new_array ['usr_alias']  );
    $values ['pw']                = MySQL::SQLValue($new_array ['pw']        );
    $values ['skin']              = MySQL::SQLValue($new_array ['skin']      );
    $values ['lang']              = MySQL::SQLValue($new_array ['lang']      );
                                                               
    $values ['filter']            = MySQL::SQLValue($new_array ['filter']           , MySQL::SQLVALUE_NUMBER  );
    $values ['rowlimit']          = MySQL::SQLValue($new_array ['rowlimit']         , MySQL::SQLVALUE_NUMBER  );
    $values ['autoselection']     = MySQL::SQLValue($new_array ['autoselection']    , MySQL::SQLVALUE_NUMBER  );
    $values ['quickdelete']       = MySQL::SQLValue($new_array ['quickdelete']      , MySQL::SQLVALUE_NUMBER  );
    $values ['allvisible']        = MySQL::SQLValue($new_array ['allvisible']       , MySQL::SQLVALUE_NUMBER  );
    $values ['flip_pct_display']  = MySQL::SQLValue($new_array ['flip_pct_display'] , MySQL::SQLVALUE_NUMBER  );
    $values ['pct_comment_flag']  = MySQL::SQLValue($new_array ['pct_comment_flag'] , MySQL::SQLVALUE_NUMBER  );
    $values ['showIDs']           = MySQL::SQLValue($new_array ['showIDs']          , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_grp']           = MySQL::SQLValue($new_array ['usr_grp']          , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_sts']           = MySQL::SQLValue($new_array ['usr_sts']          , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_trash']         = MySQL::SQLValue($new_array ['usr_trash']        , MySQL::SQLVALUE_NUMBER  );
    $values ['usr_active']        = MySQL::SQLValue($new_array ['usr_active']       , MySQL::SQLVALUE_NUMBER  );

    $filter ['usr_ID']            = MySQL::SQLValue($usr_id, MySQL::SQLVALUE_NUMBER);
    
    $table = $kga['server_prefix']."usr";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);

    $success = true;

    if (! $conn->Query($query)) $success = false;

    if ($success) {
        if (! $conn->TransactionEnd()) $conn->Kill();
    } else {
        if (! $conn->TransactionRollback()) $conn->Kill();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * deletes a user
 *
 * @param array $usr_id  usr_id of the user
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author th
 */

function usr_delete($usr_id) {
    global $kga, $conn;
    
    $values['usr_trash'] = 1;    
    $filter['usr_ID'] = MySQL::SQLValue($usr_ID, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."usr";
        
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    return $conn->Query($query);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Assigns a leader to 1-n groups by adding entries to the cross table
 *
 * @param int $ldr_id        usr_id of the group leader to whom the groups will be assigned
 * @param array $grp_array    contains one or more grp_IDs
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob
 */
function assign_ldr2grps($ldr_id, $grp_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();    
    
    $table = $kga['server_prefix']."ldr";
    $filter['grp_leader'] = MySQL::SQLValue($ldr_id, MySQL::SQLVALUE_NUMBER);
    $query = MySQL::BuildSQLDelete($table, $filter);
    
    $d_result = $conn->Query($query);    
    
    if ($d_result == false) {
            $conn->TransactionRollback();
            return false;
    }
    
    foreach ($ldr_array as $current_grp) {
        
        $filter['grp_ID']     = MySQL::SQLValue($current_grp,  MySQL::SQLVALUE_NUMBER);
        $filter['grp_leader'] = MySQL::SQLValue($ldr_id,       MySQL::SQLVALUE_NUMBER);
        $query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($query);
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID']       = MySQL::SQLValue($current_grp , MySQL::SQLVALUE_NUMBER);
            $values['grp_leader']   = MySQL::SQLValue($ldr_id      , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);

            $result = $conn->Query($query);
            
            if ($result == false) {
                    $conn->TransactionRollback();
                    return false;
            }
        }
    }

    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Assigns a group to 1-n group leaders by adding entries to the cross table
 * (counterpart to assign_ldr2grp)
 * 
 * @param array $grp_id        grp_id of the group to which the group leaders will be assigned
 * @param array $ldr_array    contains one or more usr_ids of the leaders)
 * @global array $kga         kimai-global-array
 * @return boolean            true on success, false on failure
 * @author ob
 */
function assign_grp2ldrs($grp_id, $ldr_array) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();

    $table = $kga['server_prefix']."ldr";
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $query = MySQL::BuildSQLDelete($table, $filter);
    
    $d_result = $conn->Query($query);    
    
    if ($d_result == false) {
            $conn->TransactionRollback();
            return false;
    }
    
    foreach ($ldr_array as $current_ldr) {
        
        $filter['grp_ID']     = MySQL::SQLValue($grp_id,      MySQL::SQLVALUE_NUMBER);
        $filter['grp_leader'] = MySQL::SQLValue($current_ldr, MySQL::SQLVALUE_NUMBER);
        $query = MySQL::BuildSQLSelect($table, $filter);
        $conn->Query($query);
        
        if ($conn->RowCount() == 0) {
            $values['grp_ID']       = MySQL::SQLValue($grp_id      , MySQL::SQLVALUE_NUMBER);
            $values['grp_leader']   = MySQL::SQLValue($current_ldr , MySQL::SQLVALUE_NUMBER);
            $query = MySQL::BuildSQLInsert($table, $values);

            $result = $conn->Query($query);
            
            if ($result == false) {
                    $conn->TransactionRollback();
                    return false;
            }
        }
    }
    
    if ($conn->TransactionEnd() == true) {
        return true;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the groups of the given group leader
 *
 * @param array $ldr_id  usr_id of the group leader
 * @global array $kga    kimai-global-array
 * @return array         contains the grp_IDs of the groups or false on error
 * @author th
 */
function ldr_get_grps($ldr_id) {
    global $kga, $conn;
    
    $filter['grp_leader'] = MySQL::SQLValue($ldr_id, MySQL::SQLVALUE_NUMBER);
    $columns[]            = "grp_ID";
    $table = $kga['server_prefix']."ldr";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }
 
    $return_grps = array();
    $counter = 0;

    $rows = $conn->RowArray(0,MYSQL_ASSOC);
    
    if ($conn->RowCount()) {
        foreach ($rows as $current_grp) {
            $return_grps[$counter] = $current_grp['grp_ID'];
            $counter++;   
        }
        return $return_grps;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns all the group leaders of the given group
 *
 * @param array $grp_id  grp_id of the group
 * @global array $kga    kimai-global-array
 * @return array         contains the usr_IDs of the group's group leaders or false on error
 * @author th
 */
 
// checked 

function grp_get_ldrs($grp_id) {
    global $kga, $conn;
    
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $columns[]        = "grp_leader";
    $table = $kga['server_prefix']."ldr";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }
    
    $return_ldrs = array();
    $counter     = 0;
    
    $rows = $conn->RowArray(0,MYSQL_ASSOC);
    
    if ($conn->RowCount()) {
        $conn->MoveFirst();
        while (! $conn->EndOfSeek()) {
            $row = $conn->Row();
            $return_ldrs[$counter] = $row->grp_leader;
            $counter++; 
        }
        return $return_ldrs;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Adds a new group
 *
 * @param array $data  name and other data of the new group
 * @global array $kga  kimai-global-array
 * @return int         the grp_id of the new group, false on failure
 * @author th
 */
function grp_create($data) {
    global $kga, $conn;
    
    $data = clean_data($data);
    
    $values ['grp_name']   = MySQL::SQLValue($data ['grp_name'] );
    $values ['grp_leader'] = $kga['usr']['usr_ID'];
    $table = $kga['server_prefix']."grp";
    $result = $conn->InsertRow($table, $values);

    if (! $result) {
    	return false;
    } else {
    	return $conn->GetLastInsertID();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns the data of a certain group
 *
 * @param array $grp_id  grp_id of the group
 * @global array $kga    kimai-global-array
 * @return array         the group's data (name, leader ID, etc) as array, false on failure
 * @author th
 */
 
// checked  

function grp_get_data($grp_id) {
    global $kga, $conn;
    
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."grp";
    $result = $conn->SelectRows($table, $filter);    
    
    if (! $result) {
    	return false;
    } else {
        return $conn->RowArray(0,MYSQL_ASSOC);
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns the number of users in a certain group
 *
 * @param array $grp_id   grp_id of the group
 * @global array $kga     kimai-global-array
 * @return int            the number of users in the group
 * @author th
 */
 
// checked 

function grp_count_users($grp_id) {
    global $kga, $conn;
    $filter['usr_grp'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."usr";
    $result = $conn->SelectRows($table, $filter);
    return $conn->RowCount();
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Edits a group by replacing its data by the new array
 *
 * @param array $grp_id  grp_id of the group to be edited
 * @param array $data    name and other new data of the group
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author th
 */
function grp_edit($grp_id, $data) {
    global $kga, $conn;
    
    $data = clean_data($data);
        
    if (! $conn->TransactionBegin()) $conn->Kill();
    
    $original_array = grp_get_data($grp_id);
    $new_array = array();
    
    foreach ($original_array as $key => $value) {
        if (isset($data[$key]) == true) {
            $new_array[$key] = $data[$key];
        } else {
            $new_array[$key] = $original_array[$key];
        }
    }
   
    $values ['grp_name'] = MySQL::SQLValue($new_array ['grp_name'] );
    $filter ['grp_ID']   = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."grp";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
   
    $success = true;

    if (! $conn->Query($query)) $success = false;

    if ($success) {
        if (! $conn->TransactionEnd()) $conn->Kill();
    } else {
        if (! $conn->TransactionRollback()) $conn->Kill();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * deletes a group
 *
 * @param array $grp_id  grp_id of the group
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author th
 */
function grp_delete($grp_id) {
    global $kga, $conn;
    $values['grp_trash'] = 1;    
    $filter['grp_ID'] = MySQL::SQLValue($grp_id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."grp";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    return $conn->Query($query);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns all configuration variables
 *
 * @global array $kga  kimai-global-array
 * @return array       array with the vars from the var table
 * @author th
 */
 
// checked 

function var_get_data() {
    global $kga, $conn;

    $table = $kga['server_prefix']."var";
    $result = $conn->SelectRows($table);

    $var_data = array();

    $conn->MoveFirst();
    while (! $conn->EndOfSeek()) {
        $row = $conn->Row();
        $var_data[$row->var] = $row->value; 
    }

    return $var_data;
}

// -----------------------------------------------------------------------------------------------------------
// // Still under development!!! DO NOT USE YET!
/**
 * Edits a configuration variables by replacing the data by the new array
 *
 * @param array $data    variables array
 * @global array $kga    kimai-global-array
 * @return boolean       true on success, false on failure
 * @author ob
 */
function var_edit($data) {
    global $kga, $conn;
    
	$data = clean_data($data);
	
    $table = $kga['server_prefix']."var";
    
    if (! $conn->TransactionBegin()) $conn->Kill();
    
    $original_array = var_get_data();
    $new_array = array();
    
    foreach ($original_array as $key => $value) {
        if (isset($data[$key]) == true) {
            $new_array[$key] = $data[$key];
        } else {
            $new_array[$key] = $original_array[$key];
        }
    }

    foreach ($new_array as $current_var_key => $current_var_value) {
    
	    $filter['var'] = $current_var_key;
	    $values ['value'] = $current_var_value;

	    $query = MySQL::BuildSQLUpdate($table, $values, $filter);	

    	$result = $conn->Query($query);
        
        // $err = $pdo_query->errorInfo();
    
        if ($result == false) {
            return $result;
        }        
    }
    
    if (! $conn->TransactionEnd()) $conn->Kill();
    
    return true;
}

// -----------------------------------------------------------------------------------------------------------

/**
 * checks whether there is a running zef-entry for a given user
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return boolean true=there is an entry, false=there is none (actually 1 or 0 is returnes as number!)
 * @author ob/th
 */

// checked 

function get_rec_state($usr_id) {
    global $kga, $conn;
    $p = $kga['server_prefix'];
    $usr_id = MySQL::SQLValue($usr_id, MySQL::SQLVALUE_NUMBER);
    $conn->Query("SELECT * FROM ${p}zef WHERE zef_usrID = $usr_id AND zef_in > 0 AND zef_out = 0 LIMIT 1;");
    if ($conn->RowCount()) {
        return "1";
    } else {
        return "0";
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * validates the contents of the zef-table and marks them if there is a problem
 *
 * @global array $kga kimai-global-array
 * @return boolean true=everything okay, false=there was at least one issue
 * @author ob 
 */
function validate_zef() {
    global $kga, $conn;
    
    $return_state = true;    

    $p = $kga['server_prefix'];
	
    // Lock tables
    $lock  = "LOCK TABLE ${p}usr, ${p}zef;";
    $conn->Query($lock);

//------
    
    // case 1: scan for multiple running entries of the same user
    
    $query = "SELECT usr_ID FROM ${p}usr";
    $result = $conn->Query($query);

    $rows = $conn->RowArray(0,MYSQL_ASSOC);
    
    foreach ($rows as $row) {
		$usr_id = $row['usr_ID'];
        // echo $row['usr_ID'] . "<br>";
        $query_zef = "SELECT COUNT(*) FROM ${p}zef WHERE zef_usrID = $usr_id AND zef_in > 0 AND zef_out = 0;";

        $result_zef = $conn->Query($query_zef);
        $result_array_zef = $conn->RowArray(0,MYSQL_ASSOC);
        
        if ($result_array_zef[0] > 1) {
        
            $return_state = false;
        
            // echo "User " . $row['usr_ID'] . "has multiple running zef entries:<br>";
            
            $query_zef = "SELECT * FROM ${p}zef WHERE zef_usrID = $usr_id AND zef_in > 0 AND zef_out = 0;";
	        $result_zef = $conn->Query($query_zef);
			$rows_zef = $conn->RowArray(0,MYSQL_ASSOC);
            
            // mark all running-zef-entries with a comment (except the newest one)
            $query_zef_max = "SELECT MAX(zef_in), zef_ID FROM ${p}zef WHERE zef_usrID = $usr_id AND zef_in > 0 AND zef_out = 0 GROUP BY zef_ID;";
            $result_zef_max = $conn->Query($query_zef_max);

            $result_array_zef_max = $conn->RowArray(0,MYSQL_ASSOC);
            // $max_id = $result_array_zef_max['zef_ID'];
            $max_id = $result_array_zef_max->zef_ID;
            
            foreach ($rows_zef as $row_zef) {
            
                if($row_zef['zef_ID'] != $max_id) {
					$zef_id = $row_zef['zef_ID'];
                    $query_zef_edit = "UPDATE ${p}zef SET 
                    zef_comment = 'bad entry: multiple running entries found',
                    zef_comment_type = 2
                    WHERE zef_ID = $zef_id ;";

                    $result_zef_edit = $conn->Query($query_zef_edit); 
                    
                    // $err = $conn->errorInfo();
                    // error_log("ERROR: " . $err[2]);
                }
            
                // var_dump($row_zef);
                // echo "<br>";
            }
        }
    }
    
    // Unlock tables
    $unlock = "UNLOCK TABLE ${p}usr, ${p}zef ;";
    $conn->Query($unlock);
    
    return $return_state;
}

// -----------------------------------------------------------------------------------------------------------

/**
 * Returns the data of a certain time record
 *
 * @param array $zef_id  zef_id of the record
 * @global array $kga    kimai-global-array
 * @return array         the record's data (time, event id, project id etc) as array, false on failure
 * @author th
 */
 
// checked 

function zef_get_data($zef_id) {
    global $kga, $conn;
    
    $p = $kga['server_prefix'];
    
    $zef_id = MySQL::SQLValue($zef_id, MySQL::SQLVALUE_NUMBER);

    if ($zef_id) {
        $result = $conn->Query("SELECT * FROM ${p}zef WHERE zef_ID = " . $zef_id);
    } else {
        $result = $conn->Query("SELECT * FROM ${p}zef WHERE zef_usrID = ".$kga['usr']['usr_ID']." ORDER BY zef_ID DESC LIMIT 1");
    }
    
    if (! $result) {
    	return false;
    } else {
        return $conn->RowArray(0,MYSQL_ASSOC);
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * delete zef entry 
 *
 * @param integer $id -> ID of record
 * @global array  $kga kimai-global-array
 * @author th
 */
function zef_delete_record($id) {
    global $kga, $conn;
    $filter["zef_ID"] = MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."zef";
    $query = MySQL::BuildSQLDelete($table, $filter);
    return $conn->Query($query);
} 

// -----------------------------------------------------------------------------------------------------------

/**
 * create zef entry 
 *
 * @param integer $id    ID of record
 * @param integer $data  array with record data
 * @global array  $kga    kimai-global-array
 * @author th
 */
function zef_create_record($usr_ID,$data) {
    global $kga, $conn;
 
    $data = clean_data($data);
    
    $values ['zef_location']     =   MySQL::SQLValue( $data ['zlocation'] );
    $values ['zef_comment']      =   MySQL::SQLValue( $data ['comment'] );
    $values ['zef_trackingnr']   =   MySQL::SQLValue( $data ['trackingnr']   , MySQL::SQLVALUE_NUMBER );
    $values ['zef_usrID']        =   MySQL::SQLValue( $usr_ID                , MySQL::SQLVALUE_NUMBER );
    $values ['zef_pctID']        =   MySQL::SQLValue( $data ['pct_ID']       , MySQL::SQLVALUE_NUMBER );
    $values ['zef_evtID']        =   MySQL::SQLValue( $data ['evt_ID']       , MySQL::SQLVALUE_NUMBER );
    $values ['zef_comment_type'] =   MySQL::SQLValue( $data ['comment_type'] , MySQL::SQLVALUE_NUMBER );
    $values ['zef_in']           =   MySQL::SQLValue( $data ['in']           , MySQL::SQLVALUE_NUMBER );
    $values ['zef_out']          =   MySQL::SQLValue( $data ['out']          , MySQL::SQLVALUE_NUMBER );
    $values ['zef_time']         =   MySQL::SQLValue( $data ['diff']         , MySQL::SQLVALUE_NUMBER );
    
    $table = $kga['server_prefix']."zef";
    return $conn->InsertRow($table, $values);
    
} 

// -----------------------------------------------------------------------------------------------------------

/**
 * edit zef entry 
 *
 * @param integer $id ID of record
 * @global array $kga kimai-global-array
 * @param integer $data  array with new record data
 * @author th
 */
 
function zef_edit_record($id,$data) {
    global $kga, $conn;
    
    $data = clean_data($data);
   
    $original_array = zef_get_data($id);
    $new_array = array();
    
    foreach ($original_array as $key => $value) {
        if (isset($data[$key]) == true) {
            $new_array[$key] = $data[$key];
        } else {
            $new_array[$key] = $original_array[$key];
        }
    }

    $values ['zef_comment']      = MySQL::SQLValue($new_array ['zef_comment']                                );
    $values ['zef_location']     = MySQL::SQLValue($new_array ['zef_location']                                );
    $values ['zef_trackingnr']   = MySQL::SQLValue($new_array ['zef_trackingnr']    , MySQL::SQLVALUE_NUMBER );
    $values ['zef_pctID']        = MySQL::SQLValue($new_array ['zef_pctID']         , MySQL::SQLVALUE_NUMBER );
    $values ['zef_evtID']        = MySQL::SQLValue($new_array ['zef_evtID']         , MySQL::SQLVALUE_NUMBER );
    $values ['zef_comment_type'] = MySQL::SQLValue($new_array ['zef_comment_type']  , MySQL::SQLVALUE_NUMBER );
    $values ['zef_in']           = MySQL::SQLValue($new_array ['zef_in']            , MySQL::SQLVALUE_NUMBER );
    $values ['zef_out']          = MySQL::SQLValue($new_array ['zef_out']           , MySQL::SQLVALUE_NUMBER );
    $values ['zef_time']         = MySQL::SQLValue($new_array ['zef_time']          , MySQL::SQLVALUE_NUMBER );
                                   
    $filter ['zef_ID']           = MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."zef";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);

    $success = true;
    
    if (! $conn->Query($query)) $success = false;
    
    if ($success) {
        if (! $conn->TransactionEnd()) $conn->Kill();
    } else {
        if (! $conn->TransactionRollback()) $conn->Kill();
    }

    return $success;

    
    // $data['pct_ID']       
    // $data['evt_ID']       
    // $data['comment']      
    // $data['comment_type'] 
    // $data['erase']        
    // $data['in']           
    // $data['out']          
    // $data['diff']    
    
    // if wrong time values have been entered in the edit window
    // the following 3 variables arrive as zeros - like so:

    // $data['in']   = 0;
    // $data['out']  = 0;
    // $data['diff'] = 0;   
    
    // in this case the record has to be edited WITHOUT setting new time values
    

     // @oleg: ein zef-eintrag muss auch ohne die zeiten aktualisierbar sein weil die ggf. bei der prüfung durchfallen.

} 

// -----------------------------------------------------------------------------------------------------------

/**
 * saves timespace of user in database (table conf)
 *
 * @param string $timespace_in unix seconds
 * @param string $timespace_out unix seconds
 * @param string $user ID of user
 *
 * @author th
 */
function save_timespace($timespace_in,$timespace_out,$user) {
    global $kga, $conn;

    if ($timespace_in == 0 && $timespace_out == 0) {
        $mon = date("n"); $day = date("j"); $Y = date("Y"); 
        $timespace_in  = mktime(0,0,0,$mon,$day,$Y);
        $timespace_out = mktime(23,59,59,$mon,$day,$Y);
    }

    $values['timespace_in']  = MySQL::SQLValue($timespace_in  , MySQL::SQLVALUE_NUMBER );
    $values['timespace_out'] = MySQL::SQLValue($timespace_out , MySQL::SQLVALUE_NUMBER );

    $filter  ['usr_ID']          =   MySQL::SQLValue($user, MySQL::SQLVALUE_NUMBER);
    $table = $kga['server_prefix']."usr";
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    
    if (! $conn->Query($query)) $conn->Kill();
    return timespace_warning($timespace_in,$timespace_out);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns list of projects for specific group as array
 *
 * @param integer $user ID of user in database
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 */
 
// checked 

function get_arr_pct($group) {
    global $kga, $conn;
    
    $arr = array();
    $p = $kga['server_prefix'];

    if ($group == "all") {
        if ($kga['conf']['flip_pct_display']) {
            $query = "SELECT * FROM ${p}pct JOIN ${p}knd ON ${p}pct.pct_kndID = ${p}knd.knd_ID JOIN ${p}grp_pct ON ${p}grp_pct.pct_ID = ${p}pct.pct_ID ORDER BY knd_name,pct_name;";
        } else {
            $query = "SELECT * FROM ${p}pct JOIN ${p}knd ON ${p}pct.pct_kndID = ${p}knd.knd_ID JOIN ${p}grp_pct ON ${p}grp_pct.pct_ID = ${p}pct.pct_ID ORDER BY pct_name,knd_name;";
        }
    } else {
        $group = MySQL::SQLValue($group, MySQL::SQLVALUE_NUMBER);
        if ($kga['conf']['flip_pct_display']) {
            $query = "SELECT * FROM ${p}pct JOIN ${p}knd ON ${p}pct.pct_kndID = ${p}knd.knd_ID JOIN ${p}grp_pct ON ${p}grp_pct.pct_ID = ${p}pct.pct_ID WHERE ${p}grp_pct.grp_ID = $group ORDER BY knd_name,pct_name;";
        } else {                                                                                                                                                                                                                                                           
            $query = "SELECT * FROM ${p}pct JOIN ${p}knd ON ${p}pct.pct_kndID = ${p}knd.knd_ID JOIN ${p}grp_pct ON ${p}grp_pct.pct_ID = ${p}pct.pct_ID WHERE ${p}grp_pct.grp_ID = $group ORDER BY pct_name,knd_name;";
        }
    }
    
    $result = $conn->Query($query);
    if ($result == false) {
        return false;
    }
    
    $rows = $conn->RecordsArray(MYSQL_ASSOC);

    $arr = array();
    $i = 0;
    
    if (count($rows)) {
        foreach ($rows as $row) {
            $arr[$i]['pct_ID']      = $row['pct_ID'];
            $arr[$i]['pct_name']    = $row['pct_name'];
			$arr[$i]['pct_comment'] = $row['pct_comment'];
            $arr[$i]['knd_name']    = $row['knd_name'];
            $arr[$i]['knd_ID']      = $row['knd_ID'];
            $arr[$i]['pct_visible'] = $row['pct_visible'];
            $i++;
        }
        return $arr;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns list of projects for specific group and specific customer as array
 *
 * @param integer $user ID of user in database
 * @param integer $knd_id customer id
 * @global array $kga kimai-global-array
 * @return array
 * @author ob
 */
 
// checked 

function get_arr_pct_by_knd($group, $knd_id) {
    global $kga, $conn;
    
    $group   = MySQL::SQLValue($group  , MySQL::SQLVALUE_NUMBER);
    $knd_id  = MySQL::SQLValue($knd_id , MySQL::SQLVALUE_NUMBER);
    $p       = $kga['server_prefix'];
    


    if ($kga['conf']['flip_pct_display']) {
        $sort = "knd_name,pct_name";
    } else {
        $sort = "pct_name,knd_name";
    }  

    $query = "SELECT * FROM ${p}pct JOIN ${p}knd 
                       ON ${p}pct.pct_kndID = ${p}knd.knd_ID JOIN ${p}grp_pct 
                       ON ${p}grp_pct.pct_ID = ${p}pct.pct_ID 
                       WHERE ${p}grp_pct.grp_ID = $group 
                       AND ${p}pct.pct_kndID = $knd_id 
                       ORDER BY $sort ;";        
    
    $conn->Query($query);
    
    $arr = array();    
    $i=0;

    $conn->MoveFirst();
    while (! $conn->EndOfSeek()) {
        $row = $conn->Row();
        $arr[$i]['pct_ID']      = $row->pct_ID;
        $arr[$i]['pct_name']    = $row->pct_name;
        $arr[$i]['knd_name']    = $row->knd_name;
        $arr[$i]['knd_ID']      = $row->knd_ID;
        $arr[$i]['pct_visible'] = $row->pct_visible;
        $i++;
    }
    
    return $arr;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns timesheet for specific user as multidimensional array
 *
 * @param integer $user ID of user in table usr
 * @param integer $in start of timespace in unix seconds
 * @param integer $out end of timespace in unix seconds
 * @global array $kga kimai-global-array
 * @return array
 * @author th 
 */
 
// checked 

function get_arr_zef($user,$in,$out,$limit) {
    global $kga, $conn;
    
    $in    = MySQL::SQLValue($in    , MySQL::SQLVALUE_NUMBER);
    $out   = MySQL::SQLValue($out   , MySQL::SQLVALUE_NUMBER);
    $user  = MySQL::SQLValue($user  , MySQL::SQLVALUE_NUMBER);
    $limit = MySQL::SQLValue($limit , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
    
    $currTimespace = "AND zef_in > $in AND zef_out < $out";
    if ($limit) {
        if (isset($kga['conf']['rowlimit'])) {
            $limit = "LIMIT " .$kga['conf']['rowlimit'];
        } else {
            $limit="LIMIT 100";
        }
    } else {
        $limit="";
    }

    $query = "SELECT zef_ID, zef_in, zef_out, zef_time, zef_pctID, zef_evtID, zef_usrID, pct_ID, knd_name, pct_kndID, evt_name, pct_comment, pct_name, zef_location, zef_trackingnr, zef_comment, zef_comment_type, usr_alias
					 
              FROM ${p}zef 
              Join ${p}pct ON zef_pctID = pct_ID
              Join ${p}knd ON pct_kndID = knd_ID
              Join ${p}usr ON zef_usrID = usr_ID
              Join ${p}evt ON evt_ID    = zef_evtID
              WHERE zef_pctID > 0 AND zef_evtID > 0 AND zef_usrID = $user $currTimespace ORDER BY zef_in DESC $limit;";
    
    $conn->Query($query);
    
    $i=0;
    $arr=array();

    // if ($conn->RowCount()>0) {
        $conn->MoveFirst();
        while (! $conn->EndOfSeek()) {
            $row = $conn->Row();
            $arr[$i]['zef_ID']           = $row->zef_ID;
            $arr[$i]['zef_in']           = $row->zef_in;
            $arr[$i]['zef_out']          = $row->zef_out;
            $arr[$i]['zef_time']         = $row->zef_time;
            $arr[$i]['zef_apos']         = intervallApos($row->zef_time);
            $arr[$i]['zef_coln']         = intervallColon($row->zef_time);
            $arr[$i]['zef_pctID']        = $row->zef_pctID;
            $arr[$i]['zef_evtID']        = $row->zef_evtID;
            $arr[$i]['zef_usrID']        = $row->zef_usrID;
            $arr[$i]['pct_ID']           = $row->pct_ID;
            $arr[$i]['knd_name']         = $row->knd_name;
            $arr[$i]['pct_kndID']        = $row->pct_kndID;
            $arr[$i]['evt_name']         = $row->evt_name;
            $arr[$i]['pct_name']         = $row->pct_name;
            $arr[$i]['pct_comment']      = $row->pct_comment;
            $arr[$i]['zef_location']     = $row->zef_location;
            $arr[$i]['zef_trackingnr']   = $row->zef_trackingnr;
            $arr[$i]['zef_comment']      = $row->zef_comment;
            $arr[$i]['zef_comment_type'] = $row->zef_comment_type;
            $i++;
        }
        return $arr;
    // } else {
        // return false;
    // }
}


//-----------------------------------------------------------------------------------------------------------

/**
 * checks if user is logged on and returns user information as array
 * kicks client if is not verified
 * TODO: this and get_config should be one function
 *
 * <pre>
 * returns: 
 * [usr_ID] user ID, 
 * [usr_sts] user status (rights), 
 * [usr_grp] group of user, 
 * [usr_name] username 
 * </pre>
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return array
 * @author th 
 */

// seems to be ok  

function checkUser() {
    global $kga, $conn;

	$p = $kga['server_prefix'];
    
    if (!$kga['virtual_users']) {
        
        if (isset($_COOKIE['kimai_usr']) && isset($_COOKIE['kimai_key']) && $_COOKIE['kimai_usr'] != "0" && $_COOKIE['kimai_key'] != "0") {
            $kimai_usr = addslashes($_COOKIE['kimai_usr']);
            $kimai_key = addslashes($_COOKIE['kimai_key']);
            
            if (get_seq($kimai_usr) != $kimai_key) {
                kickUser();
            } else {
                $query = "SELECT usr_ID,usr_sts,usr_grp FROM ${p}usr WHERE usr_name = '$kimai_usr' AND usr_active = '1' AND NOT usr_trash = '1';";
                $conn->Query($query);
                $row = $conn->RowArray(0,MYSQL_ASSOC);
                
                $usr_ID   = $row['usr_ID'];
                $usr_sts  = $row['usr_sts']; // User Status -> 0=Admin | 1=GroupLeader | 2=User
                $usr_grp  = $row['usr_grp'];
                $usr_name = $kimai_usr;
                
                if ($usr_ID < 1) {
                    kickUser();
                }
            }
            
        } else {
            kickUser();
        }

    } else {
        $usr_ID   = $_SESSION['user']; 
        $usr_grp  = $_SESSION['user'];  
        $usr_name = $_SESSION['user'];  
        $usr_sts  = 2; 
    }
    
    if ($usr_ID<1) {
        kickUser();
    }
    
    $usr = array(
        "usr_ID"=>$usr_ID,
        "usr_sts"=>$usr_sts,
        "usr_grp"=>$usr_grp,
        "usr_name"=>$usr_name
    );
    
    return $usr;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * write global configuration AND details of a specific user into $kga
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return array $kga 
 * @author th
 *
 */

// seems to be ok 

function get_config($user) {
    global $kga, $conn;
        
        if ($user) {
            
            $table = $kga['server_prefix']."usr";
            $filter['usr_ID'] = MySQL::SQLValue($user, MySQL::SQLVALUE_NUMBER);
            
            // get values from user record
            $columns[] = "usr_ID";
            $columns[] = "usr_name";
            $columns[] = "usr_grp";
            $columns[] = "usr_sts";
            $columns[] = "usr_trash";
            $columns[] = "usr_active";
            $columns[] = "usr_mail";
            $columns[] = "pw";
            $columns[] = "ban";
            $columns[] = "banTime";
            $columns[] = "secure";

            $conn->SelectRows($table, $filter, $columns);
            $rows = $conn->RowArray(0,MYSQL_ASSOC);
            foreach($rows as $key => $value) {
                $kga['usr'][$key] = $value;
            } 
            
            // get values from user configuration (user-preferences)
            unset($columns);
            $columns[] = "rowlimit"; 
            $columns[] = "skin"; 
            $columns[] = "lastProject"; 
            $columns[] = "lastEvent"; 
            $columns[] = "lastRecord"; 
            $columns[] = "filter"; 
            $columns[] = "filter_knd"; 
            $columns[] = "filter_pct"; 
            $columns[] = "filter_evt"; 
            $columns[] = "view_knd"; 
            $columns[] = "view_pct"; 
            $columns[] = "view_evt"; 
            $columns[] = "zef_anzahl"; 
            $columns[] = "timespace_in"; 
            $columns[] = "timespace_out"; 
            $columns[] = "autoselection"; 
            $columns[] = "quickdelete"; 
            $columns[] = "allvisible"; 
            $columns[] = "flip_pct_display"; 
            $columns[] = "showIDs"; 
            $columns[] = "pct_comment_flag"; 
            $columns[] = "lang"; 

            $conn->SelectRows($table, $filter, $columns);
            $rows = $conn->RowArray(0,MYSQL_ASSOC);
            foreach($rows as $key => $value) {
                $kga['conf'][$key] = $value;
            } 
        }
        
        // get values from global configuration 
        $table = $kga['server_prefix']."var";
        $conn->SelectRows($table);
        
        $conn->MoveFirst();
        while (! $conn->EndOfSeek()) {
            $row = $conn->Row();
            $kga['conf'][$row->var] = $row->value;
        }
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns ID of running timesheet event for specific user
 *
 * <pre>
 * ['zef_ID'] ID of last recorded task
 * ['zef_in'] in point of timesheet record in unix seconds
 * ['zef_pctID']
 * ['zef_evtID']
 * </pre>
 *
 * @param integer $user ID of user in table usr <------ NOT TRUE - gets the last record from the usr-conf! - FIXME
 * @global array $kga kimai-global-array
 * @return integer
 * @author th
 */
 
// checked 

function get_event_last($user) {
    global $kga, $conn;
    
    $user  = MySQL::SQLValue($user , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
    
    $lastRecord = $kga['conf']['lastRecord'];
    
    $query = "SELECT zef_ID,zef_in,zef_pctID,zef_evtID FROM ${p}zef WHERE zef_ID = $lastRecord ;";

    $conn->Query($query);
    return $conn->RowArray(0,MYSQL_ASSOC);
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns single timesheet entry as array
 *
 * @param integer $id ID of entry in table zef
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 */

// checked 

function get_entry_zef($id) {
    global $kga, $conn;

    $id    = MySQL::SQLValue($id   , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
	
    $query = "SELECT * FROM ${p}zef 
              Left Join ${p}pct ON zef_pctID = pct_ID 
              Left Join ${p}knd ON pct_kndID = knd_ID 
              Left Join ${p}evt ON evt_ID    = zef_evtID
              WHERE zef_ID = $id LIMIT 1;";

    $conn->Query($query);
    return $conn->RowArray(0,MYSQL_ASSOC);
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns time summary of current timesheet
 *
 * @param integer $user ID of user in table usr
 * @param integer $in start of timespace in unix seconds
 * @param integer $out end of timespace in unix seconds
 * @global array $kga kimai-global-array
 * @return integer
 * @author th 
 */
 
// checked 
 
function get_zef_time($user,$in,$out) {
    global $kga, $conn;

    $in    = MySQL::SQLValue($in   , MySQL::SQLVALUE_NUMBER);
    $out   = MySQL::SQLValue($out  , MySQL::SQLVALUE_NUMBER);
    $user  = MySQL::SQLValue($user , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
    
    $query = "SELECT SUM(`zef_time`) AS zeit FROM ${p}zef WHERE zef_usrID = $user AND zef_in > $in AND zef_out < $out ;";
    $conn->Query($query);

    $row = $conn->RowArray(0,MYSQL_ASSOC);
    $zeit = $row['zeit'];
    return $zeit;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns list of customers in a group as array
 *
 * @param integer $group ID of group in table grp or "all" for all groups
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 */

// checked 

function get_arr_knd($group) {
    global $kga, $conn;

	$p = $kga['server_prefix'];
           

    if ($group == "all") {
        $query = "SELECT * FROM ${p}knd  ORDER BY knd_name;";
    } else {
        $group = MySQL::SQLValue($group , MySQL::SQLVALUE_NUMBER); 
        $query = "SELECT * FROM ${p}knd JOIN ${p}grp_knd ON `${p}grp_knd`.`knd_ID`=`${p}knd`.`knd_ID` WHERE `${p}grp_knd`.`grp_ID` = $group ORDER BY knd_name;";
    }
    
    $result = $conn->Query($query);
    if ($result == false) {
        return false;
    }

    $arr = array();
    $i = 0;
    if ($conn->RowCount()) {
        $conn->MoveFirst();
        while (! $conn->EndOfSeek()) {
            $row = $conn->Row();
            $arr[$i]['knd_ID']       = $row->knd_ID;   
            $arr[$i]['knd_name']     = $row->knd_name;
            $arr[$i]['knd_visible']  = $row->knd_visible;
            $i++;
        }
        return $arr;
    } else {
        return false;
    }
}


//-----------------------------------------------------------------------------------------------------------

/**
 * returns list of time summary attached to customer ID's within specific timespace as array
 *
 * @param integer $user ID of user in table usr
 * @param integer $in start of timespace in unix seconds
 * @param integer $out end of timespace in unix seconds
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 */

// checked

function get_arr_time_knd($user,$in,$out) {
    global $kga, $conn;
    
    $in    = MySQL::SQLValue($in   , MySQL::SQLVALUE_NUMBER);
    $out   = MySQL::SQLValue($out  , MySQL::SQLVALUE_NUMBER);
    $user  = MySQL::SQLValue($user , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
    
    if ($in) $zeitraum = "AND zef_in > $in";
    if ($in && $out) $zeitraum = "AND zef_in > $in AND zef_in < $out";
    
    $query = "SELECT SUM(zef_time) as zeit, knd_ID FROM ${p}zef 
              Left Join ${p}pct ON zef_pctID = pct_ID
              Left Join ${p}knd ON pct_kndID = knd_ID 
              WHERE zef_usrID = $user $zeitraum 
              GROUP BY knd_ID;";
              
    $conn->Query($query);
    
    $arr = array();  
    $rows = $conn->RecordsArray(MYSQL_ASSOC);
    if(is_array($rows) && sizeof($rows) > 0)
    {
	foreach($rows as $row) {
        	$arr[$row['knd_ID']] = $row['zeit'];
    	}
    }
    return $arr;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns list of time summary attached to project ID's within specific timespace as array
 *
 * @param integer $user ID of user in table usr
 * @param integer $in start time in unix seconds
 * @param integer $out end time in unix seconds
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 */
 
// checked

function get_arr_time_pct($user,$in,$out) {
    global $kga, $conn;

    $in    = MySQL::SQLValue($in   , MySQL::SQLVALUE_NUMBER);
    $out   = MySQL::SQLValue($out  , MySQL::SQLVALUE_NUMBER);
    $user  = MySQL::SQLValue($user , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
    
    if ($in) $zeitraum = "AND zef_in > $in";
    if ($in && $out) $zeitraum = "AND zef_in > $in AND zef_in < $out";

    $query = "SELECT sum(zef_time) as zeit,zef_pctID FROM ${p}zef WHERE zef_usrID = $user $zeitraum GROUP BY zef_pctID;";
    
    $conn->Query($query);

    $i=0;
    $arr = array();  
    $conn->MoveFirst();
    while (! $conn->EndOfSeek()) {
        $row = $conn->Row();
        $arr[$row->zef_pctID] = $row->zeit;
        $i++;
    }
    
    return $arr;
}

//-----------------------------------------------------------------------------------------------------------

## Load into Array: Events 

// checked
 
function get_arr_evt($group) {
    global $kga, $conn;
	
	$p = $kga['server_prefix']; 

    if ($group == "all") {
        $query = "SELECT * FROM ${p}evt ORDER BY evt_name;";
    } else {
        $group = MySQL::SQLValue($group , MySQL::SQLVALUE_NUMBER); 
        $query = "SELECT * FROM ${p}evt JOIN ${p}grp_evt ON `${p}grp_evt`.`evt_ID`=`${p}evt`.`evt_ID` WHERE `${p}grp_evt`.`grp_ID` = $group  ORDER BY evt_name;";
    }
    
    $result = $conn->Query($query);
    if ($result == false) {
        return false;
    }

    $arr = array();
    $i = 0;
    if ($conn->RowCount()) {
        $conn->MoveFirst();
        while (! $conn->EndOfSeek()) {
            $row = $conn->Row();
            $arr[$i]['evt_ID']       = $row->evt_ID;   
            $arr[$i]['evt_name']     = $row->evt_name;
            $arr[$i]['evt_visible']  = $row->evt_visible;
            $i++;
        }
        return $arr;
    } else {
        return false;
    }
}

//-----------------------------------------------------------------------------------------------------------

## EVT time-sum

// checked

function get_arr_time_evt($user,$in,$out) {
    global $kga, $conn;

    $in    = MySQL::SQLValue($in   , MySQL::SQLVALUE_NUMBER);
    $out   = MySQL::SQLValue($out  , MySQL::SQLVALUE_NUMBER);
    $user  = MySQL::SQLValue($user , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
    
    if ($in) $zeitraum="AND zef_in > $in";
    if ($in && $out) $zeitraum="AND zef_in > $in AND zef_in < $out";

    $query = "SELECT sum(zef_time) as zeit,zef_evtID FROM ${p}zef WHERE zef_usrID = $user $zeitraum GROUP BY zef_evtID;";
    
    $conn->Query($query);
    
    $arr = array();  
    if($rows = $conn->RecordsArray(MYSQL_ASSOC))
    {
	foreach($rows as $row) {
     	   $arr[$row['zef_evtID']] = $row['zeit'];
	    }
	   }
    return $arr;
}

//-----------------------------------------------------------------------------------------------------------

## Load into Array: Events with attached time-sums

// checked

function get_arr_evt_with_time($group,$user,$in,$out) {
    global $kga, $conn;
    
    $arr_evts = get_arr_evt($group);
    $arr_time = get_arr_time_evt($user,$in,$out);
    
    $arr = array(); 
    $i=0;
    foreach ($arr_evts as $evt) {
        $arr[$i]['evt_ID']      = $evt['evt_ID'];
        $arr[$i]['evt_name']    = $evt['evt_name'];
        $arr[$i]['evt_visible'] = $evt['evt_visible'];
        if (isset($arr_time[$evt['evt_ID']])) $arr[$i]['zeit'] = intervallApos($arr_time[$evt['evt_ID']]);
        else $arr[$i]['zeit']   = intervallApos(0);
        $i++;
    }
    return $arr;
}

//-----------------------------------------------------------------------------------------------------------

## Load into Array: Customers with attached time-sums

// checked

function get_arr_knd_with_time($group,$user,$in,$out) {
    global $kga, $conn;
    
    $arr_knds = get_arr_knd($group);
    $arr_time = get_arr_time_knd($user,$in,$out);
    
    $arr = array(); 
    $i=0;
    foreach ($arr_knds as $knd) {
        $arr[$i]['knd_ID']      = $knd['knd_ID'];
        $arr[$i]['knd_name']    = $knd['knd_name'];
        $arr[$i]['knd_visible'] = $knd['knd_visible'];
        if (isset($arr_time[$knd['knd_ID']])) $arr[$i]['zeit'] = intervallApos($arr_time[$knd['knd_ID']]);
        else $arr[$i]['zeit']   = intervallApos(0);
        $i++;
    }
    return $arr;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns time of currently running event recording as array
 *
 * result is meant as params for the stopwatch if the window is reloaded
 *
 * <pre>
 * returns:
 * [all] start time of entry in unix seconds (forgot why I named it this way, sorry ...)
 * [hour]
 * [min]
 * [sec]
 * </pre>
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 */

// checked
 
function get_current_timer() {
    global $kga, $conn;
    
    $user  = MySQL::SQLValue($kga['usr']['usr_ID'] , MySQL::SQLVALUE_NUMBER);
	$p     = $kga['server_prefix'];
        
    $conn->Query("SELECT zef_ID,zef_in,zef_time FROM ${p}zef WHERE zef_usrID = $user ORDER BY zef_in DESC LIMIT 1;");
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);

    $zef_time  = (int)$row['zef_time'];
    $zef_in    = (int)$row['zef_in'];

    if (!$zef_time && $zef_in) {
        $aktuelleMessung = hourminsec(time()-$zef_in);
        $current_timer['all']  = $zef_in;
        $current_timer['hour'] = $aktuelleMessung['h'];
        $current_timer['min']  = $aktuelleMessung['i'];
        $current_timer['sec']  = $aktuelleMessung['s'];
    } else {
        $current_timer['all']  = 0;
        $current_timer['hour'] = 0;
        $current_timer['min']  = 0;
        $current_timer['sec']  = 0;
    }
    return $current_timer;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns the total worktime of a zef_entry day
 *
 * WARNING: $inPoint has to be *exactly* the first second of the day 
 *
 * @param integer $inPoint begin of the day in unix seconds
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return string
 * @author th 
 */
 
// checked

function get_zef_time_day($inPoint,$user) {
    global $kga, $conn;

    $p = $kga['server_prefix'];
    $inPoint = MySQL::SQLValue($inPoint, MySQL::SQLVALUE_NUMBER);
    $user    = MySQL::SQLValue($user   , MySQL::SQLVALUE_NUMBER);
            
    $outPoint=$inPoint+86399;
    
    $conn->Query("SELECT sum(zef_time) as zeit FROM ${p}zef WHERE zef_in > $inPoint AND zef_out < $outPoint AND zef_usrID = $user ;");
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['zeit'];
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns the total worktime of a zef_entry month
 *
 * WARNING: $inPoint has to be *exactly* the first second of any day in the wanted month 
 *
 * @param integer $inPoint begin of one day of desired month in unix seconds
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return string
 * @author th 
 */
 
// checked

function get_zef_time_mon($inPoint,$user) {
    global $kga, $conn;
    
    $inPoint = MySQL::SQLValue($inPoint, MySQL::SQLVALUE_NUMBER);
    $user    = MySQL::SQLValue($user   , MySQL::SQLVALUE_NUMBER);
    $p       = $kga['server_prefix'];
    
    $inDatum_m = date("m",$inPoint);
    $inDatum_Y = date("Y",$inPoint);
    $inDatum_t = date("t",$inPoint);
    
    $inPoint  = mktime(0,0,0,$inDatum_m,1,$inDatum_Y);
    $outPoint = mktime(23,59,59,$inDatum_m,$inDatum_t,$inDatum_Y);

    $conn->Query("SELECT sum(zef_time) as zeit FROM ${p}zef WHERE zef_in > $inPoint AND zef_out < $outPoint AND zef_usrID = $user ;");

    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['zeit'];
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns the total worktime in database
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return string
 * @author th 
 */
 
// checked

function get_zef_time_all($user) {
    global $kga, $conn;
    
    $user = MySQL::SQLValue($user, MySQL::SQLVALUE_NUMBER);
    $p    = $kga['server_prefix'];
        
    $conn->Query("SELECT sum(zef_time) as zeit FROM ${p}zef WHERE zef_usrID = $user ;");

    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['zeit'];    
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns the total worktime of a zef_entry year
 *
 * @param integer $year 4 digit year (not sure yet if 2 digits work...)
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return string
 * @author th 
 */
 
// checked

function get_zef_time_year($year,$user) {
    global $kga, $conn;
    
    $user = MySQL::SQLValue($user, MySQL::SQLVALUE_NUMBER);
    $year = MySQL::SQLValue($year, MySQL::SQLVALUE_NUMBER);
    $p = $kga['server_prefix'];
        
    $in  = (int)mktime(0,0,0,1,1,$year); 
    $out = (int)mktime(23,59,59,12,(int)date("t"),$year);
    
    $conn->Query("SELECT sum(zef_time) as zeit FROM ${p}zef WHERE zef_in > $in AND zef_out < $out AND zef_usrID = $user ;");

    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['zeit'];
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns the version of the installed Kimai database to compare it with the package version
 *
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 *
 * [0] => version number (x.x.x)
 * [1] => svn revision number
 *
 */

// checked

function get_DBversion() {
    global $kga, $conn;
    
    $filter['var'] = MySQL::SQLValue('version');
    $columns[] = "value";
    $table = $kga['server_prefix']."var";
    $result = $conn->SelectRows($table, $filter, $columns);
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);
    $return[] = $row['value'];  
    
    if ($result == false) $return[0] = "0.5.1";
    
    $filter['var'] = MySQL::SQLValue('revision');
    $result = $conn->SelectRows($table, $filter, $columns);
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);
    $return[] = $row['value'];
    
    return $return;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns the key for the session of a specific user 
 *
 * the key is both stored in the database (usr table) and a cookie on the client. 
 * when the keys match the user is allowed to access the Kimai GUI. 
 * match test is performed via function userCheck()
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return string
 * @author th 
 */
 
// checked 

function get_seq($user) {
    global $kga, $conn;
    
    $filter['usr_name'] = MySQL::SQLValue($user);
    $columns[] = "secure";
    $table = $kga['server_prefix']."usr";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['secure'];
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns array of all users 
 *
 * [usr_ID] => 23103741
 * [usr_name] => admin
 * [usr_grp] => 1
 * [usr_sts] => 0
 * [grp_name] => miesepriem
 * [usr_mail] => 0
 * [usr_active] => 0
 *
 *
 * @global array $kga kimai-global-array
 * @return array
 * @author th 
 */
 
// checked

function get_arr_usr($trash=0) {
    global $kga, $conn;
    
    $p = $kga['server_prefix'];
        
    
    if (!$trash) {
        $trashoption = "WHERE usr_trash !=1";
    } else {
        $trashoption = "";
    }
    
    $query = "SELECT * FROM ${p}usr Left Join ${p}grp ON usr_grp = grp_ID $trashoption ORDER BY usr_name ;";
    $conn->Query($query);

    $rows = $conn->RowArray(0,MYSQL_ASSOC);
    
    $i=0;
    $arr = array();

    $conn->MoveFirst();
    while (! $conn->EndOfSeek()) {
        $row = $conn->Row();
        $arr[$i]['usr_ID']     = $row->usr_ID;
        $arr[$i]['usr_name']   = $row->usr_name;
        $arr[$i]['usr_grp']    = $row->usr_grp;
        $arr[$i]['usr_sts']    = $row->usr_sts;
        $arr[$i]['grp_name']   = $row->grp_name;
        $arr[$i]['usr_mail']   = $row->usr_mail;
        $arr[$i]['usr_active'] = $row->usr_active;
        $arr[$i]['usr_trash']  = $row->usr_trash;
        
        if ($row->pw !='' && $row->pw != '0') {
            $arr[$i]['usr_pw'] = "yes"; 
        } else {                 
            $arr[$i]['usr_pw'] = "no"; 
        }
        $i++;
    }

    return $arr;
}

//-----------------------------------------------------------------------------------------------------------

/**
 * returns array of all groups 
 *
 * [0]=> array(6) {
 *      ["grp_ID"]      =>  string(1) "1" 
 *      ["grp_name"]    =>  string(5) "admin" 
 *      ["grp_leader"]  =>  string(9) "1234" 
 *      ["grp_trash"]   =>  string(1) "0" 
 *      ["count_users"] =>  string(1) "2" 
 *      ["leader_name"] =>  string(5) "user1" 
 * } 
 * 
 * [1]=> array(6) { 
 *      ["grp_ID"]      =>  string(1) "2" 
 *      ["grp_name"]    =>  string(4) "Test" 
 *      ["grp_leader"]  =>  string(9) "12345" 
 *      ["grp_trash"]   =>  string(1) "0" 
 *      ["count_users"] =>  string(1) "1" 
 *      ["leader_name"] =>  string(7) "user2" 
 *  } 
 *
 * @global array $kga kimai-global-array
 * @return array
 * @author th 
 *
 */
 
// fail! 
 
function get_arr_grp($trash=0) {
    global $kga, $conn;
    
    $p = $kga['server_prefix'];

    // Lock tables
    $lock  = "LOCK TABLE ${p}usr, ${p}grp;";
    $conn->Query($lock);

//------

    if (!$trash) {
        $trashoption = "WHERE grp_trash !=1";
    } 

    $query  = "SELECT * FROM ${p}grp $trashoption ORDER BY grp_name;";
    $conn->Query($query);

    // rows into array
    $groups = array();
    $i=0;
    
    $rows = $conn->RecordsArray(MYSQL_ASSOC);

    foreach ($rows as $row){
        $groups[] = $row;

        // append user count
        $groups[$i]['count_users'] = grp_count_users($row['grp_ID']);
        
        logfile($row['grp_ID']);

        // append leader array
        $ldr_id_array = grp_get_ldrs($row['grp_ID']);
        
        $j = 0;
        foreach ($ldr_id_array as $ldr_id) {
            $ldr_name_array[$j] = usr_id2name($ldr_id);
            $j++;
        }
        
        $groups[$i]['leader_name'] = $ldr_name_array;

        $i++;
    }

//------

    // Unlock tables
    $unlock = "UNLOCK TABLE ${p}usr, ${p}grp;";
    $conn->Query($unlock);
    
    return $groups;    
}

//-----------------------------------------------------------------------------------------------------------

/**
 * performed when the stop buzzer is hit.
 * Checks which record is currently recording and
 * writes the end time into that entry.
 * if the measured timevalue is longer than one calendar day
 * it is split up and stored in the DB by days
 *
 * @global array $kga kimai-global-array
 * @param integer $user ID of user
 * @author th 
 *
 */
 
// checked
 
function stopRecorder() {
## stop running recording |
    global $kga, $conn;
    
    $table = $kga['server_prefix']."zef";

    $last_task        = get_event_last($kga['usr']['usr_ID']); // aktuelle vorgangs-ID auslesen
    
    $filter['zef_ID'] = $last_task['zef_ID'];
    $values['zef_in'] = $last_task['zef_in'];
    
    // ...in-zeitpunkt und jetzt-zeitpunkt werden an den EXPLODER gesendet
    // der daraus ein mehrdimensionales array macht. die tage dieses arrays
    // werden anschließend in die DB zurückgeschreiben
    $records = explode_record($values['zef_in'],$kga['now']);
    
    $values['zef_out']  = $records[0]['out'];
    $values['zef_time'] = $records[0]['diff'];

    // hier wird sofort mal der erste ausgeworfene tag verarbeitet.
    // wenn nur einer zurückgekommen ist, ist die verarbeitung danach direkt
    // beendet.
    // zeitdifferenz und outPoint in laufendem vorgang speichern
    
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    
    logfile($query);
    
    return $conn->Query($query);    

    // noch mehr tage?
    if (count($records)>1) {
        save_further_records($user,$last_task,$records);
    }
}

//-----------------------------------------------------------------------------------------------------------

function save_further_records($user,$last_task,$records) {
    global $kga, $conn;

    // nur der zweite eintrag wird zusätzlich gespeichert
    // TODO: schleife für alle einträge
  
    if (count($records)>2) {
        $values['zef_comment']      = $kga['lang']['ac_error']; // auto continued with error (entry too long).";
        $values['zef_comment_type'] = 2;
    } else {
        $values['zef_comment']      = $kga['lang']['ac']; // "auto continued.";
        $values['zef_comment_type'] = 1;
    }

    $values['zef_in']    = MySQL::SQLValue($records[1]['in']      , MySQL::SQLVALUE_NUMBER);
    $values['zef_out']   = MySQL::SQLValue($records[1]['out']     , MySQL::SQLVALUE_NUMBER);
    $values['zef_time']  = MySQL::SQLValue($records[1]['diff']    , MySQL::SQLVALUE_NUMBER);
    $values['zef_usrID'] = MySQL::SQLValue($user                  , MySQL::SQLVALUE_NUMBER);
    $values['zef_pctID'] = MySQL::SQLValue($last_task['zef_pctID'], MySQL::SQLVALUE_NUMBER);
    $values['zef_evtID'] = MySQL::SQLValue($last_task['zef_evtID'], MySQL::SQLVALUE_NUMBER);
    
    $table = $kga['server_prefix']."zef";
    $query = MySQL::BuildSQLInsert($table, $values);
    return $conn->Query($query);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * starts timesheet record
 *
 * @param integer $pct_ID ID of project to record
 * @global array $kga kimai-global-array
 * @author th
 */
 
// seems to work fine

function startRecorder($pct_ID,$evt_ID,$user) {
    global $kga, $conn;
    
    if (! $conn->TransactionBegin()) $conn->Kill();
    
    $pct_ID = MySQL::SQLValue($pct_ID, MySQL::SQLVALUE_NUMBER  );
    $evt_ID = MySQL::SQLValue($evt_ID, MySQL::SQLVALUE_NUMBER  );
    $user   = MySQL::SQLValue($user  , MySQL::SQLVALUE_NUMBER  );
        
    $values ['zef_pctID'] = $pct_ID;
    $values ['zef_evtID'] = $evt_ID;
    $values ['zef_in']    = $kga['now'];
    $values ['zef_usrID'] = $user;
    
    $table = $kga['server_prefix']."zef";
    $result = $conn->InsertRow($table, $values);
    
    if (! $result) {
    	return false;
    } 
    
    unset($values);
    $values ['lastRecord'] = $conn->GetLastInsertID();
    $table = $kga['server_prefix']."usr";
    $filter  ['usr_ID'] = $user;
    $query = MySQL::BuildSQLUpdate($table, $values, $filter);
    
    $success = true;
    
    if (! $conn->Query($query)) $success = false;
    
    if ($success) {
        if (! $conn->TransactionEnd()) $conn->Kill();
    } else {
        if (! $conn->TransactionRollback()) $conn->Kill();
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * return details of specific user
 * DEPRICATED!!
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return array
 * @author th 
 */

// checked

function get_usr($usr_id) {
    global $kga, $conn;
    
    $p = $kga['server_prefix'];
    
    $usr_id = MySQL::SQLValue($usr_id, MySQL::SQLVALUE_NUMBER);
    $prefix = $kga['server_prefix'];
        
    $query = "SELECT * FROM ${p}usr Left Join ${p}grp ON usr_grp = grp_ID WHERE usr_ID = $usr_id LIMIT 1;";
    $result = $conn->Query($query);
    if ($result == false) {
        return false;
    }

    $row = $conn->RowArray(0,MYSQL_ASSOC);

    $arr['usr_ID']     = $row['usr_ID'];
    $arr['usr_name']   = $row['usr_name'];
    $arr['usr_grp']    = $row['usr_grp'];
    $arr['usr_sts']    = $row['usr_sts'];
    $arr['grp_name']   = $row['grp_name'];
    $arr['usr_mail']   = $row['usr_mail'];
    $arr['usr_active'] = $row['usr_active'];
    
    if ($row['pw']!=''&&$row['pw']!='0') {
        $arr['usr_pw'] = "yes"; 
    } else {                 
        $arr['usr_pw'] = "no"; 
    }
       
    return $arr;
}

// -----------------------------------------------------------------------------------------------------------

/**
 * return ID of specific user named 'XXX'
 *
 * @param integer $name name of user in table usr
 * @global array $kga kimai-global-array
 * @return string
 * @author th
 */

// checked

function usr_name2id($name) {
    global $kga, $conn;

    $filter ['usr_name'] = MySQL::SQLValue($name);
    $columns[] = "usr_ID";
    $table = $kga['server_prefix']."usr";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['usr_ID'];
}

// -----------------------------------------------------------------------------------------------------------

/**
 * return name of a user with specific ID
 *
 * @param string $id the user's usr_ID
 * @global array $kga kimai-global-array
 * @return int
 * @author th
 */
function usr_id2name($id) {
    global $kga, $conn;
    
    $filter ['usr_ID'] = MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);
    $columns[] = "usr_name";
    $table = $kga['server_prefix']."usr";
    
    $result = $conn->SelectRows($table, $filter, $columns);
    if ($result == false) {
        return false;
    }
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['usr_name'];
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns data of the group with ID X
 * DEPRECATED!
 */

// checked

function get_grp($id) {
    return grp_get_data($id);
}

// -----------------------------------------------------------------------------------------------------------

/**
 * get in and out unix seconds of specific user
 *
 * <pre>
 * returns:
 * [0] -> in
 * [1] -> out
 * </pre>
 *
 * @param string $user ID of user
 * @global array $kga kimai-global-array
 * @return array
 * @author th
 */

// checked

function get_timespace() {
    global $kga, $conn;
    
    if (isset($kga['usr']['usr_ID'])) {
        $filter ['usr_ID'] = $kga['usr']['usr_ID'];
        $columns[] = "timespace_in";
        $columns[] = "timespace_out";
        $table = $kga['server_prefix']."usr";
    
        $result = $conn->SelectRows($table, $filter, $columns);
        if ($result == false) {
            return false;
        }
    
        $row = $conn->RowArray(0,MYSQL_ASSOC);

        $timespace[0] = $row['timespace_in'];
        $timespace[1] = $row['timespace_out'];

        /* database has no entries? */
        $mon = date("n"); $day = date("j"); $Y = date("Y");
        if (!$timespace[0]) {
            $timespace[0] = mktime(0,0,0,$mon,1,$Y);
        }
        if (!$timespace[1]) {
            $timespace[1] = mktime(23,59,59,$mon,lastday($month=$mon,$year=$Y),$Y);
        }
    
        return $timespace;
    } else {
        return false;
    }
}

// -----------------------------------------------------------------------------------------------------------

/**
 * lookup if an item (knd pct evt) is referenced in timesheet table
 * returns number of entities
 *
 * @param integer $id of item
 * @param string $subject of item
 * @return integer
 *
 * @author th
 */
 
// fail!

function getUsage($id,$subject) {
    global $kga, $conn;
    
    if (($subject!="pct")&&($subject!="evt")&&($subject!="knd")) {
        return false;
    }
    $id = MySQL::SQLValue($id, MySQL::SQLVALUE_NUMBER);
    $p = $kga['server_prefix'];
    
    switch ($subject) {
        case "pct":
        case "evt":
            $query = "SELECT COUNT(*) AS result FROM ${p}zef WHERE zef_${subject}ID = $id;";
            break;
        case "knd":
            $query = "SELECT COUNT(*) AS result FROM ${p}pct Left Join ${prefix}knd ON pct_kndID = knd_ID WHERE pct_kndID = $id;";
            break;
        default:
            return false;
            break;
    }
    
    $result = $conn->Query($query);
    if ($result == false) {
        return false;
    }
    
    $row = $conn->RowArray(0,MYSQL_ASSOC);
    return $row['result'];
}

// -----------------------------------------------------------------------------------------------------------

/**
 * returns the date of the first timerecord of a user (when did the user join?)
 * this is needed for the datepicker
 * @param integer $id of user
 * @return integer unix seconds of first timesheet record
 * @author th
 */
 
// checked 

function getjointime($usr_id) {
    global $kga, $conn;
    
    $usr_id = MySQL::SQLValue($usr_id, MySQL::SQLVALUE_NUMBER);
    $p = $kga['server_prefix'];

    $query = "SELECT zef_in FROM ${p}zef WHERE zef_usrID = $usr_id ORDER BY zef_in ASC LIMIT 1;";
    
    $result = $conn->Query($query);
    if ($result == false) {
        return false;
    }

    $result_array = $conn->RowArray(0,MYSQL_NUM);
    
    if ($result_array[0] == 0) {
        return mktime(0,0,0,date("n"),date("j"),date("Y"));        
    } else {
        return $result_array[0];
    }
}



// -----------------------------------------------------------------------------------------------------------
// TODO


// FOR TS FILTER 
// WORKS FOR PDO --- TODO: UMySQL-Version
/**
 * returns a multidimensional array in string format for customer-project-relationships
 *
 * @param integer $user ID of user in table usr
 * @global array $kga kimai-global-array
 * @return String
 * @author ob 
 */
/*
function knd_pct_arr() {
    global $kga, $conn;
    
    $usr = checkUser();
    get_config($usr['usr_ID']);
    
    // Lock tables
    $pdo_query_l = $conn->prepare("LOCK TABLE 
    " . $kga['server_prefix'] . "knd, 
    " . $kga['server_prefix'] . "pct, 
    " . $kga['server_prefix'] . "zef    
    ");
    $result_l = $pdo_query_l->execute();
    

    $pdo_query = $conn->prepare("SELECT * FROM " . $kga['server_prefix'] . "knd");
    $result = $pdo_query->execute();
    
    $knds = array();
    
    // build initial knd array
    while ($row  = $pdo_query->fetch(PDO::FETCH_ASSOC)) {
        array_push($knds, $row['knd_ID']);
    }
    
    $list = array();
    
    // fill the array with pcts
    foreach ($knds as $current_knd) {
        $pdo_query = $conn->prepare("SELECT * FROM " . $kga['server_prefix'] . "pct WHERE pct_kndID = ?");
        $result = $pdo_query->execute(array($current_knd));
        
        $pdo_query_count = $conn->prepare("SELECT COUNT(*) FROM " . $kga['server_prefix'] . "pct WHERE pct_kndID = ?");
        $result_count = $pdo_query_count->execute(array($current_knd));
        $result_array_count = $pdo_query_count->fetch();
        
        $list[$current_knd] = array();
                
        // insert last project
        $pdo_query_pre = $conn->prepare("SELECT MAX(`zef_ID`) FROM " . $kga['server_prefix'] . "zef JOIN " . $kga['server_prefix'] . "pct
         ON " . $kga['server_prefix'] . "zef.zef_pctID = " . $kga['server_prefix'] . "pct.pct_ID 
         WHERE zef_usrID = ?
         AND pct_kndID = ?");
        $result_pre = $pdo_query_pre->execute(array($kga['usr']['usr_ID'], $current_knd));
        $result_pre_array = $pdo_query_pre->fetch();
        
        $pdo_query2 = $conn->prepare("SELECT * FROM " . $kga['server_prefix'] . "zef WHERE zef_ID = ?");
        $result2 = $pdo_query2->execute(array($result_pre_array[0]));
        
        $pdo_query_count2 = $conn->prepare("SELECT COUNT(*) FROM " . $kga['server_prefix'] . "zef WHERE zef_ID = ?");
        $result_count2 = $pdo_query_count2->execute(array($result_pre_array[0]));
        $result_array_count2 = $pdo_query_count2->fetch();
        
        $result_array2 = $pdo_query2->fetch(PDO::FETCH_ASSOC);
        
//        echo "<pre>";
//        var_dump($result_array2);
//        echo "</pre>";
        
        // error_log("COUNT: " . $result_array_count[0]);
        
        if ($result_array_count[0] != 0) {
        
            if ($result_array_count2[0] != 0) {
                // if there is a last accessed project by the user:
                $list[$current_knd][0] = $result_array2['zef_pctID'];
            } else {
                // if there are projects associated with this customer, but none accessed by current user:
                
                $pdo_query_default = $conn->prepare("SELECT * FROM " . $kga['server_prefix'] . "pct WHERE pct_kndID = ? ORDER BY pct_name");
                $result_default = $pdo_query_default->execute(array($current_knd));
                $result_array_default = $pdo_query_default->fetch();
        
                $list[$current_knd][0] = 0;
            }
        
        } else {
            // if the customer has no projects at all:
            $list[$current_knd][0] = 0;
        }
        
        // $list[$current_knd][0] = "foo";
        
        while ($row = $pdo_query->fetch(PDO::FETCH_ASSOC)) {
            array_push($list[$current_knd], $row['pct_ID']);
        }
    }
    
//    echo "<pre>";
//    var_dump ($list);
//    echo "</pre>";
    
    
    // string format for array
    $s_list = '[],';
    foreach ($knds as $current_knd) {
        $s_list .= '[';
        
        $i = 0;        
        foreach ($list[$current_knd] as $current_pct) {
            $s_list .= $current_pct;
            
            if ($i < count($list[$current_knd]) - 1) { 
                $s_list .= ',';
            }
            
            $i++;
        }
        
        $s_list .= '],';
    }
    
    $s_list = substr($s_list, 0, -1);
    
    
    // Unlock tables
    $pdo_query_ul = $conn->prepare("UNLOCK TABLE 
    " . $kga['server_prefix'] . "knd, 
    " . $kga['server_prefix'] . "pct, 
    " . $kga['server_prefix'] . "zef    
    ");
    $result_ul = $pdo_query_ul->execute();
    
    return $s_list;

}

*/


// OBSOLETE .....
// /** ----------------- */
// /** ---- ALPHA ---- */
// /** ------------- */
// 
// function get_arr_first_pct_of_knd() {
//     global $kga, $conn;
//     
//     $arr = array();
//     
//     $pdo_query = $conn->prepare("SELECT knd_ID, pct_ID, pct_name FROM " . $kga['server_prefix'] . "knd JOIN " . $kga['server_prefix'] . "pct ON knd_ID = pct_kndID ORDER BY knd_ID, pct_name;");
//      $pdo_query->execute(array());
// 
//     $ruwen = 1;
//     $flag  = 0;
//     $i=0;
//     while ($row = $pdo_query->fetch(PDO::FETCH_ASSOC)) {
//         if ($ruwen == $row['pct_ID'] && $flag != $row['pct_ID']) {
//             $arr[$i]['knd_ID']      = $row['knd_ID'];
//             $arr[$i]['pct_ID']      = $row['pct_ID'];
//             $arr[$i]['pct_name']    = $row['pct_name'];
//             $i++;
//             $flag=$row['pct_ID'];
//         } else {
//             $ruwen=$row['pct_ID'];
//         }
//     }
//     return $arr;
// }
// /** -------- */
// /** -------- */
// /** -------- */
// 




?>