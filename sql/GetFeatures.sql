select 
pf.vcFeature as name,
(select  vcFeature from PropertyFeature as pf1 where pf.IntParentFeatureID=pf1.IntFeatureID) as [group]
from PropertyFeatureRel
left outer join PropertyFeature pf on pf.IntFeatureID=PropertyFeatureRel.IntFeatureID
left outer join FeatureValues on FeatureValues.IntValueId=PropertyFeatureRel.IntValueId
left outer join FeatureValues_Desc on FeatureValues_Desc.IntValueDescID=FeatureValues.IntValueDescId
where PropertyFeatureRel.IntPropertyID=301965 and bitValue > 0



