<template>
  <div class="app-container">
    
   <!--  <el-row>
      <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" style="margin-right: 10px;">
        <h1 >My Cart<br><br><br></h1>
      </el-col>
    </el-row> -->
    <el-row :gutter="10">
      <el-col  :xs="24" :sm="24" :md="16" :lg="16" :xl="16" >
        <div class="shopping-cart">
          <!-- Title -->
          <div class="title">
            My Cart
          </div>
         
          <!-- Product #1 -->
          <div class="item"  v-for="product in cartProducts" :key="product.id" v-if="cartProducts">
            <div class="buttons">
              <el-button                
                type="danger"
                icon="el-icon-delete"
                circle
                :loading="buttonLoading"
                @click="removeFromCart(product.product_id)"
              ></el-button>
            </div>
         
            <div class="image" v-lazy-container="{ selector: 'img' }">
              <img :data-src="product.products.cover_image_thumbnail"  data-loading="images/fallback-product.png" alt="" style="max-height: 78px;max-width: 78px;" />
            </div>
         
            <div class="description">
              <span>{{product.products.name}}</span>
            </div>
         
            <div class="quantity">
             
              <el-input style="width: 80px;" v-model="product.qty" @change="updateCartQty(product.product_id,product.qty)" type="number"  min="1"  :max="product.products.stock" />
            </div>
         
            <div class="total-price">₹ {{product.products.retail_amount*product.qty}}</div>
          </div>
          <div class="empty-cart" v-if="cartProducts.length == 0">
              <h2 style="text-align: center;">Your cart is empty, buy something.</h2>
              <img :src="emptyCart" alt="" style="max-height: 400px;max-width: 400px;" />
          </div>
        </div>
      </el-col>
      <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
        <div class="shopping-cart" v-if="cartProducts.length != 0">
          <!-- Title -->
          <div class="title">
            Cart Total
          </div>          
          <div class="calculations">
            <div class="cal-grand">
              <span>Total</span>
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
        <!--   <div class="calculations">
            <div class="cal-title">
              <span>Distributor Discount</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.distributor_discount}}</span></div>
          </div> -->
         <!--  <div class="calculations">
            <div class="cal-title">
              <span>Product Discount</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.discount}}</span></div>
          </div> -->
          <div class="calculations">
            <div class="cal-title">
              <span>Total PV</span>
            </div>         
            <div class="cal-amount"><span>{{temp.pv}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-grand">
              <span>Grand Total</span>
            </div>         
            <div class="cal-amount"><span>₹ {{temp.grand_total}}</span></div>
          </div>

          <div class="checkout-btn make-payment-btn" v-if="cartProducts.length != 0">
            <el-button 
                class="checkout"               
                type="success"
                round
                size="large"
                icon="el-icon-shopping-cart-full"                
                :loading="buttonLoading"
                @click="$router.push('/shopping/checkout')"
              >Checkout</el-button>
          </div>
          <div class="checkout-btn make-payment-btn" v-if="cartProducts.length == 0">
            <el-button 
                class="checkout"               
                type="success"
                round
                size="large"
                icon="el-icon-shopping-cart-full"                
                :loading="buttonLoading"
                @click="$router.push('/shopping/products')"
              >Go to Products</el-button>
          </div>
        </div>
      </el-col>
    </el-row>

  </div>
</template>

<script>
import { getMyCart, addToCart, removeFromCart, updateCartQty } from "@/api/user/shopping";
import { getSettings } from "@/api/user/settings";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import emptyCart from "@/assets/images/empty-cart.jpg";

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
      emptyCart:emptyCart,
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
      settings:{shipping_charge:0},
      temp: {
        subtotal:0,
        total_gst: 0,
        shipping:0,
        admin:0,
        discount:0,
        grand_total:0,
        pv:0,
        discount:0,
        distributor_discount:0,

      },
      is_create:true,      
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getSettings();    
    this.getMyCart();    
  },
  methods: {        
    getMyCart(){
      getMyCart().then(response => {
        this.cartProducts = response.data;   
        this.calculateFinal();
        this.temp.shipping=parseFloat(this.settings.shipping_charge);
        this.temp.grand_total+=this.temp.shipping;
      });
    },
    getSettings() {      
      getSettings().then(response => {
        this.settings = response.data  
          this.temp.shipping=parseFloat(this.settings.shipping_charge);
          this.temp.grand_total+=this.temp.shipping;
      });
    },
    calculateFinal() {
      this.resetTemp();
        this.cartProducts.forEach((cart)=>{
          this.temp.subtotal+=parseFloat(cart.products.retail_base)*parseInt(cart.qty);
          // this.temp.distributor_discount+=parseFloat(cart.products.retail_amount-cart.products.dp_amount)*parseInt(cart.qty);
          this.temp.total_gst+=parseFloat(cart.products.retail_gst)*parseInt(cart.qty);
          // this.temp.shipping+=parseFloat(cart.products.shipping_fee)*parseInt(cart.qty);
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
        grand_total:0,
        pv:0,
        discount:0,
        distributor_discount:0,

      };
    },
    updateCartQty(id,qty){
      let tempData={
        'product_id':id,
        'qty':qty,
      };
      updateCartQty(tempData).then((response) => {
        this.buttonLoading=false;
        this.getMyCart();
      }).catch((err)=>{
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

.empty-cart{
  margin:0 auto;
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
  padding: 20px 30px;
  height: 120px;
  display: flex;
}

.item {
  /*border-top:  1px solid #E1E8EE;*/
  border-bottom:  1px solid #E1E8EE;
}

/* Buttons -  Delete and Like */
.buttons {
  position: relative;
  padding-top: 25px;
  margin-right: 30px;
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

.make-payment-btn{
  padding: 15px 15px 15px 15px;
  margin:0 auto;
}


/* Product Image */
.image {
  margin-right: 30px;
  width: 100px;
  text-align:center;
}

/* Product Description */
.description {
  padding-top: 10px;
  margin-right: 30px;
  width: 160px;
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
  font-size: 20px;
  margin-top: 4px;
  color: #5d5d5d;
  font-weight: 400;
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
  padding-top: 20px;
  margin-right: 60px;
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
  padding-top: 27px;
  text-align: center;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

.cal-amount {  
  width: 70%;
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
</style>
