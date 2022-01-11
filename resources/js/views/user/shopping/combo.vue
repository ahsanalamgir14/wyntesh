<template>
  <div class="app-container">
    <el-row :gutter="10" style="height:100%">
      <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16" style="padding:0rem 1rem; display:flex; flex-direction:column; border-right:1px solid #ededed; height:100%">
         <div>
            <h2 class="font-bold uppercase text-gray-500 mb-2"> Product Combo Order : Combo # {{Combo.id }}</h2>
         </div>
        <el-row id="productsScroll" style="max-height:calc(100% - 52px); min-height:calc(100% - 52px); overflow:auto">
          <div v-for="comboCategory in Combo.categories" :key="comboCategory.id" v-if="comboCategory">
            <el-card class="box-card rounded-lg mb-2" :body-style="{ padding: '0.5rem' }" shadow="never" v-for="quantity in comboCategory.quantity" :key="quantity">
            <div>
              <span class="m-0 p-0 text-sm">{{comboCategory.category.name}} {{quantity}}</span>
            </div>
            <el-row class="mt-2 mb-2">
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <label class="text-gray-700 text-sm" for="count">Color : </label>
                 <el-select size="mini" v-model="listQuery.color_id" @change="handleFilter" clearable class="filter-item " style="width:150px;" filterable placeholder="Select color">
                  <el-option v-for="item in colors" :key="item.name" :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
              </el-col>
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <label class="text-gray-700 text-sm" for="count">Size : </label>
              <el-select size="mini" v-model="listQuery.size_id" @change="handleFilter" clearable class="filter-item " style="width:150px;" filterable placeholder="Select size">
                <el-option v-for="item in sizes" :key="item.name" :label="item.name" :value="item.id">
                </el-option>
              </el-select>
              </el-col>
               <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <label class="text-gray-700 text-sm" for="count">product : </label>
                <el-select size="mini" v-model="listQuery.size_id" @change="handleFilter" clearable class="filter-item " style="width:150px;" filterable placeholder="Select size">
                <el-option v-for="item in sizes" :key="item.name" :label="item.name" :value="item.id">
                </el-option>
              </el-select>
              </el-col>
            </el-row>
            <hr>
          </el-card>
          </div>
          <!-- <el-card class="box-card rounded-lg mb-2" :body-style="{ padding: '0.5rem' }" shadow="never" v-for="product in products" :key="product.id" v-if="product">
            <div>
              <span>{{tempCombo.categories.category.name}}</span>
            </div>
            <div class="text item flex items-center justify-start">
              <div class="pr-2" v-lazy-container="{ selector: 'img' }">
                <img class="image w-20 h-20" :data-src="product.cover_image_thumbnail" data-loading="images/fallback-product.png">
              </div>
              <div class="pr-2">
                <h3 class="font-semibold text-sm" style="font-size:0.8rem"> {{product.name}} </h3>
                <p class="text-xs"> DP: Rs. {{product.dp_amount}} | {{pvLabel}}: {{product.pv}} | Stock: {{product.variant}}</p>
                 <div class="mt-3" v-if="product.is_color_variant" >
                <label class="text-gray-700 text-sm" for="count">Color:</label>
                <div class="flex items-center mt-1">
                  <el-radio-group v-model="currentProduct.color_id" size="mini" @change="handalColorChange(currentProduct.color_id)">
                    <el-radio-button v-for="item in product.variants.color" :label="item.color.id" v-if="item.color"  :key="item.id">{{item}}</el-radio-button>
                  </el-radio-group>
                </div>
              </div>
              <div class="mt-3" v-if="product.is_size_variant">
                <label class="text-gray-700 text-sm" for="count">Size:</label>
                <div class="flex items-center mt-1">
                  <el-radio-group v-model="currentProduct.size_id" size="mini" @change="handalSizeChange()">
                    <el-radio-button v-for="item in product.variants.size" v-if="item.size" :label="item.size.id" :disabled="!parseInt(item.stock)" :key="item.id">{{item.size}}</el-radio-button>
                  </el-radio-group>
                </div>
              </div>
              </div>
              <div class="ml-auto flex items-center">
                <el-input-number class="mr-2" size="mini" style="width:100px" v-model="productQuantity[product.variant_id]" :min="1" :max="10" :value="1"></el-input-number>
                <el-button v-waves class="filter-item uppercase rounded" style="background-color: hsl(120 100% 30%); color: hsl(102deg 80% 100%); border-color:hsl(102deg 80% 100%);" type="primary" size="small" @click="addToCart(product.variant_id)">Add </el-button>
              </div>
            </div>
          </el-card> -->
        </el-row>
      </el-col>
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" style="padding:0rem 1rem; display:flex; flex-direction: column; height:100%">
        <el-row class="flex justify-center items-center" style="min-height:calc(100% - 51px); " v-if="!cartProducts.length">
          <div class="">
            <img src="/images/empty_cart.svg" class="w-48 mx-auto" alt="">
            <h3 class="text-center text-md text-gray-600 pt-6"> No items added to this order yet </h3>
          </div>
        </el-row>
        <el-row id="cartScroll" style="max-height:calc(100% - 189px); min-height:calc(100% - 189px); overflow:auto">
          <el-card class="box-card rounded-lg mb-2" :body-style="{ padding: '0.5rem' }" shadow="never" v-for="cart in cartProducts" :key="cart.id">
            <div class="text item flex items-center justify-start">
              <div class="pr-2" v-lazy-container="{ selector: 'img' }">
                <img class="image w-12 h-12" :data-src="cart.product.cover_image_thumbnail" data-loading="images/fallback-product.png">
              </div>
              <div class="pr-2">
                <h3 class="font-semibold text-sm" style="font-size:0.8rem"> {{cart.product.name}} <br>({{pvLabel}} : {{cart.product.pv}}) </h3>
                <p class="text-xs"> Rs. {{cart.product.dp_amount*cart.qty}} <br>(Rs. {{cart.product.dp_amount}} * {{cart.qty}} Qty) </p>
              </div>
              <div class="ml-auto flex items-center">
                <el-input-number class="mr-2" size="mini" style="width:100px" v-model="productQuantity[cart.variant_id]" :min="1" :max="10" @change="handleUpdateCart(cart.variant_id)"></el-input-number>
                <a @click="removeFromCart(cart.variant_id)">
                  <span class="el-icon-circle-close text-2xl align-middle text-red-500"></span>
                </a>
              </div>
            </div>
          </el-card>
        </el-row>
        <!-- <el-row style="position:sticky; bottom:0px;" v-if="cartProducts.length">
          <div class="pt-2">
            <table class="border border-collapse  w-full table-auto">
              <tr>
                <td class="p-2 border" style="width: 33.33%">
                  <span class="block text-xs"> Sub Total </span>
                  <span class="text-sm font-semibold"> Rs. {{checkoutDetails.subtotal}} </span>
                </td>
                <td class="p-2 border" style="width: 33.33%">
                  <span class="block text-xs uppercase"> GST </span>
                  <span class="text-sm font-semibold"> Rs. {{checkoutDetails.tax}} </span>
                </td>
                <td class="p-2 border" style="width: 33.33%">
                  <span class="block text-xs"> {{pvLabel}} </span>
                  <span class="text-sm font-semibold"> {{checkoutDetails.pv}}</span>
                </td>
              </tr>
              <tr>
                <td colspan="3" class="">
                  <div class="p-2 inline-block align-text-top float-left">
                    <span class="block text-xs uppercase"> Total Amount </span>
                    <span class="text-xl font-semibold"> Rs. {{checkoutDetails.grand_total}} </span>
                  </div>
                  <div class="inline-block float-right" style="width:50%">
                    <el-button v-waves class="filter-item border-1 text-lg rounded-none w-full py-6 font-bold uppercase 
                    text-blue-500 border-blue-500 bg-blue-100 
                    hover:text-green-500 hover:border-green-500 hover:bg-green-100" style="background-color: hsl(120 100% 30%); border-color: hsl(120 100% 30%); color: hsl(102deg 80% 100%); padding:1.5rem 0.5rem ; border-radius:unset; min-width:180px" @click="placeOrder()"> Place Order </el-button>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        </el-row> -->
      </el-col>
    </el-row>
    <!-- <el-dialog title="Package Order" top="20px" :fullscreen="is_mobile" width="80%" :visible.sync="dialogActivateVisible">
      <el-form ref="formPackagePurchase" :rules="packageRules" :model="package_temp">
        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <el-form-item label="Member ID" prop="member_id">
              <el-input v-model="package_temp.member_id" @change="handleMemberIdChange" />
            </el-form-item>
            <el-form-item label="Name" prop="name">
              <el-input v-model="member.name" disabled />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <el-form-item label="Email" prop="email">
              <el-input v-model="member.email" disabled placeholder="Email" />
            </el-form-item>
            <el-form-item label="Contact" prop="contact">
              <el-input v-model="member.contact" disabled />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <el-form-item label="Package" prop="package_id" label-width="120">
              <br>
              <el-select v-model="package_temp.package_id" @change="handlePackageChange" clearable style="width:100%;" filterable placeholder="Select Package">
                <el-option v-for="item in packages" :key="item.name" :label="item.name" :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Total Amount" label-width="120" prop="total_amount">
              <el-input disabled v-model="package_temp.total_amount"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="mini" @click="dialogActivateVisible = false">Cancel</el-button>
        <el-button size="mini" type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="placePackageOrder()">Make Purchase</el-button>
      </span>
    </el-dialog> -->
  </div>
