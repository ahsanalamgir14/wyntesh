<template>
  <div class="app-container">
    <el-row :gutter="10" style="height:100%">
      <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16" style="padding:0rem 1rem; display:flex; flex-direction:column; border-right:1px solid #ededed; height:100%">
         <div>
            <h2 class="font-bold uppercase text-gray-500 mb-2"> Product Combo Order : Combo # {{combo.id }}</h2>
         </div>
        <el-row id="productsScroll" style="max-height:calc(100% - 52px); min-height:calc(100% - 52px); overflow:auto">
          <div v-for="comboCategory in combo.categories" :key="comboCategory.id" v-if="comboCategory">
            <el-card class="box-card rounded-lg mb-2" :body-style="{ padding: '0.5rem' }" shadow="never" v-for="quantity in comboCategory.quantity" :key="quantity">
            <div>
              <span class="m-0 p-0 text-sm">{{comboCategory.category.name}} {{quantity}}</span>
            </div>
            <el-row class="mt-2 mb-2">
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <label class="text-gray-700 text-sm" for="count">Color : </label>
                 <el-select size="mini" v-model="selectedCategoryColors[comboCategory.category_id + '_' + quantity]" @change="handleFilter" clearable class="filter-item " style="width:150px;" filterable placeholder="Select color">
                  <el-option v-for="item in comboCategoryColors[comboCategory.category_id + '_' + quantity]" :key="item.name" :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
              </el-col>
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <label class="text-gray-700 text-sm" for="count">Size : </label>
              <el-select size="mini" v-model="selectedCategorySizes[comboCategory.category_id + '_' + quantity]" @change="handleFilter" clearable class="filter-item " style="width:150px;" filterable placeholder="Select size">
                <el-option v-for="item in comboCategorySizes[comboCategory.category_id + '_' + quantity]" :key="item.name" :label="item.name" :value="item.id">
                </el-option>
              </el-select>
              </el-col>
               <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <label class="text-gray-700 text-sm" for="count">Select Product : </label>
                <el-select size="mini" v-model="selectedCategoryProducts[comboCategory.category_id + '_' + quantity]" @change="handleFilter" clearable class="filter-item " style="width:150px;" filterable placeholder="Select size">
                <el-option v-for="item in comboCategoryProducts[comboCategory.category_id + '_' + quantity]" :key="item.name" :label="item.name" :value="item.id">
                </el-option>
              </el-select>
              </el-col>
            </el-row>
            <hr>
          </el-card>
          </div>
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
      </el-col>
    </el-row>   
  </div>
</template>
<script>
import { getProduct, getMyCartProducts, addToCart, removeFromCart, getSizeByColor, getColorBySize, getStock,getSizes,getColors,getSizesByCategory,getColorsByCategory} from "@/api/user/shopping";
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
      combo:[],
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
      comboCategorySizes: {},
      comboCategoryColors: {},
      comboCategoryProducts: {},
      selectedCategoryProducts: {},
      selectedCategorySizes: {},
      selectedCategoryColors: {},
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
        const combo = response.data;
        this.combo = combo;
        this.products=response.products;
        this.getComboFilters(combo);
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
  getComboFilters(combo) {
    combo.categories.forEach((comboCategory, index) => {

      let key = '';

      if(comboCategory.quantity > 1) {
        
        getColorsByCategory(comboCategory.category_id).then(response => {
          
          for (let qty = 1; qty <= comboCategory.quantity; qty++) {
            key = comboCategory.category_id + '_' + qty;  
            this.$set(this.comboCategoryColors, key, response.data);  
            console.log(key, this.comboCategoryColors[key]);      
          }
          
        });
        
        getSizesByCategory(comboCategory.category_id).then(response => {
          
          for (let qty = 1; qty <= comboCategory.quantity; qty++) {
            key = comboCategory.category_id + '_' + qty;  
            this.$set(this.comboCategorySizes, key, response.data);
            console.log(key, this.comboCategorySizes[key]);
          }

        });
      } else {
        key = comboCategory.category_id + '_' + 1;
        getColorsByCategory(comboCategory.category_id).then(response => {
          this.$set(this.comboCategoryColors, key, response.data);
        });
        
        getSizesByCategory(comboCategory.category_id).then(response => {
          this.$set(this.comboCategorySizes, key, response.data);
        });
      }      

      
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
