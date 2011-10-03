select '[' + vcFromAMPM + ']' from InspectionDetails where vcFromAMPM <> 'AM' and vcFromAMPM<>'PM' and vcFromAMPM is not null and len(vcFromAMPM)>0

