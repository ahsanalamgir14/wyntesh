select  member_id 
from    (select * from positions where position=2
         order by parent_id) positions_sorted,
        (select @pv := '1') initialisation
where   find_in_set(parent_id, @pv)
and     length(@pv := concat(@pv, ',', member_id))

1207160854618486301	- order placed
1207160854613645396 - payout sms
1207160854606919152 - welcome sms

