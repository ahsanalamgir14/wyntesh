<template>
  <div class="app-container">
    
    <el-row>
      <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
        <div id="page-wrap">

          <table id="items" style="border: none;">
            
              <tr class="item-row" >
                  <td class="item-name" style="border: none;">
                     <img id="image" src="images/dark_logo.png" height="150px"   alt="logo" style="width: 50% !important;height: 50% !important;" /><br>
                    <span style="font-size: 18px;font-weight: bold;">{{company_details.company_name}}</span><br><br>
                    {{company_details.address}}<br>
                    {{company_details.city}}<br>
                    {{company_details.state}}, {{company_details.pincode}}<br>
                    {{company_details.contact_phone}}<br>
                    {{company_details.contact_email}}<br>
                    GSTN - 19AAHCV1338G1Z8<br></td>
                  
                  <td class="cost" style ="vertical-align:bottom;border: none;"> 
                    <table id="meta" style="border: none;">
                      <tr>
                          <td class="meta-head">Invoice #</td>
                          <td><div>{{order.order_no}}</div></td>
                      </tr>
                      <tr>
                          <td class="meta-head">ORDER #</td>
                          <td><div>{{order.order_no}}</div></td>
                      </tr>
                      <tr>

                          <td class="meta-head">Invoice Date</td>
                          <td><div id="date">{{ order.created_at | parseTime('{d}-{m}-{y}') }}</div></td>
                      </tr>
                       <tr>

                          <td class="meta-head">Order Date</td>
                          <td><div id="date">{{ order.created_at | parseTime('{d}-{m}-{y}') }}</div></td>
                      </tr>

                  </table></td>
              </tr>
          </table>

          <table id="items">
            
              <tr class="item-row" >
                  <td class="item-name">
                    
                    <span style="font-size: 15px;font-weight: bold;">Shipping to</span><br><br>
                    <span style="font-size: 15px;font-weight: bold;">{{order.shipping_address.full_name}}</span><br>
                    <span style="font-size: 15px;">{{order.shipping_address.address}}, {{order.shipping_address.landmark}}, {{order.shipping_address.city}}, {{order.shipping_address.state}}, {{order.shipping_address.pincode}}</span>
                  </td>
                  <td class="item-name">
                    
                    <span style="font-size: 15px;font-weight: bold;">Billing to</span><br><br>
                    <span style="font-size: 15px;font-weight: bold;">{{order.billing_address.full_name}}</span><br>
                    <span style="font-size: 15px;">{{order.billing_address.address}}, {{order.billing_address.landmark}}, {{order.billing_address.city}}, {{order.billing_address.state}}, {{order.billing_address.pincode}}</span>
                  </td>
                  
              </tr>
          </table>

          
          <table id="items">
          
            <tr>
                <th>Product</th>                
                <th>MRP</th>
                <!-- <th>retail</th> -->
                <!-- <th>Discount</th> -->
                <th>Quantity</th>
                <th>PV</th>
                <th>GST</th>
                <th>GST %</th>
                <th>Total</th>
            </tr>
            
            <tr class="item-row"  v-for="product in order.products" :key="product.id">
                <td class="item-name"><div>{{product.product.name}}</div></td>
                <td class="cost"><div >{{product.amount}}</div></td>
                <!-- <td class="cost"><div >{{product.product.retail_amount}}</div></td> -->
                <!-- <td class="cost"><div >{{product.product.retail_amount-product.product.retail_amount}}</div></td> -->
                <td class="qty"><div >{{product.qty}}</div></td>
                <td class="description"><div>{{product.pv}}</div></td>
                <td class="description"><div>{{product.gst}}</div></td>
                <td class="description"><div>{{product.gst_rate}}</div></td>

                <td class="total"><div >{{product.final_amount*product.qty}}</div></td>
            </tr>

            <tr>
              <td colspan="7"  style="padding-top:40px;border-top: none"></td>
            </tr>
            <tr>
                <td colspan="3" class="blank" rowspan="2"> <b>Total PV in this order: {{order.pv}}</b></td>
                <td colspan="3" class="total-line">Base Price Total</td>
                <td class="total-value"><div id="subtotal">{{order.amount}}</div></td>
            </tr>
            <tr>

                <td colspan="3" class="total-line">GST</td>
                <td class="total-value"><div id="total">{{order.gst}}</div></td>
            </tr>
            <tr>
                <td colspan="3" class="blank"> </td>
                <td colspan="3" class="total-line">Shipping</td>

                <td class="total-value"><div id="paid">{{order.shipping_fee}}</div></td>
            </tr>
            <tr>
                <td colspan="3" class="blank" rowspan="2"> 
                </td>
                <td colspan="3" class="total-line">Admin Charge</td>

                <td class="total-value"><div id="paid">{{order.admin_fee}}</div></td>
            </tr>
            <tr>
                <td colspan="3" class="total-line balance">Final Amount</td>
                <td class="total-value balance"><div class="due">{{order.final_amount}}</div></td>
            </tr>
          
          </table>
          
          <!-- <div id="terms">
            <h5>Terms</h5>
            <textarea>NET 30 Days. Finance Charge of 1.5% will be made on unpaid balances after 30 days.</textarea>
          </div> -->
        
        </div>
      </el-col>
    </el-row>
    <center style="margin-top: 20px;">
      <el-button type="success" class="print-btn" @click="print()">Print</el-button>
    </center>

  </div>
