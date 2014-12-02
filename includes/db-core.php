<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2014 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	function mf_connect_db(){
		try {
		  $dbh = new PDO('sqlsrv:Server='.MF_DB_HOST.';Database='.MF_DB_NAME, MF_DB_USER, MF_DB_PASSWORD);
		  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		  $dbh->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);

		  return $dbh;
		} catch(PDOException $e) {
		    die("Error connecting to the database: ".$e->getMessage());
		}
	}
	
	function mf_do_query($query,$params,$dbh){
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$sth->debugDumpParams();
			die("Query Failed: ".$e->getMessage());
		}
		
		return $sth;
	}
	
	function mf_do_fetch_result($sth){
		return $sth->fetch(PDO::FETCH_ASSOC);	
	}
	
	function mf_ap_forms_update($id,$data,$dbh){
		
		$update_values = '';
		$params = array();
		
		//dynamically create the sql update string, based on the input given
		foreach ($data as $key=>$value){
			if($key == 'form_id'){
				continue;
			}

			if($value === "null"){
					$value = null;
			}
			
			$update_values .= "[{$key}] = :{$key},";
			$params[':'.$key] = $value;
		}
		$update_values = rtrim($update_values,',');
		
		$params[':form_id'] = $id;
		
		$query = "UPDATE ".MF_TABLE_PREFIX."forms set 
									$update_values
							  where 
						  	  		form_id = :form_id";

		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$sth->debugDumpParams();
			echo "Query Failed: ".$e->getMessage();

			$error_message = "Query Failed: ".$e->getMessage();
			return $error_message;
		}
		
		return true;
	}

	function mf_ap_settings_update($data,$dbh){
		
		$update_values = '';
		$params = array();
		
		//dynamically create the sql update string, based on the input given
		foreach ($data as $key=>$value){
			if($value === "null"){
					$value = null;
			}
			
			$update_values .= "[{$key}]= :{$key},";
			$params[':'.$key] = $value;
		}
		$update_values = rtrim($update_values,',');
		
		$query = "UPDATE ".MF_TABLE_PREFIX."settings set $update_values";

		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$sth->debugDumpParams();
			echo "Query Failed: ".$e->getMessage();

			$error_message = "Query Failed: ".$e->getMessage();
			return $error_message;
		}
		
		return true;
	}
	
	function mf_ap_form_themes_update($id,$data,$dbh){
		
		$update_values = '';
		$params = array();
		
		//dynamically create the sql update string, based on the input given
		foreach ($data as $key=>$value){
			if($value === "null"){
					$value = null;
			}
			
			$update_values .= "[{$key}]= :{$key},";
			$params[':'.$key] = $value;
		}
		$update_values = rtrim($update_values,',');
		
		$params[':theme_id'] = $id;
		
		$query = "UPDATE ".MF_TABLE_PREFIX."form_themes set 
									$update_values
							  where 
						  	  		theme_id = :theme_id";
		
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
			$error_message = "Query Failed: ".$e->getMessage();
			return $error_message;
		}
		
		return true;
	}
	
	//check if a column name exist or not within a table
	//return true if column exist
	function mf_mysql_column_exist($table_name, $column_name,$dbh) {
		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table_name}' and COLUMN_NAME = '{$column_name}'";
		$sth = mf_do_query($query,array(),$dbh);
		$row = mf_do_fetch_result($sth);

		if(!empty($row)){
			return true;	
		}else{
			return false;
		}
	}

	//return column type and name for table
	// true if column exist
	function mf_mysql_columns_type($table_name, $dbh) {
		$query = "SELECT COLUMN_NAME
						, DATA_TYPE
						, REPLACE(REPLACE(COLUMN_DEFAULT,'(''',''),''')','') COLUMN_DEFAULT
						, case when IS_NULLABLE = 'NO' then 0 else null end IS_NULLABLE
						, case when CHARACTER_MAXIMUM_LENGTH is not null then '(' + cast(CHARACTER_MAXIMUM_LENGTH as varchar) + ')' else null end as CHARACTER_MAXIMUM_LENGTH 
				FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table_name}'";
		$sth = mf_do_query($query,array(),$dbh);

		$columns_type = array();
		while($row = mf_do_fetch_result($sth)){
			$columns_type[$row["COLUMN_NAME"]]['DATA_TYPE'] = "{$row['DATA_TYPE']}{$row['CHARACTER_MAXIMUM_LENGTH']}";
			$columns_type[$row["COLUMN_NAME"]]['COLUMN_DEFAULT'] = "{$row['COLUMN_DEFAULT']}";
			$columns_type[$row["COLUMN_NAME"]]['IS_NULLABLE'] = "{$row['IS_NULLABLE']}";
		}
		return $columns_type;	
	}

	function mf_mysql_drop_constraint($table_name, $elemnt, $dbh)
	{
		$query = "DECLARE @sql NVARCHAR(MAX)
					WHILE 1=1
					BEGIN
					    SELECT TOP 1 @sql = N'alter table [{$table_name}] drop constraint ['+dc.NAME+N']'
					    from sys.default_constraints dc
					    JOIN sys.columns c
					        ON c.default_object_id = dc.object_id
					    WHERE 
					        dc.parent_object_id = OBJECT_ID('{$table_name}')
					    AND c.name = N'{$elemnt}'
					    IF @@ROWCOUNT = 0 BREAK
					    EXEC (@sql)
					END";
		mf_do_query($query,array(),$dbh);
	}

    function extractType($dbType)
	{
        if(strpos($dbType,'bigint')!==false)
                return 'bigint';
        else if(strpos($dbType,'float')!==false || strpos($dbType,'real')!==false || strpos($dbType,'decimal')!==false)
                return 'double';
        else if(strpos($dbType,'int')!==false || strpos($dbType,'smallint')!==false || strpos($dbType,'tinyint'))
                return 'integer';
        else if(strpos($dbType,'bit')!==false)
                return 'boolean';
        else if(strpos($dbType,'date')!==false || strpos($dbType,'datetime')!==false)
                return 'Datetime';
        else
                return 'string';
	}

    function typecast($value, $columnSchema)
    {	
    	$dbType = extractType($columnSchema['DATA_TYPE']);
    	$defaultValue = $columnSchema['COLUMN_DEFAULT'];
		$isNullable = $columnSchema['IS_NULLABLE'];

		if(($value===null && $columnSchema['IS_NULLABLE'] === '0') || ( $value==='' && $columnSchema['IS_NULLABLE'] === '0' && $dbType === 'integer'))
				$value = $defaultValue;
        if(gettype($value) === $dbType || $value===null)
                return $value;
        if($value==='')
                return $dbType ==='string' ? '' : null;
        switch($dbType)
        {
                case 'integer': return (integer)$value;
                case 'boolean': return (boolean)$value;// return $value ? 1 : 0;
                case 'bigint': return number_format($value,0);
                case 'double': return (double)$value;
                case 'string': return (string)$value;
		        case 'Datetime': 
	                $date_array = date_parse($value);
					// returns original date string assuming the format was Y-m-d H:i:s
					$date_string = date('Y-m-d H:i:s', mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year'])); 
                	return ($date_array["error_count"] == 0 && $date_array["warning_count"] == 0)? $date_string: null;
                default: return $value;
        }
    }
?>