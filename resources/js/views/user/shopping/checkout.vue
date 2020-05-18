<template>
  <div class="app-container">
    <el-steps :active="step" finish-status="success" :process-status="processStatus" simple style="margin-top: 20px">
      <el-step title="Items" @click="step==0" ></el-step>
      <el-step title="Delivery and Payment" ></el-step>
      <el-step title="Confirmation" ></el-step>
    </el-steps>

    <el-row :gutter="10" v-if="step==0" style="margin-top: 20px;">
      <el-col  :xs="24" :sm="24" :md="16" :lg="16" :xl="16" >
        <div class="shopping-cart">
          <div class="title">
            Items
          </div>
         
          <div class="item"  v-for="product in cartProducts" :key="product.id">
            <div class="buttons">
              <el-button                
                type="danger"
                icon="el-icon-delete"
                circle
                :loading="buttonLoading"
                @click="removeFromCart(product.product_id)"
              ></el-button>
            </div>
         
            <!-- <div class="image">
              <img :src="product.products.cover_image_thumbnail" alt="" style="max-height: 78px;max-width: 78px;" />
            </div> -->
         
            <div class="description">
              <span>{{product.products.name}}</span>
            </div>
         
            <div class="quantity">
             
              <el-input style="width: 80px;" v-model="product.qty" @change="updateCartQty(product.product_id,product.qty)" type="number"  min="1"  :max="product.products.stock" />
            </div>
            <div class="total-price">{{product.products.pv*product.qty}} PV</div>
            <div class="total-price">₹ {{product.products.retail_amount*product.qty}}</div>
          </div>
        </div>
      </el-col>
      <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
        <div class="shopping-cart">
          <div class="title">
            Order Amount
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Subtotal</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.subtotal}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>GST</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.total_gst}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Shipping</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.shipping}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Admin Charge</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.admin}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Discount</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.discount}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Total PV</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.pv}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-grand">
              <span>Grand Total</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.grand_total}}</span></div>
          </div>

          <div class="checkout-btn make-payment-btn">
            <el-button 
                class="checkout"               
                type="success"
                round
                size="large"
                icon="el-icon-shopping-cart-full"                
                :loading="buttonLoading"
                @click="step=1"
              >Place Order</el-button>
          </div>
        </div>
      </el-col>
    </el-row>
    <el-row :gutter="20" v-if="step==1" style="margin-top: 20px;">
      <div class="filter-container">        
        <el-button
          class="filter-item"
          style="margin-left: 10px;"
          type="warning"   
           @click="step=0"     
          ><i class="fas fa-arrow-left"></i> Back</el-button>
        <el-button
          class="filter-item"
          style="margin-right: 10px;float: right;"
          type="success"   
           @click="$router.push('/my/addresses')"     
          ><i class="fas fa-plus"></i> Add Address</el-button>      
      </div>
      <el-form ref="orderForm" :rules="orderRules" :model="temp"  >
        
        <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
          <el-form-item label="Shipping Address" prop="shipping_address_id">
            <br>
            <el-select style="width: 100%" v-model="temp.shipping_address_id" @change="selectShippingAddress()" clearable autocomplete="off" filterable placeholder="Select Shipping Address">
              <el-option
                v-for="item in addresses"
                :key="item.id"
                :label="item.full_name"
                :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>            
          <el-form-item label="Full Name"  prop="full_name">
            <el-input disabled v-model="shipping_address.full_name" />
          </el-form-item>
          <el-form-item label="Address" prop="address">
            <el-input
              type="textarea"
              disabled
              :rows="2"
              placeholder="Address"
              v-model="shipping_address.address">
            </el-input>
          </el-form-item>
          <el-form-item label="Lanmark"  prop="landmark">
            <el-input disabled v-model="shipping_address.landmark" />
          </el-form-item>
          <el-row :gutter="5">
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="Pincode"  prop="pincode">
                <el-input disabled v-model="shipping_address.pincode" />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="Mobile Number"  prop="mobile_number">
                <el-input disabled v-model="shipping_address.mobile_number" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="5">
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="City"  prop="city">
                <el-input disabled v-model="shipping_address.city" />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="State"  prop="state">
                <el-input disabled v-model="shipping_address.state" />
              </el-form-item>
            </el-col>
          </el-row>
        </el-col>
        <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
          <el-form-item label="Billing Address" prop="billing_address_id">
            <br>
            <el-select style="width: 100%" v-model="temp.billing_address_id" @change="selectBillingAddress()" clearable autocomplete="off" filterable placeholder="Select billing Address">
              <el-option
                v-for="item in addresses"
                :key="item.full_name"
                :label="item.full_name"
                :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>            
          <el-form-item label="Full Name"  prop="full_name">
            <el-input disabled v-model="billing_address.full_name" />
          </el-form-item>
          <el-form-item label="Address" prop="address">
            <el-input
              type="textarea"
              disabled
              :rows="2"
              placeholder="Address"
              v-model="billing_address.address">
            </el-input>
          </el-form-item>
          <el-form-item label="Lanmark"  prop="landmark">
            <el-input disabled v-model="billing_address.landmark" />
          </el-form-item>
          <el-row :gutter="5">
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="Pincode"  prop="pincode">
                <el-input disabled v-model="billing_address.pincode" />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="Mobile Number"  prop="mobile_number">
                <el-input disabled v-model="billing_address.mobile_number" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="5">
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="City"  prop="city">
                <el-input disabled v-model="billing_address.city" />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="State"  prop="state">
                <el-input disabled v-model="billing_address.state" />
              </el-form-item>
            </el-col>
          </el-row>
        </el-col>
        <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
          <div class="shopping-cart" style="margin-top: 20px;">
            <div class="title">
              Wallet Balance : ₹ {{balance}}
            </div>
            <div class="payment-mode-div">
              <div class="payment-mode">
                <el-form-item label=""  prop="payment_mode">
                  <el-select style="width: 100%" v-model="temp.payment_mode" autocomplete="off" filterable placeholder="Payment Mode">
                    <el-option
                      v-for="item in payment_modes"
                      :key="item.name"
                      :label="item.name"
                      :value="item.name"
                      :disabled="item.disabled?item.disabled:false"
                      >
                    </el-option>
                  </el-select>
                </el-form-item>
              </div>
            </div>
            <div class="calculations">
              <div class="cal-grand">
                <span>Amount Payable</span>
              </div>         
              <div class="cal-amount"><span>₹ {{temp.grand_total}}</span></div>
            </div>

            <div class="make-payment-btn">
              <el-button 
                  class="checkout"               
                  type="success"
                  round
                  size="large"
                  icon="el-icon-shopping-cart-full"                
                  :loading="buttonLoading"
                  @click="placeOrder()"
                >Make Payment</el-button>
            </div>
          </div>
        </el-col> 
      </el-form>
    </el-row>
    <el-row :gutter="10" v-if="step==2" style="margin-top: 20px;">
      <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
        <div class="order-success">
          <center>
            <h4>Your order has been placed successfully</h4>
            <h1 style="text-align: center;">Order No #{{order_no}}</h1>
            <img :src="orderSuccess" alt="" style="max-height: 350px;max-width: 350px;" />
          </center>
        </div>
      </el-col>
    </el-row>

  </div>
