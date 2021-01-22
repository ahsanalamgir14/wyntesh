select  member_id 
from    (select * from positions where position=2
         order by parent_id) positions_sorted,
        (select @pv := '1') initialisation
where   find_in_set(parent_id, @pv)
and     length(@pv := concat(@pv, ',', member_id))

1207160854618486301	- order placed
1207160854613645396 - payout sms
1207160854606919152 - welcome sms

Hi {#var#}, Order has been placed worth Rs {#var#} on {#var#}, your order number is {#var#}. - Wyntash	

Hi, %%|name^{"inputtype" : "text", "maxlength" : "15"}%%, Order has been placed worth Rs %%|amount^{"inputtype" : "text", "maxlength" : "15"}%% on %%|sitename^{"inputtype" : "text", "maxlength" : "30"}%%, your order number is %%|orderno^{"inputtype" : "text", "maxlength" : "15"}%%. - Wyntash

Hi Main ID, Order has been placed worth Rs 1645.00 on https://wyntash.in, your order number is 9693781605. - Wyntash

Congrats {#var#}, Your Wallet has been Credited with Rs. {#var#} at {#var#}. Check your income reports for more details. - Wyntash	

Congrats %%|name^{"inputtype" : "text", "maxlength" : "20"}%%, Your Wallet has been Credited with Rs. %%|payoutamount^{"inputtype" : "text", "maxlength" : "10"}%% at %%|sitename^{"inputtype" : "text", "maxlength" : "20"}%%. Check your income reports for more details. - Wyntash

Hello {#var#}, Welcome to {#var#}. Your Login ID is {#var#}. Login and jump into endless opportunity. - Wyntash	

Hello %%|name^{"inputtype" : "text", "maxlength" : "20"}%%, Welcome to %%|sitename^{"inputtype" : "text", "maxlength" : "30"}%%. Your Login ID is %%|username^{"inputtype" : "text", "maxlength" : "10"}%%. Login and jump into endless opportunity. - Wyntash