</template>

<script>
import { getMyCart, addToCart, removeFromCart, updateCartQty, getOrder } from "@/api/user/shopping";
import { parseTime } from "@/utils";

export default {
  name: "Invoice",
  data() {
    return {
      order:{},
      company_details:{},
      user:{},
    };
  },
  created() {    
    let order_id=this.$route.params.id;
    this.getOrder(order_id);
  },
  methods: {        
    getOrder(order_id){
      getOrder(order_id).then(response => {
        this.order = response.data;
        this.company_details = response.company_details;
        this.user = response.user;
        //this.calculateFinal();     
      });
    },
    print(){
      const recaptcha = this.$recaptchaInstance;
      recaptcha.hideBadge()
      
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
body { font: 14px/1.4 Georgia, serif; }
#page-wrap { width: 800px; margin: 0 auto; }

table { border-collapse: collapse; }
table td, table th { border: 1px solid black; padding: 5px; }

#header { height: 35px; width: 100%; margin: 20px 0; background: #222; text-align: center; color: white; font: bold 15px Helvetica, Sans-Serif; text-decoration: uppercase; letter-spacing: 20px; padding: 8px 0px; }

#address { margin-left: 20px;width: 100%; margin-bottom: 50px;  float: left; font: 14px Georgia, Serif;}
#customer { overflow: hidden; }

#logo {  float: left; position: relative;  border: 1px solid #fff;   width: 100%;}
#logoctr { display: none; }
#logohelp { text-align: left; display: none; font-style: italic; padding: 10px 5px;}
#logohelp input { margin-bottom: 5px; }
.edit #logohelp { display: block; }
.edit #save-logo, .edit #cancel-logo { display: inline; }
.edit #image, #save-logo, #cancel-logo, .edit #change-logo, .edit #delete-logo { display: none; }
#customer-title { font-size: 20px; font-weight: bold; float: left; }

#meta { margin-top: 1px; width: 300px; float: right; }
#meta td { text-align: right; border: none; }
#meta td.meta-head { text-align: right; }

#items { clear: both; width: 100%; margin: 30px 0 0 0; border: 1px solid black; }
#items th { background: #eee; }
#items tr.item-row td { vertical-align: top; }
#items td.description { width: 150px; text-align: right;}
#items td.qty {text-align: right;}
#items td.cost {text-align: right; width: 150px;}
#items td.total {text-align: right; width: 200px;}
#items td.item-name { width: 400px; }
#items td.total-line { border-right: 0; text-align: right; }
#items td.total-value { border-left: 0; padding: 10px; }
#items td.balance { background: #eee; }
#items td.blank {  }

#terms { text-align: center; margin: 20px 0 0 0; }
#terms h5 { text-transform: uppercase; font: 13px Helvetica, Sans-Serif; letter-spacing: 10px; border-bottom: 1px solid black; padding: 0 0 8px 0; margin: 0 0 8px 0; }
.grecaptcha-badge {
   display: none !important; 
  }

@media print {
  .print-btn {
    display: none;
  }
  .grecaptcha-badge {
   display: none !important; 
  }
}

</style>