</template>

<script>
import { getMyCart, addToCart, removeFromCart, updateCartQty, placeOrder } from "@/api/user/shopping";
import { getAllAddresses } from "@/api/user/addresses";
import { getMyBalance } from "@/api/user/wallet";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import orderSuccess from "@/assets/images/order-success.jpg";

export default {
  name: "transfer-pin",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        'Not Used': "success",
        Used: "info",
        Rejected: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      orderSuccess:orderSuccess,
      processStatus:'process',
      step:0,
      tableKey: 0,
      list: [],
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 12,
        search:undefined,
        package_id: undefined,
        sort: "+id"
      },
      categories:[],
      cartProducts:[],
      temp: {
        subtotal:0,
        total_gst: 0,
        shipping:0,
        admin:0,
        discount:0,
        grand_total:0,
        shipping_address_id:undefined,
        billing_address_id:undefined,
        pv:0,
        payment_mode:1,

      },
      billing_address:{
        full_name:undefined,
        mobile_number:undefined,
        pincode:undefined,
        address:undefined,
        landmark:undefined,
        city:undefined,
        state:undefined,
      },
      shipping_address:{
        full_name:undefined,
        mobile_number:undefined,
        pincode:undefined,
        address:undefined,
        landmark:undefined,
        city:undefined,
        state:undefined,
      },
      payment_modes:[
        {
          id:1,
          name:'Wallet'
        },
        {
          id:2,
          name:'Online',
          disabled:true,
        },
        {
          id:3,
          name:'Paytm',
          disabled:true,
        },
      ],
      addresses:[],
      order_no:undefined,
      balance:0.00,
      is_create:true,      
      downloadLoading: false,
      buttonLoading: false,
      orderRules: {
        shipping_address_id: [{ required: true, message: 'Shipping Adddress is required', trigger: 'change' }],
        payment_mode: [{ required: true, message: 'Please select payment mode.', trigger: 'change' }]
      },
    };
  },
  created() {    
    this.getMyCart();
    this.getAllAddresses();
    this.getMyBalance();
  },
  methods: {        
    getMyCart(){
      getMyCart().then(response => {
        this.cartProducts = response.data;   
        this.calculateFinal();
        if(this.cartProducts.length==0){
          this.$router.push('/shopping/products')
        }     
      });
    },
    getAllAddresses(){
      getAllAddresses().then(response => {
        this.addresses = response.data; 
      });
    },
    getMyBalance(){
      getMyBalance().then(response => {
        this.balance = response.data; 
      });
    },
    selectShippingAddress(){      
      let address=this.addresses.filter((address)=>{
        return address.id==this.temp.shipping_address_id
      });
      if(address[0]){
        this.shipping_address=address[0];  
      }
      
    },
    selectBillingAddress(){      
      let address=this.addresses.filter((address)=>{
        return address.id==this.temp.billing_address_id
      });
      if(address[0]){
        this.billing_address=address[0];  
      }
      
    },
    calculateFinal() {
      this.resetTemp();
        this.cartProducts.forEach((cart)=>{
          this.temp.subtotal+=parseFloat(cart.products.retail_base)*parseInt(cart.qty);
          this.temp.total_gst+=parseFloat(cart.products.retail_gst)*parseInt(cart.qty);
          this.temp.shipping+=parseFloat(cart.products.shipping_fee)*parseInt(cart.qty);
          this.temp.admin+=parseFloat(cart.products.admin_fee)*parseInt(cart.qty);
          this.temp.discount+=parseFloat(cart.products.discount_amount)*parseInt(cart.qty);
          this.temp.pv+=parseFloat(cart.products.pv)*parseInt(cart.qty);
          this.temp.grand_total=this.temp.subtotal+this.temp.total_gst+this.temp.shipping+this.temp.admin-this.temp.discount;
        });  
    },
    resetTemp(){
      this.temp= {
        subtotal:0,
        total_gst: 0,
        shipping:0,
        admin:0,
        discount:0,
        pv:0,
        grand_total:0,

      };
    },
    placeOrder(){   
      this.$refs["orderForm"].validate(valid => {
        if (valid) { 
          if(parseFloat(this.balance) < parseFloat(this.temp.grand_total)){
            this.$message.error('Oops, You have not enough balance to place order.');
            return;
          }
          this.buttonLoading=true;        
          placeOrder(this.temp).then((response) => {
            this.step=2;
            this.processStatus='success';
            this.order_no=response.data.order_no;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
          }).catch((error)=>{
            this.buttonLoading=false;  
          });
        }
      })
      
    },
    updateCartQty(id,qty){
      let tempData={
        'product_id':id,
        'qty':qty,
      };
      updateCartQty(tempData).then((response) => {
        this.buttonLoading=false;
        this.getMyCart();
      });      
    },
    removeFromCart(id){      
      this.buttonLoading=true;
      removeFromCart(id).then((response) => {
        this.buttonLoading=false;
        this.getMyCart();
        this.$events.fire('update-cart-count');
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        });            
      });      
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
  }
};
</script>

