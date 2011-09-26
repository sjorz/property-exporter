select vcDescription as caption,
vcFilePathName as large_url,
vcThumbnailPath as thumbnail_url,
vcDisplaySortOrder as [order],
bitMainImage as [default],
CONVERT(varchar,modifiedDate,126) as updated_at
from PropertyImages
where intPropertyID=419155
