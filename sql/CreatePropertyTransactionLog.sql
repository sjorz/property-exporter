drop table PropertyTransactionLog;
create table PropertyTransactionLog (
created_at  datetime    not null,
PropertyId   integer	not null,
action varchar(1)		not null);

