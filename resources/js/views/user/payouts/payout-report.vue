<template>
  <div class="app-container" >

    <el-row>
     <center>
      <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" id="template_pos">
        <div id="container">
        <div id="page-wrap" >

                  <section id="memo">
                    <div class="logo">
                        <img src="images/hader_logo.png" alt="" width="150" height="150" name="Picture 1" align="bottom" border="1" class="image" />

                    </div>
                    
                    <div class="company-info">
                      <div>Wyntash</div>
                         
                      <span>Income statement of Business Affiliate</span><br>
                       <span>teamwyntash@gmail.com</span>
                    </div>

                  </section>

                  <section id="invoice-title-number">
                  
                    <span id="title">{{monthNumToName(temp.date) }}</span>
                    <!-- <span id="number">₹ {{temp.netPayble}}</span> -->
                    
                  </section>
                  
                  <div class="clearfix"></div>
                  
                  <section id="client-info-name">
                    <div>
                      <span class="bold">{{temp.name}}</span>
                    </div>
                  </section>
                  <section id="client-info">
                    <div>
                      <span class="bold">{{temp.id}}</span>
                    </div>
                  </section>

                  <section id="client-info-rank">
                   
                    <div class="rankparent">
                      <el-button id="rank" type="success" round>{{rank}}</el-button>
                      <!-- <span id="rank">{{rank}} </span> -->
                    </div>
                    
                  
                  </section>
                  <section class="imageparent">
                        <img :src="temp.pic"  class="childimage" />                    

                  </section>

                  
                  <section id="items">
                    
                    <table cellpadding="0" cellspacing="0">
                    
                      <tr>
                        <th style="padding: 12px 11px !important;">Income</th>
                        <th>Amount</th>
                      </tr>
                      
                      <tr data-iterate="item">
                        <td>Affiliate Bonus</td>
                        <td style="text-align: right;">{{affiliate_bonus}}</td>
                      </tr>

                      <tr data-iterate="item">
                        <td>Squad Bonus</td>
                        <td style="text-align: right;">{{squad_bonus.toFixed(2)}}</td>
                      </tr>

                      <tr data-iterate="item">
                        <td>Elevation Bonus</td>
                        <td style="text-align: right;" >{{elevation_bonus.toFixed(2)}}</td>
                      </tr>
                      
                      <tr data-iterate="item">
                        <td>Luxury Bonus</td>
                        <td style="text-align: right;" >{{luxury_bonus.toFixed(2)}}</td>
                      </tr>

                      <tr data-iterate="item">
                        <td>Pro Bonus</td>
                        <td style="text-align: right;">{{premium_bonus.toFixed(2)}}</td>
                      </tr>
                    
                      
                    </table>
                    
                  </section>
                  
                  <section id="sums">
                    <table cellpadding="0" cellspacing="0">
                        <tr class="add">
                            <td>TOTAL INCOME :</td>
                            <td>{{temp.totalIncome.toFixed(2)}}</td>
                        </tr>
                          
                        <tr >
                            <td>TDS :</td>
                            <td>{{temp.totalDeductions.toFixed(2)}}</td>
                        </tr>

                        <tr>
                            <td>NET PAYABLE :</td>
                            <td>{{temp.netPayble.toFixed(2)}}</td>
                        </tr>

                    </table>

                    <div class="clearfix"></div>
                    
                  </section>
                  
               

        </div>
        </div>
</el-col>
     </center>
</el-row>
<center style="margin-top: 20px;">
  <el-button type="success" class="print-btn" @click="print()">Print</el-button>
</center>

</div>
</template>

