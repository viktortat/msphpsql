--TEST--
Test insertion with floats
--FILE--
﻿<?php
include 'MsCommon.inc';

function ExecData($withParams)
{     
    set_time_limit(0);  
    sqlsrv_configure('WarningsReturnAsErrors', 1);  
    
    // Connect
    $conn = Connect();
    if( !$conn ) { FatalError("Could not connect.\n"); }

    $tableName = GetTempTableName();
    
    $stmt = sqlsrv_query($conn, "CREATE TABLE $tableName ([c1_int] int, [c2_smallint] smallint)");
    sqlsrv_free_stmt($stmt);
    
    if ($withParams) 
    {
        $stmt = sqlsrv_prepare($conn, "INSERT INTO $tableName (c1_int, c2_smallint) VALUES (?, ?)", array(array(&$v1, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_INT), array(&$v2, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_INT)));
    }
    else
    {
        $stmt = sqlsrv_prepare($conn, "INSERT INTO $tableName (c1_int, c2_smallint) VALUES (?, ?)", array(array(&$v1), array(&$v2)));
    }
    $values = array();
    $numRows = 0;

    $v1 = 1;  
    array_push($values, $v1);
    $v2 = 2;  
    array_push($values, $v2);
    sqlsrv_execute($stmt); 
    $numRows++;
    
    $v1 = 11; 
    array_push($values, $v1);
    $v2 = 12; 
    array_push($values, $v2);
    sqlsrv_execute($stmt); 
    $numRows++;
    
    $v1 = 21; 
    array_push($values, $v1);
    $v2 = 22; 
    array_push($values, $v2);
    sqlsrv_execute($stmt); 
    $numRows++;
    
    $v1 = 31; 
    array_push($values, $v1);
    $v2 = 32; 
    array_push($values, $v2);
    sqlsrv_execute($stmt); 
    $numRows++;
    
    $v1 = 41; 
    array_push($values, $v1);
    $v2 = 42; 
    array_push($values, $v2);
    sqlsrv_execute($stmt); 
    $numRows++;
        
    sqlsrv_free_stmt($stmt);   
    
    $idx = 0;
    $stmt = sqlsrv_query($conn, "SELECT * FROM $tableName");  
    while ($result = sqlsrv_fetch($stmt))
    {
        for ($i = 0; $i < 2; $i++) 
        {
            $value = sqlsrv_get_field($stmt, $i); 

            $expected = $values[$idx++];
            if ($expected !== $value)
            {
                echo "Value $value is unexpected\n";                
            }
        }
    }
    sqlsrv_free_stmt($stmt);   
    
    DeleteRows($conn, $numRows, $tableName);
    sqlsrv_close($conn);           
}

function DeleteRows($conn, $numRows, $tableName)
{
    $stmt1 = sqlsrv_prepare($conn, "SELECT * FROM $tableName");
    $stmt2 = sqlsrv_prepare($conn, "DELETE TOP(3) FROM $tableName");

    $noExpectedRows = $numRows;
    $noDeletedRows = 3;
    
    while ($noExpectedRows > 0)
    {
        sqlsrv_execute($stmt1);
        $noActualRows = 0;
        while ($result = sqlsrv_fetch($stmt1))
        {
            $noActualRows++;
        }
        echo "Number of Actual Rows: $noActualRows\n";
        if ($noActualRows != $noExpectedRows)
        {
            echo("Number of retrieved rows does not match expected value\n");
        }
        sqlsrv_execute($stmt2);
        $noAffectedRows = sqlsrv_rows_affected($stmt2);
        $noActualRows = ($noExpectedRows >= $noDeletedRows)? $noDeletedRows : $noExpectedRows;
        echo "Number of Affected Rows: $noAffectedRows\n";
        if ($noActualRows != $noAffectedRows)
        {
            echo("Number of deleted rows does not match expected value\n");
        }
        $noExpectedRows -= $noDeletedRows;
    }
    
    sqlsrv_free_stmt($stmt1);
    sqlsrv_free_stmt($stmt2);
}

function Repro()
{
    StartTest("sqlsrv_statement_exec_param_ints");
    echo "\nTest begins...\n";
    try
    {
        ExecData(true);
        ExecData(false);
    }
    catch (Exception $e)
    {
        echo $e->getMessage();
    }
    echo "\nDone\n";
    EndTest("sqlsrv_statement_exec_param_ints");
}

Repro();

?>
--EXPECT--
﻿
Test begins...
Number of Actual Rows: 5
Number of Affected Rows: 3
Number of Actual Rows: 2
Number of Affected Rows: 2
Number of Actual Rows: 5
Number of Affected Rows: 3
Number of Actual Rows: 2
Number of Affected Rows: 2

Done
Test "sqlsrv_statement_exec_param_ints" completed successfully.
