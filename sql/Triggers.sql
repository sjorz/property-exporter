-- command (Ctrl-Shift-M) to fill in the parameter
-- values below.
--
-- See additional Create Trigger templates for more
-- examples of different Trigger statements.
--
-- This block of comments will not be included in
-- the definition of the function.
-- ================================================
-- =============================================
-- Author:      <Author,,Name>
-- Create date: <Create Date,,>
-- Description: <Description,,>
-- =============================================

IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_TYPE='BASE TABLE'
           AND TABLE_NAME='PropertyTransactionLog')
                    drop table dbo.PropertyTransactionLog;
    create table dbo.PropertyTransactionLog (
    	created_at  datetime    not null,
    	PropertyId   integer    not null,
    	action varchar(1)       not null);
GO

IF EXISTS (
    select * from dbo.sysobjects 
    where name = 'LOG_PID' 
    and OBJECTPROPERTY(id, 'IsTrigger') = 1)
BEGIN
	DROP TRIGGER dbo.LOG_PID
END
GO

CREATE TRIGGER dbo.LOG_PID
   ON  dbo.Property
   FOR INSERT,UPDATE,DELETE
AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
    -- interfering with SELECT statements.
    SET NOCOUNT ON;

    INSERT INTO dbo.PropertyTransactionLog SELECT current_timestamp,IntPropertyId,'D' FROM deleted
    INSERT INTO dbo.PropertyTransactionLog SELECT current_timestamp,intPropertyID,'U' FROM inserted

END
GO

if exists (
    select * from dbo.sysobjects 
    where name = 'LOG_PID_ADDRESS' 
    and OBJECTPROPERTY(id, 'IsTrigger') = 1)
BEGIN
	DROP TRIGGER dbo.LOG_PID_ADDRESS
END
GO

CREATE TRIGGER dbo.LOG_PID_ADDRESS
   ON  dbo.Address
   FOR INSERT,DELETE,UPDATE
AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
    -- interfering with SELECT statements.
    SET NOCOUNT ON;

    -- Insert statements for trigger here
    DECLARE @addressId int

    SELECT @addressId = (SELECT intAddressId FROM Inserted)

    insert into dbo.PropertyTransactionLog select current_timestamp,prop.intPropertyID,'U' from PersonContact pc
        join [address] addr on addr.IntAddressId=pc.AddressID
        join Person pers on pers.PersonContactId=pc.PersonID
        join Property prop on prop.intOwnerID=pers.MemberID
        where pc.AddressId=@addressId

END
GO

