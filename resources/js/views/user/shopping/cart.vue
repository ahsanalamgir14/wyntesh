<template>
  <div class="app-container">
    <el-row :gutter="10" class="pb-32">
      <div class="shopping-cart rounded-lg shadow " v-if="cartProducts.length == 0">
          <div class="empty-cart for_mobile_width " >
            <h2 style="text-align: center;" class="middel-text text-3xl mt-10 mb-10 text-gray-700 font-bold px-3 md:px-0">Your cart is empty, buy something.</h2>
            <img :src="emptyCart" alt="" style="max-height: 420px;" class="mx-auto" />
          </div>
      </div>
      <div v-else >
        <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16">
          <div class="shopping-cart rounded-lg shadow">
            <div class="title">
              Your Cart
            </div>
            <div class="item" v-for="product in cartProducts" :key="product.id" v-if="cartProducts">
              <div class="buttons">
                <el-button type="danger" icon="el-icon-delete" circle :loading="buttonLoading" @click="removeFromCart(product.variant_id)"></el-button>
              </div>
              <div class="image flex justify-center" v-lazy-container="{ selector: 'img' }">
                <img :data-src="product.products.cover_image_thumbnail" data-loading="images/fallback-product.png" alt="" style="max-height: 78px;max-width: 78px;" class="" />
              </div>
              <div class="description">
                <div class="text-gray-700 font-bold text-sm mt-3 ">{{ product.products.name }}</div>
                <div class="text-gray-500 font-bold text-sm  ">{{product.products.qty}}  {{product.products.qty_unit}}, {{ (product.variant.color?product.variant.color.name:' ') +' '+ (product.variant.size?'('+product.variant.size.brand_size+')':'') }}</div>
              </div>
              <div class="quantity">
                <el-input style="width: 80px;" v-model="product.qty" @change="updateCartQty(product.product_id,product.qty,product.variant.stock,product.variant_id)" type="number" min="1" :max="product.variant.stock" />
              </div>
              <div class="total-price font-extrabold text-gray-700"> {{product.products.dp_amount*product.qty | convert_with_symbol }}</div>
            </div>
          </div>
        </el-col>
        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
          <div class="shopping-cart rounded-lg shadow" v-if="cartProducts.length != 0">
            <div class="flex flex-col items-center w-full ">
              <div class="flex items-center justify-center p-3 mt-4 bg-green-100 rounded-full "><i class="fas fa-cart-arrow-down text-green-600"></i></div>
              <div class="text-lg font-bold text-green-700 leading-tight text-center mt-4 ">Your Cart</div>
              <!-- <div class="text-sm text-center text-gray-700 text-secondary ">Verify the summary before purchase</div> -->
            </div>
            <div class="calculations mt-4">
              <div class="cal-grand">
                <span>Subtotal</span>
              </div>
              <div class="cal-amount"><span>{{temp.subtotal | convert_with_symbol}}</span></div>
            </div>
            <div class="calculations">
              <div class="cal-title">
                <span>IGST</span>
              </div>
              <div class="cal-amount"><span>{{temp.gst_amount | convert_with_symbol}}</span></div>
            </div>
            <div class="calculations">
              <div class="cal-title">
                <span>SGST</span>
              </div>
              <div class="cal-amount"><span>{{temp.sgst_amount | convert_with_symbol}}</span></div>
            </div>
            <div class="calculations">
              <div class="cal-title">
                <span>CGST</span>
              </div>
              <div class="cal-amount"><span>{{temp.cgst_amount | convert_with_symbol}}</span></div>
            </div>
            <div class="calculations">
              <div class="cal-title">
                <span>Shipping</span>
              </div>
              <div class="cal-amount"><span>{{temp.shipping | convert_with_symbol}}</span></div>
            </div>
            <div class="calculations">
              <div class="cal-title">
                <span>Distributor Discount</span>
              </div>
              <div class="cal-amount"><span>{{temp.distributor_discount | convert_with_symbol}}</span></div>
            </div>
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
              <div class="cal-amount"><span>{{Math.round(temp.grand_total) | convert_with_symbol}}</span></div>
            </div>
            <div class="checkout-btn make-payment-btn" v-if="cartProducts.length != 0">
              <el-button class="checkout" type="success" round size="large" icon="el-icon-shopping-cart-full" :loading="buttonLoading" @click="$router.push('/shopping/checkout')">Checkout</el-button>
            </div>
            <div class="checkout-btn make-payment-btn" v-if="cartProducts.length == 0">
              <el-button class="checkout" type="success" round size="large" icon="el-icon-shopping-cart-full" :loading="buttonLoading" @click="$router.push('/shopping/products')">Go to Products</el-button>
            </div>
          </div>
        </el-col>
      </div>
    </el-row>
  </div>
</template>
<script>
import { getMyCart, addToCart, removeFromCart, updateCartQty } from "@/api/user/shopping";
import { getSettings } from "@/api/user/settings";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import emptyCart from "@/assets/images/empty-cart.png";