<style scoped >
* {
  box-sizing: border-box;
}

html,
body {
  width: 100%;
  height: 100%;
  padding: 10px;
  margin: 0;
  background-color: #7EC855;
  font-family: 'Roboto', sans-serif;
}

.shopping-cart {
 
  background: #FFFFFF;
  /*  box-shadow: 0px 1px 10px 5px rgba(0,0,0,0.10);
  border-radius: 6px;*/
  border: 1px solid #ccc;
  display: flex;
  flex-direction: column;
}
.order-success{
  margin:0 auto;
  width: 50%;
}
.title {
  height: 60px;
  border-bottom: 1px solid #E1E8EE;
  padding: 20px 30px;
  color: #5E6977;
  font-size: 18px;
  font-weight: 400;
}

.calculations {
  padding: 5px 7px;
  height: 40px;
  display: flex;
}

.calculations {
 /* border-top:  1px solid #E1E8EE;*/
  border-bottom:  1px solid #E1E8EE;
}


.item {
  padding: 0px 15px;
  height: 65px;
  display: flex;
}

.item {
  /*border-top:  1px solid #E1E8EE;*/
  border-bottom:  1px solid #E1E8EE;
}

/* Buttons -  Delete and Like */
.buttons {
  position: relative;
  padding-top: 12px;
  margin-right: 60px;
}

