<?php

function imageFolder()
{
	return '\/Images\/images_agents1\/';
}

function getSqlForProperty($pid)
{
	return sprintf (" 
select
PropertyCat.Category as listing_type,
cast (case when prop.uif1=1 then 'street' else (case when prop.uif1=2 then 'full' else 'suburb' end) end as varchar(10)) as address_display_type,
pc.Category as property_type,
CONVERT(varchar,prop.sdtExpiryDate,126) as [expiry_date],
CONVERT(varchar,prop.dteDateEntered,126) as created_at,
prop.txtComments as description,
prop.txtAdvertByline as byline,
dbo.PropertyStatus.vcStatusDesc as status_type,
CONVERT(varchar,prop.AvailableDate,126) as date_available,
prop.MinTerm as desired_term,
prop.RelPrice as weekly_rent,
prop.priceRangeFrom as weekly_rent_minimum,
prop.priceRangeTo as weekly_rent_maximum,
prop.Bond as bond,
prop.intPropertyID as legacy_property_id,
prop.FeedReference as feed_ref,
Address.vcStreetNo as street_number,
Address.vcStreetNoTo as street_number_to,
Address.vcStreetName as street_name,
Address.vcStreetType as street_type,
Address.vcSuburb as suburb,
Address.chrState as state,
Address.intPostCode as postcode,
Address.vcSuiteNo as unit_number,
prop.sntSize as bedrooms,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=%d and pf.IntFeatureID=17
) as bathrooms,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=%d and pf.IntFeatureID=49
) as ensuites,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=%d and pf.IntFeatureID=86
) as parking_type,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=%d and pf.IntFeatureID=75
) as car_spaces,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=%d and pf.IntFeatureID=56
) as furnished,
(select pf.txtDetails from PropertyFeatureRel as pf
where pf.IntPropertyID=%d and pf.IntFeatureID=1430
) as total_land_area,
(select pf.txtDetails from PropertyFeatureRel as pf
where pf.IntPropertyID=%d and pf.IntFeatureID=1633
) as energy_efficiency_rating,
(select pf.txtDetails from PropertyFeatureRel as pf
where pf.IntPropertyID=%d and pf.IntFeatureID=5
) as property_age,
(select top(1) cast(case when bitValue=1 then 'true' else 'false' end as varchar(10)) from PropertyFeatureRel as pf
where pf.IntPropertyID=%d and pf.IntFeatureID in (1423,1334,1335,1336,1426)) as pets_allowed 
from property prop
join Address on Address.intAddressId=prop.intAddressId
join PropertyCat on PropertyCat.PropertyCatID=prop.intPropertyCatID
join PropertyStatus on PropertyStatus.intStatusId=prop.intStatusID
join dbo.PropertySubCatRel pr on prop.intPropertyID=pr.intPropertyId
join PropertyCat pc on pr.intPropertyCatID=pc.PropertyCatID
where prop.intPropertyID=%d",
		$pid, $pid, $pid, $pid, $pid, $pid, $pid, $pid, $pid, $pid);
}