<script>
    import { getMemberPayout } from "@/api/user/payouts";
    import { parseTime } from "@/utils";
    import PanThumb from '@/components/PanThumb';
    export default {
      name: "Invoice",
      components: { PanThumb },
      data() {
        return {
            temp:{
                name:undefined,
                rank:undefined,
                userid:undefined,
                contactno:undefined,
                date:undefined,
                payoutid:undefined,
                totalIncome:undefined,
                totalDeductions:undefined,
                netPayble:undefined,
                pic:undefined,
                address:undefined,
                email :undefined,
            },
            monthNames :["Jan", "Feb", "March", "April", "May", "June","July", "Aug", "Sep", "Oct", "Nov", "Dec"],
            rank:undefined,
            payout:{},
            company_details:{},
            user:{},
            payout_incomes:[],
            luxury_bonus:0,
            squad_bonus:0, 
            affiliate_bonus:0, 
            id:0, 
            contact_no:0, 
            elevation_bonus :0,
            tripFund :0,
            premium_bonus :0,
            tripFunds1 :0,
            vehilceFund2 :0,
            vehilceFunds2 :0,
            vehilceFunds1 :0,
            houseFund2 :0,
            houseFunds2 :0,
            houseFunds1 :0,
            vision4uBonus5 :0,
            vision4uBonus25 :0,
            vision4uBonuss25 :0,
            franchiseIncome :0,
         
      };
  },
  created() {    
    let payout_id=this.$route.params.id;
    this.getPayout(payout_id);
    // this.print(); 
},
methods: {    
     monthNumToName(date){
          let newdate = parseTime(date);
          const moonLanding = new Date(newdate);
          return this.monthNames[moonLanding.getMonth()]+" "+moonLanding.getFullYear();
     },
    getPayout(id){
      getMemberPayout(id).then(response => {
        console.log(response);
        // this.temp.affiliter           = response.affiliter;
        this.temp.contact_no           = response.payout.member.user.contact;
        this.temp.email           = response.payout.member.user.email;
        this.temp.id           = response.user.username;
        this.temp.pic           = response.payout.member.user.profile_picture;
        this.temp.address           = response.payout.member.kyc.address;
        // this.temp.pic           = response.payout.member.user.profile_picture;
        this.temp.name          = response.payout.member.user.name;
        this.temp.rank          = response.payout.rank.name;
        this.temp.userid        = response.payout.member.user.username;
        this.temp.contactno     = response.payout.member.user.contact;
        this.temp.date          = response.payout.created_at;
        this.temp.payoutid      = response.payout.id;
        this.payout             = response.payout;
        this.payout_incomes     = response.incomes;
        this.company_details    = response.company_details;
        this.user               = response.user;
        this.rank               = response.user.rank;
        this.affiliate_bonus    = response.affiliter;
        let scope               = this
        
        this.temp.totalIncome       = parseFloat(response.payout.admin_fee) + parseFloat(response.payout.tds) + parseFloat(response.payout.total_payout) + parseFloat(this.affiliate_bonus);
        this.temp.totalDeductions   = parseFloat(response.payout.admin_fee) + parseFloat(response.payout.tds);
        this.temp.netPayble         = parseFloat(response.payout.total_payout) + parseFloat(response.affiliter);

        response.incomes.forEach(function (nObj) {
            console.log(nObj['income'].name)

            if(nObj['income'].name == "Squad Bonus"){
                scope.squad_bonus = parseFloat(nObj.payout_amount) + parseFloat(nObj.admin_fee)+parseFloat(nObj.tds);
            }
            if(nObj['income'].name == "Elevation Bonus"){
                scope.elevation_bonus = parseFloat(nObj.payout_amount) + parseFloat(nObj.admin_fee)+parseFloat(nObj.tds);
                console.log(nObj['income'].name);
            }
            if(nObj['income'].name == "Luxury Bonus"){
                scope.luxury_bonus = parseFloat(nObj.payout_amount) + parseFloat(nObj.admin_fee)+parseFloat(nObj.tds);
            }
            if(nObj['income'].name == "Premium Bonus"){
                scope.premium_bonus = parseFloat(nObj.payout_amount) + parseFloat(nObj.admin_fee)+parseFloat(nObj.tds);
            }
            
             
          
        })
    });
  },
  print(){
      const recaptcha = this.$recaptchaInstance;
      // recaptcha.hideBadge()
      
      window.print();
  },
  calculateFinal() {
      this.resetTemp();
      this.cartProducts.forEach((cart)=>{
          this.temp.subtotal+=parseFloat(cart.products.retail_amount)*parseInt(cart.qty);
          this.temp.total_gst+=parseFloat(cart.products.retail_gst)*parseInt(cart.qty);
          this.temp.shipping+=parseFloat(cart.products.shipping_fee)*parseInt(cart.qty);
          this.temp.admin+=parseFloat(cart.products.admin_fee)*parseInt(cart.qty);
          this.temp.discount+=parseFloat(cart.products.discount_amount)*parseInt(cart.qty);
          this.temp.grand_total=this.temp.subtotal+this.temp.total_gst+this.temp.shipping+this.temp.admin-this.temp.discount;
      });  
  },
}
};
</script>

