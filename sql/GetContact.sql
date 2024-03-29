select
person.FirstName as first_name_1,
person.Surname as last_name_1,
person.personId as legacy_contact_id,
contact.FirstName as first_name,
contact.Surname as last_name,
contact.Email as email_address,
contact.BusPhone as phone_number,
contact.Mobile as mobile_number
from dbo.Property as prop
join dbo.Person as person on person.MemberID=prop.intOwnerID
join dbo.PersonContact as contact on contact.PersonId=person.PersonID
where prop.intPropertyID=419154