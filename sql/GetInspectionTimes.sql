select
null as day_of_week, 
CONVERT(varchar, Convert(datetime, strInspectDate) + Convert(datetime,strStartTime),126) as start_time,
CONVERT(varchar, Convert(datetime, strInspectDate) + Convert(datetime,strEndTime),126) as end_time
from Inspections
where PropertyID=301965
UNION ALL
select 
'monday' as day_of_week,
cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
from InspectionDetails
where intPropertyID=382486 and bitMondays=1
UNION ALL
select 
'tuesday' as day_of_week,
cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
from InspectionDetails
where intPropertyID=382486 and bitTuesdays=1
UNION ALL
select 
'wednesday' as day_of_week,
cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
from InspectionDetails
where intPropertyID=382486 and bitWednesdays=1
UNION ALL
select 
'thursday' as day_of_week,
cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
from InspectionDetails
where intPropertyID=382486 and bitThursdays=1
UNION ALL
select 
'friday' as day_of_week,
cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
from InspectionDetails
where intPropertyID=382486 and bitFridays=1
UNION ALL
select 
'saturday' as day_of_week,
cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
from InspectionDetails
where intPropertyID=382486 and bitSaturdays=1
UNION ALL
select 
'sunday' as day_of_week,
cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
from InspectionDetails
where intPropertyID=382486 and bitSundays=1
