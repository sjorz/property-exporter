drop table PropertyTransactionLog;
create table PropertyTransactionLog (
created_at  datetime    not null,
PropertyId   integer	not null,
action varchar(1)		not null);

insert into PropertyTransactionLog (created_at,PropertyId,[action]) select getdate(),intPropertyId,'U' from Property where intStatusID=1


