select
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
addr.chrState as state
from dbo.Property as prop
join dbo.Person as person on person.MemberID=prop.intOwnerID
join dbo.PersonContact as contact on contact.PersonId=person.PersonID
join dbo.Company as comp on comp.MemberId=person.MemberID
join dbo.Address as addr on addr.intAddressID=person.AddressID
where prop.intPropertyID=419154