<style scoped >
/*! Invoice Templates @author: Invoicebus @email: info@invoicebus.com @web: https://invoicebus.com @version: 1.0.0 @updated: 2015-02-27 16:02:34 @license: Invoicebus */
/* Reset styles */
@import url("https://fonts.googleapis.com/css?family=Open+Sans:400,700&subset=cyrillic,cyrillic-ext,latin,greek-ext,greek,latin-ext,vietnamese");
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed,
figure, figcaption, footer, header, hgroup,
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
  margin: 0;
  padding: 0;
  border: 0;
  font: inherit;
  font-size: 100%;
  vertical-align: baseline;
}

html {
  line-height: 1;
}

ol, ul {
  list-style: none;
}

table {
  border-collapse: collapse;
  border-spacing: 0;
}

caption, th, td {
  text-align: left;
  font-weight: normal;
  vertical-align: middle;
}

q, blockquote {
  quotes: none;
}
q:before, q:after, blockquote:before, blockquote:after {
  content: "";
  content: none;
}

a img {
  border: none;
}

article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
  display: block;
}

/* Invoice styles */
/**
 * DON'T override any styles for the <html> and <body> tags, as this may break the layout.
 * Instead wrap everything in one main <div id="container"> element where you may change
 * something like the font or the background of the invoice
 */
html, body {
  /* MOVE ALONG, NOTHING TO CHANGE HERE! */
}

/** 
 * IMPORTANT NOTICE: DON'T USE '!important' otherwise this may lead to broken print layout.
 * Some browsers may require '!important' in oder to work properly but be careful with it.
 */
.clearfix {
  display: block;
  clear: both;
}

.hidden {
  display: none;
}

b, strong, .bold {
  font-weight: bold;
}

#container {
  font: normal 13px/1.4em 'Open Sans', Sans-serif;
  margin: 0 auto;
  /*min-height: 1158px;*/
    height: 923px;
  background: #F7EDEB url("/images/bg.png") 0 0 no-repeat;

  /*background: url("/images/bg.png")  0 0 no-repeat;*/
  background-size: 100% auto;
  color: #5B6165;
  position: relative;
}

#memo {
    padding-top: 7px;
    /*margin: 26px 110px 0 60px;*/
    margin: 19px 34px 0 26px;

    border-bottom: 1px solid #ddd;
    height: 124px;  
}
#memo .logo {
  float: left;
  margin-right: 20px;
}
#memo .logo img {
  width: 98px;
  height: 100px;
}
#memo .company-info {
  float: right;
  text-align: right;
}
#memo .company-info > div:first-child {
     line-height: 82px;
    font-weight: bold;
    font-size: 49px;
    color: #B32C39;
    text-transform: uppercase;
}
#memo .company-info span {
     font-size: 15px;
    display: inline-block;
    min-width: 20px;
    font-weight: 600;
}
#memo:after {
  content: '';
  display: block;
  clear: both;
}

#invoice-title-number {
  font-weight: bold;
  margin: 26px 0;
  text-align: center;
}
#invoice-title-number span {
  line-height: 0.88em;
  display: inline-block;
  min-width: 20px;
}
#invoice-title-number #title {
  text-transform: uppercase;
  padding: 4px 34px 3px 60px;
  font-size: 26px;
  background: #b32f39;
  color: white;
  width: 100%;
    text-align: center;
    padding: 10px;
    border-radius: 4px;
}
#invoice-title-number #number {
  margin-left: 10px;
  font-size: 35px;
  position: relative;
  top: -5px;
}

#client-info-name {
  float: left;
  margin-left: 60px;
  min-width: 220px;
  text-align: left;
  font-size: 25px;
  line-height: 22px;
  width: 50%;
  margin-bottom: 10px;
}
#client-info {
  float: left;
  margin-left: 60px;
  min-width: 220px;
  text-align: left;
  font-size: 25px;
  line-height: 22px;
  width: 50%;
}
#client-info-rank {
  float: left;
  margin-left: 60px;
  min-width: 220px;
  text-align: left;
  font-size: 16px;
  line-height: 22px;
}
#client-info > div {
  margin-bottom: 3px;
  min-width: 20px;
}
#client-info span {
  min-width: 20px;
}
#client-info > span {
  text-transform: uppercase;
}

table {
  table-layout: fixed;
}
table th, table td {
  vertical-align: top;
  word-break: keep-all;
  word-wrap: break-word;
  font-size: 26px;
  text-align: left ;
  font-weight: 600;
}