function getSqlForPhotos($pid)
{
    return sprintf ("
        select
			vcDescription as caption,
			vcFilePathName as original_url,
			vcThumbnailPath as thumbnail_url,
			vcDisplaySortOrder as [order],
			bitMainImage as [default],
			CONVERT(varchar,modifiedDate,126) as updated_at
		from PropertyImages
		where intPropertyID=%d", $pid);
}

function getSqlForFeatures($pid)
{
    return sprintf ("select 
		pf.vcFeature as name,
		(select  vcFeature from PropertyFeature as pf1 where pf.IntParentFeatureID=pf1.IntFeatureID) as [group]
		from PropertyFeatureRel
		left outer join PropertyFeature pf on pf.IntFeatureID=PropertyFeatureRel.IntFeatureID
		left outer join FeatureValues on FeatureValues.IntValueId=PropertyFeatureRel.IntValueId
		left outer join FeatureValues_Desc on FeatureValues_Desc.IntValueDescID=FeatureValues.IntValueDescId
		where PropertyFeatureRel.IntPropertyID=%d and bitValue > 0", $pid);
}

function getSqlForLaundryFeature($pid)
{
    return sprintf ("select 
		pf.vcFeature as [group],
		FeatureValues.vcFeatureValue AS name
		from PropertyFeatureRel
		left outer join PropertyFeature pf on pf.IntFeatureID=PropertyFeatureRel.IntFeatureID
		left outer join FeatureValues on FeatureValues.IntValueId=PropertyFeatureRel.IntValueId
		left outer join FeatureValues_Desc on FeatureValues_Desc.IntValueDescID=FeatureValues.IntValueDescId
		where PropertyFeatureRel.IntPropertyID=%d and PropertyFeatureRel.intValueId=214", $pid);
}

function getSqlForContact($pid)
{
	$s = imageFolder();

    return sprintf ("select
		CASE WHEN pc.Surname IS NULL then person.FirstName ELSE pc.FirstName END as first_name,
        CASE WHEN pc.Surname IS NULL then person.Surname ELSE pc.SurName END as last_name,
		pc.personId as legacy_contact_id,
 		person.BusPhone as phone_number, person.Mobile as mobile_number, person.Fax as fax,
		person.Email as email_address,
		person.Mobile as mobile_number,
 		CAST(CASE WHEN member.ContactImageId IS NULL THEN NULL ELSE  '/Images/images_agents1/' + CAST(member.ContactImageId AS varchar(250)) END AS varchar(500)) AS photo
		from dbo.Property as prop 
 		INNER JOIN Person ON Person.PersonID = prop.intContactID
		LEFT OUTER join PersonContact as pc on pc.PersonID=Person.PersonContactID
 		LEFT OUTER JOIN Member ON Person.MemberID = Member.MemberId
		where prop.intPropertyID=%d", $pid);
}

function getSqlForLandlord($pid)
{
    return sprintf ("select
		person.FirstName as first_name,
		person.Surname as last_name,
		person.Email as email_address,
		person.BusPhone as phone_number,
		person.Mobile as mobile_number,
		person.TradingName as trading_name,
		contact.personId as legacy_landlord_id,
		addr.vcStreetNo as street_address,
		addr.vcStreetName as street_address_1,
		addr.vcSuburb as suburb,
		addr.intPostCode as postcode,
		addr.chrState as state
		from dbo.Property as prop
		join dbo.Person as person on person.MemberID=prop.intOwnerID
		inner join dbo.PersonContact as contact on contact.PersonId=person.PersonID
		left outer join dbo.Address as addr on addr.intAddressID=person.AddressID
		where prop.intPropertyID=%d
		and not exists (select MemberID from company where company.MemberID=prop.intOwnerID)",
		$pid);
}

function getSqlForCompany($pid)
{
	$s = imageFolder();

    return sprintf ("select
		person.FirstName as first_name,
		person.Surname as last_name,
		person.Email as email_address,
		comp.Telephone_No as phone_number,
		comp.FeedReferenceID as feed_ref,
		comp.CompanyID as legacy_company_id,
		person.Mobile as mobile_number,
		person.TradingName as trading_name,
		addr.vcStreetNo as street_address,
		addr.vcStreetName as street_address_1,
		addr.vcSuburb as suburb,
		addr.intPostCode as postcode,
		addr.chrState as state,
		'/Images/images_agents1/' + banners.Path as banner
		from dbo.Property as prop
		join dbo.Person as person on person.MemberID=prop.intOwnerID
		left outer join dbo.PersonContact as contact on contact.PersonId=person.PersonID
		join dbo.Company as comp on comp.MemberId=person.MemberID
		join dbo.LogosAndImages as banners on banners.OwnerID=comp.CompanyID
		left outer join dbo.Address as addr on addr.intAddressID=person.AddressID
		where prop.intPropertyID=%d", $pid);
}

function getSqlForInspectionTimes($pid)
{
    return sprintf ("
		select
		null as day_of_week, 
		CONVERT(varchar, Convert(datetime, strInspectDate) + Convert(datetime,replace (strStartTime,'.',':')),126) as start_time,
		CONVERT(varchar, Convert(datetime, strInspectDate) + Convert(datetime,replace (strEndTime,'.',':')),126) as end_time
		from Inspections
		where PropertyID=%d
		UNION ALL
		select 
		'monday' as day_of_week,
		cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
		cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
		from InspectionDetails
		where intPropertyID=%d and bitMondays=1
		UNION ALL
		select 
		'tuesday' as day_of_week,
		cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
		cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
		from InspectionDetails
		where intPropertyID=%d and bitTuesdays=1
		UNION ALL
		select 
		'wednesday' as day_of_week,
		cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
		cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
		from InspectionDetails
		where intPropertyID=%d and bitWednesdays=1
		UNION ALL
		select 
		'thursday' as day_of_week,
		cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
		cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
		from InspectionDetails
		where intPropertyID=%d and bitThursdays=1
		UNION ALL
		select 
		'friday' as day_of_week,
		cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
		cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
		from InspectionDetails
		where intPropertyID=%d and bitFridays=1
		UNION ALL
		select 
		'saturday' as day_of_week,
		cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
		cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
		from InspectionDetails
		where intPropertyID=%d and bitSaturdays=1
		UNION ALL
		select 
		'sunday' as day_of_week,
		cast (('1-1-1970 ' + CONVERT(varchar,sntFromHrs) + ':' + CONVERT(varchar,sntFromMins) + vcFromAMPM) as datetime) as start_time,
		cast (('1-1-1970 ' + CONVERT(varchar,sntToHrs) + ':' + CONVERT(varchar,sntToMins) + vcToAMPM) as datetime) as end_time
		from InspectionDetails
		where intPropertyID=%d and bitSundays=1",
 		$pid, $pid, $pid, $pid, $pid, $pid, $pid, $pid);
}


?>