.delete-btn {
  display: inline-block;
  cursor: pointer;
  width: 18px;
  height: 17px;
  
  margin-right: 20px;
}

.like-btn {
  position: absolute;
  top: 9px;
  left: 15px;
  display: inline-block;
  
  width: 60px;
  height: 60px;
  background-size: 2900%;
  background-repeat: no-repeat;
  cursor: pointer;
}

.is-active {
  animation-name: animate;
  animation-duration: .8s;
  animation-iteration-count: 1;
  animation-timing-function: steps(28);
  animation-fill-mode: forwards;
}

@keyframes animate {
  0%   { background-position: left;  }
  50%  { background-position: right; }
  100% { background-position: right; }
}

/* Product Image */
.image {
  margin-right: 50px;
  width: 100px;
  text-align:center;
}

/* Product Description */
.description {
  padding-top: 10px;
  margin-right: 60px;
  width: 250px;
}

.description span {
  display: block;
  font-size: 14px;
  color: #43484D;
  font-weight: 400;
}

.description span:first-child {
  margin-bottom: 5px;
}
.description span:last-child {
  font-weight: 300;
  margin-top: 8px;
  color: #86939E;
}

.cal-title {
  width: 100%;
}

.cal-title span {
  margin-left: 25px;
  display: block;
  font-size: 16px;
  color: #6c7175;
  font-weight: 400;
}

.cal-grand {
  width: 100%;
}
.cal-grand span {
  margin-left: 25px;
  display: block;
  font-size: 16px;
  margin-top: 5px;
  color: #5d5d5d;
  font-weight: 400;
}

.payment-mode-div  {
    height: 55px;
    border-bottom:  1px solid #E1E8EE;
}
.payment-mode  {
  margin: 10px 20px 10px 20px;
}

.cal-title span:first-child {
  margin-bottom: 5px;
}
.cal-title span:last-child {
  font-weight: 300;
  margin-top: 8px;
  color: #86939E;
}

/* Product Quantity */
.quantity {
  padding-top: 10px;
  margin-right: 10px;
}
.quantity input {
  -webkit-appearance: none;
  border: none;
  text-align: center;
  width: 32px;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

.checkout-btn{
  padding: 15px 15px 15px 15px;
}

.make-payment-btn{
  padding: 15px 15px 15px 15px;
  margin:0 auto;
}

.checkout-btn button{
  float: right;
}


button[class*=btn] {
  width: 30px;
  height: 30px;
  background-color: #E1E8EE;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}
.minus-btn img {
  margin-bottom: 3px;
}
.plus-btn img {
  margin-top: 2px;
}
button:focus,
input:focus {
  outline:0;
}

/* Total Price */
.total-price {
  width: 83px;
  padding-top: 20px;
  text-align: center;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

.cal-amount {  
  width: 100%;
  margin-right: 25px;
  padding-top: 8px;
  text-align: right;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

/* Responsive */
@media (max-width: 800px) {
  .shopping-cart {
    width: 100%;
    height: auto;
    overflow: hidden;
  }
  .item {
    height: auto;
    flex-wrap: wrap;
    justify-content: center;
  }
  .image img {
    max-height: 50px;
    max-width: 50px;
  }
  .image,
  .quantity,
  .description {
    width: 100%;
    text-align: center;
    margin: 6px 0;
  }
  .buttons {
    margin-right: 20px;
  }
}

.el-form-item--medium .el-form-item__content, .el-form-item--medium .el-form-item__label {
    line-height: 30px;
}

.el-form-item {
    margin-bottom: 10px;
}

</style>