export default {
  name: "transfer-pin",
  components: { },
  directives: { waves },
  data() {
    return {
      emptyCart: emptyCart,
      temp: {
        subtotal: 0,
        gst_amount: 0,
        sgst_amount: 0,
        cgst_amount: 0,
        utgst_amount: 0,
        shipping: 0,
        discount: 0,
        grand_total: 0,
        pv: 0,
        distributor_discount: 0,
      },
      categories: [],
      cartProducts: [],
      is_shipping_waiver:true,
      settings: { shipping_charge: 0,shipping_charge_2:0 },
      user_state: '',
      buttonLoading: false
    };
  },
  created() {
    this.getSettings();
    this.getMyCart();
  },
  methods: {
    getMyCart() {
      getMyCart().then(response => {
        this.cartProducts = response.data;
        this.calculateFinal();
        this.$events.fire('update-cart-count');
        if(!this.is_shipping_waiver){
          if(this.temp.grand_total < this.settings.shipping_criteria){
            this.temp.shipping=parseFloat(this.settings.shipping_charge_2);
          }else{
            this.temp.shipping=parseFloat(this.settings.shipping_charge);
          }
        }

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
      this.cartProducts.forEach((cart) => {
        this.temp.subtotal += parseFloat(cart.products.dp_base) * parseInt(cart.qty);
        this.temp.distributor_discount += parseFloat(cart.products.retail_amount - cart.products.dp_amount) * parseInt(cart.qty);
        if (this.user_state == this.settings.home_state) {
          this.temp.sgst_amount += parseFloat(cart.products.dp_sgst_amount) * parseInt(cart.qty);
          this.temp.cgst_amount += parseFloat(cart.products.dp_cgst_amount) * parseInt(cart.qty);
        } else {
          this.temp.gst_amount += (parseFloat(cart.products.dp_gst_amount) * parseInt(cart.qty));
        }

        this.temp.discount += parseFloat(cart.products.discount_amount) * parseInt(cart.qty);
        this.temp.pv += parseFloat(cart.products.pv) * parseInt(cart.qty);
        this.temp.grand_total = this.temp.subtotal + this.temp.gst_amount + this.temp.sgst_amount + this.temp.cgst_amount + this.temp.utgst_amount + this.temp.shipping - this.temp.discount;

          if(!cart.products.is_shipping_waiver){
              this.is_shipping_waiver=false
          }
      });
    },
    resetTemp() {
      this.temp = {
        subtotal: 0,
        gst_amount: 0,
        sgst_amount: 0,
        cgst_amount: 0,
        utgst_amount: 0,
        shipping: 0,
        discount: 0,
        grand_total: 0,
        pv: 0,
        distributor_discount: 0,

      };
    },
    updateCartQty(id, qty,stock,variant_id) {

      if(qty <= 0 ){
        this.$message({
          showClose: true,
          message: 'Enter valid quantity.',
          type: 'error'
        });
        qty=1;
      }

        let tempData = {
            'product_id': id,
            'qty': qty,
            'variant_id': variant_id,
        };
        updateCartQty(tempData).then((response) => {
            this.buttonLoading = false;
            this.getMyCart();
        }).catch((res)=>{
            this.getMyCart();
            this.$notify({
                title: "Success",
                message: response.message,
                type: "success",
                duration: 2000
            });
        });  
    },
    removeFromCart(id) {
      this.buttonLoading = true;
      removeFromCart(id).then((response) => {
        this.buttonLoading = false;
        this.getMyCart();
        this.is_shipping_waiver=true;
        this.$events.fire('update-cart-count');
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        });
      });
    },
  }
};

</script>
<style scoped>
* {
  box-sizing: border-box;
}

.app-container {
  background-color: #f1f5f9;
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
  display: flex;
  flex-direction: column;
}

.empty-cart {
  margin: 0 auto;
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
  height: 30px;
  display: flex;
}

.calculations {
  /* border-top:  1px solid #E1E8EE;*/
}


.item {
  padding: 20px 30px;
  height: 120px;
  display: flex;
}

.item {
  /*border-top:  1px solid #E1E8EE;*/
  border-bottom: 1px solid #E1E8EE;
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
  0% {
    background-position: left;
  }

  50% {
    background-position: right;
  }

  100% {
    background-position: right;
  }
}

.make-payment-btn {
  padding: 15px 15px 15px 15px;
  margin: 0 auto;
}


/* Product Image */
.image {
  margin-right: 30px;
  width: 100px;
  text-align: center;
}

/* Product Description */
.description {
  padding-top: 10px;
  margin-right: 30px;
  width: 200px;
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
  margin-top: 0px;
  color: #5d5d5d;
  font-weight: 600;
}

.payment-mode-div {
  height: 55px;
  border-bottom: 1px solid #E1E8EE;
}

.payment-mode {
  margin: 10px 20px 10px 20px;
}

.cal-title span:first-child {
  margin-bottom: 0px;
}

.cal-title span:last-child {
  font-weight: 300;
  margin-top: 0px;
  color: #86939E;
}

/* Product Quantity */
.quantity {
  padding-top: 20px;
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

.checkout-btn {
  padding: 15px 15px 15px 15px;
}

.make-payment-btn {
  padding: 15px 15px 15px 15px;
  margin: 0 auto;
}

.checkout-btn button {
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
  outline: 0;
}

/* Total Price */
.total-price {
  width: 130px;
  padding-top: 20px;
  text-align: center;
  font-size: 16px;
}

.cal-amount {
  width: 70%;
  margin-right: 25px;
  padding-top: 0px;
  text-align: right;
  font-size: 16px;
  color: #43484D;
  font-weight: 600;
}


.checkout-btn {
  padding: 15px 15px 15px 15px;
}

.checkout-btn button {
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
  outline: 0;
}

/* Total Price */
.total-price {
  width: 130px;
  padding-top: 27px;
  text-align: center;
  font-size: 16px;
}

.cal-amount {
  width: 70%;
  margin-right: 25px;
  padding-top: 0px;
  text-align: right;
  font-size: 16px;
  color: #43484D;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 800px) {
  .middel-text {
    width: 99%;
  }

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
    margin-bottom: 20px;
  }

  .for_mobile_width {
    max-width: 100%;

  }
}

</style>
