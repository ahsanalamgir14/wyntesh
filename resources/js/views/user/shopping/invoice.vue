<template>
  <div class="app-container" style="padding: 0">
    
    <el-row>
      <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
        <div id="page-wrap">

          <table id="items" style="border: none;">
            
              <tr class="item-row" >
                  <td class="item-name" style="border: none;">
                     <img id="image" src="images/dark_logo.png" height="150px"   alt="logo" style="width: 20% !important;" /><br>
                    <span style="font-size: 15px;font-weight: bold;">WYNTASH Ecom Pvt Ltd</span><p class="company-details">
                    CIN - U74999TZ2020PTC033882</p>
                    <p class="company-details">
                    {{company_details.address}}</p>
                    <p class="company-details">
                    {{company_details.city}}</p>
                    <p class="company-details">
                    {{company_details.state}}, {{company_details.pincode}}</p>
                    <p class="company-details">
                    {{company_details.contact_phone}}</p>
                    <p class="company-details">
                    {{company_details.contact_email}}</p>
                    <p class="company-details">
                    GSTN - 33AACW6547R1ZM</p></td>
                  
                  <td class="cost" style ="vertical-align:bottom;border: none;"> 
                    <table id="meta" style="border: none;">
                      <tr>
                          <td class="meta-head"><p class="company-details">TAX Invoice #</p></td>
                          <td><div><p class="company-details">{{order.order_no}}</p></div></td>
                      </tr>
                      <tr>
                          <td class="meta-head"><p class="company-details">ORDER #</p></td>
                          <td><div><p class="company-details">{{order.order_no}}</p></div></td>
                      </tr>
                      <tr>

                          <td class="meta-head"><p class="company-details">TAX Invoice Date</p></td>
                          <td><div id="date"><p class="company-details">{{ order.created_at | parseTime('{d}-{m}-{y}') }}</p></div></td>
                      </tr>
                       <tr>
 
                          <td class="meta-head"><p class="company-details">Order Date</p></td>
                          <td><div id="date"><p class="company-details">{{ order.created_at | parseTime('{d}-{m}-{y}') }}</p></div></td>
                      </tr>

                  </table></td>
              </tr>
          </table>

          <table id="items">
            
              <tr class="item-row" >
                  <td class="item-name">
                    
                   <p style="font-size: 12px;font-weight: bold;">Shipping to</p><br>
                    <p style="font-size: 12px;font-weight: bold;">{{order.user.name}}</p>
                    
                    <p style="font-size: 12px;">Member Id : {{order.user.username}}</p>
                    
                   
                    <p style="font-size: 12px;">{{order.shipping_address}}</p>
                  </td>
                  <td class="item-name">
                    
                    <!-- <p style="font-size: 12px;font-weight: bold;">Billing to</p><br>
                    <p style="font-size: 12px;font-weight: bold;">{{order.user.name}}</p>
                    <p style="font-size: 12px;">Member Id : {{order.user.username}}</p>
                    <p style="font-size: 12px;">{{order.billing_address}}</p> -->
                  </td>
                  
              </tr>
          </table>

          
          <table id="items">
          
            <tr>
                <th>Product</th>                
                <th>HSN</th>
                <th>Base Price</th>
                <!-- <th>retail</th> -->
                <!-- <th>Discount</th> -->
                <th>Quantity</th>
                <th>PV</th>
                <th>CGST %</th>
                <th>CGST</th>
                <th>SGST %</th>
                <th>SGST</th>
                <th>IGST %</th>
                <th>IGST</th>
                <th>Total</th>
            </tr>
            
            <tr class="item-row"  v-for="product in order.products" :key="product.id">
                <td class="item-name"><div>{{product.product.name}}</h3>{{ (product.variant.color?product.variant.color.name:' ') +' '+ (product.variant.size?'('+product.variant.size.brand_size+')':'') }}</div></td>
                <td class="qty"><div >{{product.product.hsn}}</div></td>
                
                <td class="cost"><div >{{order.is_combo?product.base_amount:product.product.dp_base}}</div></td>
                <!-- <td class="cost"><div >{{product.product.retail_amount}}</div></td> -->
                <!-- <td class="cost"><div >{{product.product.retail_amount-product.product.retail_amount}}</div></td> -->
                <td class="qty"><div >{{product.quantity}}</div></td>
                <td class="description"><div>{{product.pv}}</div></td>
                <td class="description"><div>{{product.cgst_rate}}</div></td>
                <td class="description"><div>{{product.cgst_amount}}</div></td>
                <td class="description"><div>{{product.sgst_rate}}</div></td>
                <td class="description"><div>{{product.sgst_amount}}</div></td>
                <td class="description"><div>{{product.gst_rate}}</div></td>
                <td class="description"><div>{{product.gst_amount}}</div></td>

                <td class="total"><div >{{product.net_amount}}</div></td>
            </tr>

            <tr class="item-row">
                <td class="item-name"><div>Shipping</div></td>
                <td class="qty"><div ></div></td>
                <td class="cost"><div >{{order.shipping_fee}}</div></td>
                <!-- <td class="cost"><div >{{product.product.retail_amount}}</div></td> -->
                <!-- <td class="cost"><div >{{product.product.retail_amount-product.product.retail_amount}}</div></td> -->
                <td class="qty"><div ></div></td>
                <td class="description"><div></div></td>
                <td class="description"><div>2.5</div></td>
                <td class="description"><div>{{parseInt(order.shipping_fee)*2.5/100}}</div></td>
                <td class="description"><div>2.5</div></td>
                <td class="description"><div>{{parseInt(order.shipping_fee)*2.5/100}}</div></td>
                <td class="description"><div></div></td>
                <td class="description"><div></div></td>

                <td class="total"><div >{{(parseInt(order.shipping_fee)*5/100)+parseInt(order.shipping_fee)}}</div></td>
            </tr>

            <tr>
              <td colspan="12"  style="padding-top:20px;border-top: none"></td>
            </tr>
            <tr>
                <td colspan="8" class="blank" rowspan="2"> <b>Total PV in this order: {{order.pv}}</b></td>
                <td colspan="3" class="total-line">Base Price Total</td>
                <td class="total-value"><div id="subtotal">{{order.base_amount}}</div></td>
            </tr>
            <tr>

                <td colspan="3" class="total-line">CGST</td>
                <td class="total-value"><div id="total">{{order.cgst_amount}}</div></td>
            </tr>
            <tr>
                <td colspan="8" class="blank"> </td>
                <td colspan="3" class="total-line">SGST</td>
                <td class="total-value"><div id="total">{{order.sgst_amount}}</div></td>
            </tr>
            <tr>
              <td colspan="8" class="blank"> </td>
                <td colspan="3" class="total-line">IGST</td>
                <td class="total-value"><div id="total">{{order.gst_amount}}</div></td>
            </tr>
            <tr>
                <td colspan="8" class="blank" rowspan="2"> </td>
                <td colspan="3" class="total-line">Shipping</td>

                <td class="total-value"><div id="paid">{{(parseInt(order.shipping_fee)*5/100)+parseInt(order.shipping_fee)}}</div></td>
            </tr>
            <tr>
                <td colspan="3" class="total-line balance"><strong>Final Amount</strong></td>
                <td class="total-value balance"><div class="due"><strong>{{parseInt(order.net_amount)+parseInt(order.shipping_fee)*5/100}}</strong></div></td>
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
      order:{
            shipping_address:{
                full_name : undefined
            },
            billing_address:{
                full_name : undefined
            }
            
        },
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
        // console.log(this.order.shipping_address.full_name);
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
#page-wrap { width: 1000px; margin: 0 auto; }

