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
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=417566 and pf.IntFeatureID=17
) as bathrooms,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=417566 and pf.IntFeatureID=49
) as ensuites,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=417566 and pf.IntFeatureID=86
) as parking_type,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=417566 and pf.IntFeatureID=75
) as car_spaces,
(select fv.vcFeatureValue from PropertyFeatureRel as pf
left outer join featurevalues as fv on fv.IntValueId=pf.IntValueId
where pf.IntPropertyID=417566 and pf.IntFeatureID=56
) as furnished,
(select pf.txtDetails from PropertyFeatureRel as pf
where pf.IntPropertyID=417566 and pf.IntFeatureID=1430
) as total_land_area,
(select pf.txtDetails from PropertyFeatureRel as pf
where pf.IntPropertyID=417566 and pf.IntFeatureID=1633
) as energy_efficiency_rating,
(select pf.txtDetails from PropertyFeatureRel as pf
where pf.IntPropertyID=417566 and pf.IntFeatureID=5
) as property_age,
(select top(1) cast(case when bitValue=1 then 'true' else 'false' end as varchar(10)) from PropertyFeatureRel as pf
where pf.IntPropertyID=417566 and pf.IntFeatureID in (1423,1334,1335,1336,1426)) as pets_allowed 
from property prop
join Address on Address.intAddressId=prop.intAddressId
join PropertyCat on PropertyCat.PropertyCatID=prop.intPropertyCatID
join PropertyStatus on PropertyStatus.intStatusId=prop.intStatusID
join dbo.PropertySubCatRel pr on prop.intPropertyID=pr.intPropertyId
join PropertyCat pc on pr.intPropertyCatID=pc.PropertyCatID
where prop.intPropertyID=417566
