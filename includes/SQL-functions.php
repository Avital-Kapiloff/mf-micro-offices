<?php

function check_ifExists($functionName, $dbh)
{
    $query = " IF EXISTS (
        SELECT * FROM sysobjects WHERE id = object_id(N'{$functionName}') 
        AND xtype IN (N'FN', N'IF', N'TF')
    )
        DROP FUNCTION {$functionName}";

    $params = array();
    $sth = $dbh->prepare($query);
    try{
      $sth->execute($params);
    }catch(PDOException $e) {
      return $e->getMessage().'<br/><br/>';
    }

    return '';
}

function create_SQLFunction_SubstringIndex($dbh)
{

    check_ifExists("SubstringIndex", $dbh);

    $query = " CREATE FUNCTION [dbo].[SubstringIndex]
(@str VARCHAR (255), @delim VARCHAR (1), @count INT)
RETURNS VARCHAR (255)
AS
BEGIN
    DECLARE @result AS VARCHAR (255), @posn AS INT, @loop AS INT, 
            @found AS INT, @reversed AS INT;
    SET @loop = 0;
    SET @posn = -1;
    SET @found = 0;
    SET @reversed = 0;
    IF @count < 0
        BEGIN
            SET @reversed = 1;
            SET @count = @count * -1;
            SET @str = REVERSE(@str);
        END
    WHILE @loop < @count
        BEGIN
            SET @posn = charindex(@delim, @str, @posn + 1);
            IF @posn > 0
                SET @found = 1;
            ELSE
                IF @found = 1 AND @reversed = 0
                    RETURN @str; -- ie mimic mysql behaviour
                ELSE
                    IF @found = 1 AND @reversed = 1
                        RETURN REVERSE(@str); -- ie mimic mysql behaviour
            SET @loop = @loop + 1;
        END
    IF @posn >= 0 AND @reversed = 0
        RETURN SUBSTRING(@str, 0, @posn);
    ELSE
        IF @posn >= 0 AND @reversed = 1
            RETURN REVERSE(SUBSTRING(@str, 0, @posn));
    RETURN '';
END
 ";

    $params = array();
    $sth = $dbh->prepare($query);
    try{
      $sth->execute($params);
    }catch(PDOException $e) {
      return $e->getMessage().'<br/><br/>';
    }

    return '';

}
 ?>