</template>
<script>
import { getProduct, getMyCartProducts, addToCart, removeFromCart, getSizeByColor, getColorBySize, getStock,getSizes,getColors} from "@/api/user/shopping";
import { getCombo } from '@/api/user/combos';
import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import defaultSettings from '@/settings';
const { pvLabel } = defaultSettings;

export default {
  name: "ProductStock",
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      pvLabel,
      products:[],
      Combo:[],
      listQuery:[],
      productQuantity:[],
      currentProduct: {
        product_id: undefined,
        variant_id: undefined,
        color_id: undefined,
        size_id: undefined,
      },
      cartProducts:[],
      colors:[],
      sizes:[],
    };
  },
  created() {
     this.getCombo(this.$route.params.id);
     this.getConfig();
  },
  methods: {
     getCombo(id) {
      this.listLoading = true;
      getCombo(id).then(response => {
        this.Combo = response.data;
        this.products=response.products;
      });
    },
     getConfig() {
      getColors().then(response => {
        this.colors = response.data;
      });
       getSizes().then(response => {
        this.sizes = response.data;
      });
    },
     getMyCartProducts() {
      getMyCartProducts().then(response => {
        this.cartProducts = response.data;
      });
    },
  addToCart(variant_id) {
      if (!variant_id) {
        this.$message({
          showClose: true,
          message: 'Please select Product',
          type: 'error'
        });
        return;
      }
      if (this.productQuantity[variant_id] <= 0) {
        this.$message({
          showClose: true,
          message: 'Enter valid quantity.',
          type: 'error'
        });
        return;
      }

      let data = {
        variant_id: variant_id,
        quantity: this.productQuantity[variant_id],
      }

      this.buttonLoading = true;

      addToCart(data).then((response) => {
        this.buttonLoading = false;
        this.getCheckoutDetails();
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })
      }).catch((err) => {
        this.buttonLoading = false;
      });;

    },
     handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },

  }
};

</script>
<style scoped>
.pagination-container {
  margin-top: 5px;
  background: #fff;
  padding: 15px 16px;
}

.app-container {
  height: calc(100vh - 50px);
}

#categoryScroll::-webkit-scrollbar-track,
#productsScroll::-webkit-scrollbar-track,
#cartScroll::-webkit-scrollbar-track
{
  -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
  border-radius: 10px;
  background-color: #F5F5F5;
}

#categoryScroll::-webkit-scrollbar,
#productsScroll::-webkit-scrollbar,
#cartScroll::-webkit-scrollbar
{
  width: 8px;
  background-color: #F5F5F5;
}

#categoryScroll::-webkit-scrollbar-thumb,
#productsScroll::-webkit-scrollbar-thumb,
#cartScroll::-webkit-scrollbar-thumb
{
  border-radius: 10px;
  -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
  background-color: #adadad;
}

</style>