table { border-collapse: collapse; }
table td, table th { border: 1px solid black; padding: 1px; font-size: 12px;}

#header { height: 35px; width: 100%; margin: 20px 0; background: #222; text-align: center; color: white; font: bold 15px Helvetica, Sans-Serif; text-decoration: uppercase; letter-spacing: 10px; padding: 8px 0px; }

#address { margin-left: 20px;width: 100%; margin-bottom: 50px;  float: left; font: 14px Georgia, Serif;}
#customer { overflow: hidden; }

#logo {  float: left; position: relative;  border: 1px solid #fff;   width: 100%;}
#logoctr { display: none; }
#logohelp { text-align: left; display: none; font-style: italic; padding: 5px 5px;}
#logohelp input { margin-bottom: 5px; }
.edit #logohelp { display: block; }
.edit #save-logo, .edit #cancel-logo { display: inline; }
.edit #image, #save-logo, #cancel-logo, .edit #change-logo, .edit #delete-logo { display: none; }
#customer-title { font-size: 15px; font-weight: bold; float: left; }

#meta { margin-top: 1px; width: 300px; float: right; }
#meta td { text-align: right; border: none; }
#meta td.meta-head { text-align: right; }

#items { clear: both; width: 100%; margin: 5px 0 0 0; border: 1px solid black; }
#items th { background: #eee; }
#items tr.item-row td { vertical-align: top; }
#items td.description { width: 150px; text-align: right;}
#items td.qty {text-align: right; width: 90px;}
#items td.cost {text-align: right; width: 150px;}
#items td.total {text-align: right; width: 100px;}
#items td.item-name { width: 550px; }
#items td.total-line { border-right: 0; text-align: right; }
#items td.total-value { border-left: 0; padding: 1px;padding-left: 10px; }
#items td.balance { background: #eee; }
#items td.blank {  }

#terms { text-align: center; margin: 20px 0 0 0; }
#terms h5 { text-transform: uppercase; font: 13px Helvetica, Sans-Serif; letter-spacing: 5px; border-bottom: 1px solid black; padding: 0 0 8px 0; margin: 0 0 8px 0; }
.grecaptcha-badge {
   display: none !important; 
  }

  .company-details{
    font-size: 12px;
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
