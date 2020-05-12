<template>
  <div class="app-container">
    
    <el-row>
      <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" style="margin-right: 10px;">
        <h1 >My Cart<br><br><br></h1>
      </el-col>
    </el-row>
    <el-row >
      <el-col  :span="23"  style="margin-right: 10px;">
        <div class="shopping-cart">
          <div class="column-labels">
            <label class="product-image">Product</label>
            <label class="product-details">Product</label>
            <label class="product-price">Price</label>
            <label class="product-quantity">Quantity</label>
            <label class="product-removal">Remove</label>
            <label class="product-line-price">Total</label>
          </div>

          <div class="product"  v-for="product in list" :key="product.id" >
            <div class="product-image">
              <img :src="product.cover_image_thumbnail" >
            </div>
            <div class="product-details">
              <div class="product-title">{{product.name}}</div>
              <!-- <span class="product-description" v-html="product.description?product.description.substr(0, 150)+' ...':''"></span> -->
            </div>
            <div class="product-price">{{product.retail_amount}}</div>
            <div class="product-quantity">
              <el-input style="width: 80px;" type="number" value="2" min="1"  />
            </div>
            <div class="product-removal">
              <button class="remove-product">
                Remove
              </button>
            </div>
            <div class="product-line-price">25.98</div>
          </div>

          <div class="totals">
            <div class="totals-item">
              <label>Subtotal</label>
              <div class="totals-value" id="cart-subtotal">71.97</div>
            </div>
            <div class="totals-item">
              <label>Tax (5%)</label>
              <div class="totals-value" id="cart-tax">3.60</div>
            </div>
            <div class="totals-item">
              <label>Shipping</label>
              <div class="totals-value" id="cart-shipping">15.00</div>
            </div>
            <div class="totals-item totals-item-total">
              <label>Grand Total</label>
              <div class="totals-value" id="cart-total">90.57</div>
            </div>
          </div>
              
              <button class="checkout">Checkout</button>

        </div>
      </el-col>
    </el-row>


    <el-dialog title="Transfer Pins to Member" width="40%" center :visible.sync="dialogPinTransferVisible" style="height: auto;margin: 0 auto;">
      <el-form ref="pinTransferForm" :rules="pinTransferRules"  :model="temp" style="width: 70%;margin: 0 auto;">
        <el-form-item label="Member ID" prop="member_id">
          <el-input  v-on:blur="handleCheckMemberId()" v-model="temp.member_id" />
        </el-form-item>
        <el-form-item label="Member Name" prop="member_name">
          <el-input  disabled v-model="temp.member_name" />
        </el-form-item>
        <el-input
          type="textarea"
          v-model="temp.note"
          :rows="2"
          placeholder="Please Enter Note">
        </el-input>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogPinTransferVisible = false">Cancel</el-button>
        <el-button type="primary" @click="transferPins()">Confirm</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import { fetchProducts, getAllCategories, getMyCartProducts, addToCart, removeFromCart } from "@/api/user/shopping";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

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
        member_id:undefined,
        member_name: undefined,
        note:undefined,   

      },
      pinTransferRules: {
        member_id: [{ required: true, message: 'Member Id is required', trigger: 'blur' }]
      },
      dialogPinTransferVisible:false,
      dialogTitle:'',
      is_create:true,      
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getConfig();
    this.getMyCartProducts();
  },
  methods: {    
    getList() {
      this.listLoading = true;
      fetchProducts(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getConfig() {      
      getAllCategories().then(response => {
        this.categories = response.data;
      });
    },
    getMyCartProducts(){
      getMyCartProducts().then(response => {
        this.cartProducts = response.data;
      });
    },
    handlePinTansfer() {
      this.dialogPinTransferVisible = true;
      this.$nextTick(() => {
        this.$refs["pinTransferForm"].clearValidate();
      });      
    },
    addToCart(id){
      let tempData={
        'product_id':id,
      };
      this.buttonLoading=true;
      addToCart(tempData).then((response) => {
        this.buttonLoading=false;
        this.getMyCartProducts();
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        });
      });      
    },
    removeFromCart(id){      
      this.buttonLoading=true;
      removeFromCart(id).then((response) => {
        this.buttonLoading=false;
        this.getMyCartProducts();
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
/*
I wanted to go with a mobile first approach, but it actually lead to more verbose CSS in this case, so I've gone web first. Can't always force things...

Side note: I know that this style of nesting in SASS doesn't result in the most performance efficient CSS code... but on the OCD/organizational side, I like it. So for CodePen purposes, CSS selector performance be damned.
*/
/* Global settings */
/* Global "table" column settings */
.product-image {
  float: left;
  width: 20%;
}

.product-details {
  float: left;
  width: 37%;
  padding-right: 20px;
}

.product-price {
  float: left;
  width: 12%;
}

.product-quantity {
  float: left;
  width: 10%;
}

.product-removal {
  float: left;
  width: 9%;
}

.product-line-price {
  float: left;
  width: 12%;
  text-align: right;
}

/* This is used as the traditional .clearfix class */
.group:before, .shopping-cart:before, .column-labels:before, .product:before, .totals-item:before,
.group:after,
.shopping-cart:after,
.column-labels:after,
.product:after,
.totals-item:after {
  content: '';
  display: table;
}

.group:after, .shopping-cart:after, .column-labels:after, .product:after, .totals-item:after {
  clear: both;
}

.group, .shopping-cart, .column-labels, .product, .totals-item {
  zoom: 1;
}

/* Apply clearfix in a few places */
/* Apply dollar signs */
.product .product-price:before, .product .product-line-price:before, .totals-value:before {
  content: '$';
}

/* Body/Header stuff */
body {
  padding: 0px 30px 30px 20px;  
  font-weight: 100;  
}

h1 {
  font-weight: 100;
  color:#2b2b2b;
}

label {
  color: #aaa;
}

.shopping-cart {
  margin-top: -45px;
}

/* Column headers */
.column-labels label {
  padding-bottom: 15px;
  margin-bottom: 15px;
  border-bottom: 1px solid #eee;
}
 .column-labels .product-details, .column-labels .product-removal {
  text-indent: -9999px;
}

/* Product entries */
.product {
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}
.product .product-image {
  text-align: center;
}
.product .product-image img {
  max-width: 50px;
  max-height: 50px;
}
.product .product-details .product-title {
  margin-right: 20px;
  
  color:#2b2b2b;
}
.product .product-details .product-description {
  margin: 5px 20px 5px 0;
  line-height: 1.4em;
  color:#757575;
}
.product .product-quantity el-input {
  width: 40px;
}
.product .remove-product {
  border: 0;
  padding: 4px 8px;
  background-color: #c66;
  color: #fff;
  
  font-size: 12px;
  border-radius: 3px;
}
.product .remove-product:hover {
  background-color: #a44;
}

/* Totals section */
.totals .totals-item {
  float: right;
  clear: both;
  width: 100%;
  margin-bottom: 10px;
}
.totals .totals-item label {
  float: left;
  clear: both;
  width: 79%;
  text-align: right;
}
.totals .totals-item .totals-value {
  float: right;
  width: 21%;
  text-align: right;
}
.totals .totals-item-total {
  font-family: "HelveticaNeue-Medium", "Helvetica Neue Medium";
}

.checkout {
  float: right;
  border: 0;
  margin-top: 20px;
  padding: 6px 25px;
  background-color: #6b6;
  color: #fff;
  font-size: 25px;
  border-radius: 3px;
}

.checkout:hover {
  background-color: #494;
}

/* Make adjustments for tablet */
@media screen and (max-width: 650px) {
  .shopping-cart {
    margin: 0;
    padding-top: 20px;
    border-top: 1px solid #eee;
  }

  .column-labels {
    display: none;
  }

  .product-image {
    float: right;
    width: auto;
  }
  .product-image img {
    margin: 0 0 10px 10px;
  }

  .product-details {
    float: none;
    margin-bottom: 10px;
    width: auto;
  }

  .product-price {
    clear: both;
    width: 70px;
  }

  .product-quantity {
    width: 100px;
  }
  .product-quantity input {
    margin-left: 20px;
  }

  .product-quantity:before {
    content: 'x';
  }

  .product-removal {
    width: auto;
  }

  .product-line-price {
    float: right;
    width: 70px;
  }
}
/* Make more adjustments for phone */
@media screen and (max-width: 350px) {
  .product-removal {
    float: right;
  }

  .product-line-price {
    float: right;
    clear: left;
    width: auto;
    margin-top: 10px;
  }

  .product .product-line-price:before {
    content: 'Item Total: $';
  }

  .totals .totals-item label {
    width: 60%;
  }
  .totals .totals-item .totals-value {
    width: 40%;
  }
}
</style>