#template_pos{
    margin-left: 480px;box-shadow: rgba(0, 0, 0, 0.25) 0px 2px 20px, rgba(0, 0, 0, 0.22) 0px 0px 8px;
    width: 568px;
}
#items {
  margin: 35px 26px 0 26px;
}
#items .first-cell, #items table th:first-child, #items table td:first-child {
    text-align: left !important;
  width: 179px !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
  text-align: left;
}
#items table {
  border-collapse: separate;
  width: 100%;
}
#items table th {
  font-weight: bold !important;
  padding: 13px 10px 12px 4px !important;
  text-align: right !important;
  color: white !important;
  background: #B32C39 !important;
  text-transform: uppercase;
}
#items table th:nth-child(2) {
  width: 30%;
  text-align: left;
}
#items table th:last-child {
  text-align: right;
}
#items table td {
  padding: 9px 8px;
  text-align: right;
  border-bottom: 1px solid #ddd;
}
#items table td:nth-child(2) {
  text-align: left;
}

#sums {
  margin: 25px 26px 0 0;
  background: url("/images/total-stripe-firebrick.png") right bottom no-repeat;
}
#sums table {
  float: right;
}
#sums table tr th, #sums table tr td {
  min-width: 100px;
  padding: 9px 8px;
  text-align: right;

}
tr td{
    padding-top: 28px !important;
    font-family: sans-serif;
}
#sums table tr th {
  font-weight: bold;
  text-align: left;
  padding-right: 35px;
}
#sums table tr td.last {

  background: url("/images/total-stripe-firebrick.png") right bottom no-repeat !important;
  min-width: 0 !important;
  max-width: 0 !important;
  width: 0 !important;
  padding: 0 !important;
  border: none !important;
}

.add {
    background: url("/images/total-stripe-firebrick.png") right bottom no-repeat !important;
    border: none !important;
    color: white;
}
#sums table tr.amount-total th {
  text-transform: uppercase;
}
#sums table tr.amount-total th, #sums table tr.amount-total td {
  font-size: 27px;
  font-weight: bold;
}
#sums table tr:last-child th {
  text-transform: uppercase;
    background: url("/images/total-stripe-firebrick.png") right bottom no-repeat !important;
}
#sums table tr:last-child th, #sums table tr:last-child td {
  font-size: 27px;
  font-weight: bold;
  color: white;
    background: url("/images/total-stripe-firebrick.png") right bottom no-repeat !important;
}

#invoice-info {
  float: left;
  margin: 50px 40px 0 60px;
}
#invoice-info > div > span {
  display: inline-block;
  min-width: 20px;
  min-height: 18px;
  margin-bottom: 3px;
}
#invoice-info > div > span:first-child {
  color: black;
}
#invoice-info > div > span:last-child {
  color: #aaa;
}
#invoice-info:after {
  content: '';
  display: block;
  clear: both;
}

#terms {
  float: left;
  margin-top: 50px;
}
#terms .notes {
  min-height: 26px;
  min-width: 50px;
  color: #B32C39;
}
#terms .payment-info div {
  margin-bottom: 3px;
  min-width: 20px;
}

.thank-you {
  margin: 10px 0 26px 0;
  display: inline-block;
  min-width: 20px;
  text-transform: uppercase;
  font-weight: bold;
  line-height: 0.88em;
  float: right;
  padding: 0px 26px 0px 2px;
  font-size: 50px;
  background: #F4846F;
  color: white;
}

.ib_bottom_row_commands {
  margin-left: 26px !important;
}

#rankparent{
        margin-bottom: 23px;
    min-width: 20px;
    margin-left: 0px;
}
#rank{
    font-weight: 600;
    width: 90%;
    text-align: center;
    margin-top: 21px;
    border-radius: 54px;
    padding: 9px;
    color: #f7f5f5;
    font-size: 27px;
}

 .imageparent{
      float: right;margin-top: -90px;
    }
    .childimage{
    width: 136px;height: 100%; margin: 16px;
  }

@media (max-width:450px) {
    #template_pos{
        margin-left: 0px;
        box-shadow: rgba(0, 0, 0, 0.25) 0px 2px 20px, rgba(0, 0, 0, 0.22) 0px 0px 8px;
    }

   .imageparent{
      float: right;margin-top:-76px;
    }
    .childimage{
      width: 136px;height: 100%; margin: 16px;
    }

}

@media print {
    table {  -webkit-print-color-adjust: exact;}
  .print-btn {
        display: none;
    }
    #template_pos{
        margin-left: 0px;
        box-shadow: rgba(0, 0, 0, 0.25) 0px 2px 20px, rgba(0, 0, 0, 0.22) 0px 0px 8px;
    }

    #container {
        height: 923px !important;
    }
    #invoice-title-number #title {
        box-shadow: inset 0 0 0 1000px #b32f39;
         -webkit-print-color-adjust: exact !important;
    }
 
}

</style>
