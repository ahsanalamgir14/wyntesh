<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button v-waves class="filter-item" type="success" icon="el-icon-shopping-bag-1" @click="$router.push('/shopping/products')">Go to All Products</el-button>
      <el-button v-waves class="filter-item" type="success" icon="el-icon-shopping-cart-full" @click="$router.push('/shopping/cart')" style="float: right;">Go to Cart</el-button>
    </div>
    <el-row :gutter="10">
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
        <el-card :body-style="{ padding: '0px' }" class="shadow-sm p-3">
          <div style="background-color: #fff;">
            <center v-lazy-container="{ selector: 'img' }">
              <img class="h-64 w-64 rounded-md object-cover mx-auto" :data-src="currentImage.url" alt="Nike Air" data-loading="images/fallback-product.png">
            </center>
          </div>
        </el-card>
        <div>
          <div v-for="img in temp.images" v-lazy-container="{ selector: 'img' }" class="imgs-block" v-bind:class="{ 'img-active': img.id==currentImage.id }" @click="changeImage(img)">
            <img :data-src="img.url" data-loading="images/fallback-product.png" class="more-images">
          </div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16">
        <el-card :body-style="{ padding: '0px' }" shadow="never" class="p-6">
          <span class="text-gray-700 font-bold text-sm">{{temp.brand_name}}</span>
          <h3 class="text-gray-800  text-2xl mb-4">{{temp.name}} <span class="text-gray-700  text-sm">({{temp.qty}} {{temp.qty_unit}})</span></h3>
          <div>
            <span class="text-gray-700 mt-3">DP - ₹ {{temp.dp_amount}} /-
              <el-popover placement="right" width="200" trigger="hover">
                <span class="product-brand" slot="reference" style="font-weight: normal;text-decoration: underline;"><a>Inclusive of all taxes</a></span>
                <div style="margin-left: 5px;">
                  <p><b>Base price</b> : {{temp.dp_base}}</p>
                  <p><b>GST</b> : {{temp.dp_gst_amount}}</p>
                  <p><b>DP</b> : {{temp.dp_amount}}</p>
                </div>
              </el-popover>
            </span>
          </div>
          <el-row :gutter="10" class="mb-4">
            <el-col :xs="24" :sm="24" :md="14" :lg="14" :xl="14">
              <div>
                <span class="text-gray-700 mt-3">MRP - ₹ {{temp.retail_amount}} /-
                  <el-popover placement="right" width="200" trigger="hover">
                    <span class="product-brand" slot="reference" style="font-weight: normal;text-decoration: underline;"><a>Inclusive of all taxes</a></span>
                    <div style="margin-left: 5px;">
                      <p><b>Base price</b> : {{temp.retail_base}}</p>
                      <p><b>GST</b> : {{temp.retail_gst_amount}}</p>
                      <p><b>MRP</b> : {{temp.retail_amount}}</p>
                    </div>
                  </el-popover>
                </span>
              </div>
              <div>
                <span class="text-gray-800 mt-3">PV - {{temp.pv}}</span>
              </div>
              <hr class="my-3">
              <!-- <div class="mt-2">
                <label class="text-gray-700 text-sm" for="count">Units Available:</label>
                <div class="flex items-center mt-1">
                  <span class="text-gray-700 text-lg ">{{temp.stock}}</span>
                </div>
              </div> -->
              <div class="mt-3" v-if="temp.is_color_variant">
                <label class="text-gray-700 text-sm" for="count">Color:</label>
                <div class="flex items-center mt-1">
                  <el-radio-group v-model="currentProduct.color_id" size="mini" @change="handalColorChange(currentProduct.color_id)">
                    <el-radio-button v-for="item in color" :label="item.color.id" v-if="item.color"  :key="item.id">{{item.color.name}}</el-radio-button>
                  </el-radio-group>
                </div>
              </div>
              <div class="mt-3" v-if="temp.is_size_variant">
                <label class="text-gray-700 text-sm" for="count">Size:</label>
                <div class="flex items-center mt-1">
                  <el-radio-group v-model="currentProduct.size_id" size="mini" @change="handalSizeChange()">
                    <el-radio-button v-for="item in size" v-if="item.size" :label="item.size.id" :disabled="!parseInt(item.stock)" :key="item.id">{{item.size.brand_size}}</el-radio-button>
                  </el-radio-group>
                </div>
              </div>
              <div class="flex items-center mt-6">
                <el-button v-if="!cartProducts.includes(currentProduct.variant_id)" :disabled="!this.temp.stock" type="success" @click="addToCart()" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2">Add to cart</el-button>
                <el-button v-if="cartProducts.includes(currentProduct.variant_id)" type="danger" @click="removeFromCart(currentProduct.variant_id)" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2">Remove cart</el-button>
              </div>
            </el-col>
            <el-col :xs="24" :sm="24" :md="10" :lg="10" :xl="10">
            </el-col>
          </el-row>
          <hr style="width: 100%">
          <el-collapse v-model="activeAccordion" style="" accordion>
            <el-collapse-item title="Description" name="1">
              <span v-html="temp.description"></span>
            </el-collapse-item>
            <el-collapse-item title="Benefits" name="2">
              <span v-html="temp.benefits"></span>
            </el-collapse-item>
          </el-collapse>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import { getProduct, getMyCartProducts, addToCart, removeFromCart, getSizeByColor, getColorBySize, getStock } from "@/api/user/shopping";
import { parseTime } from "@/utils";
import waves from "@/directive/waves";
export default {
  name: "product",
  directives: { waves },
  data() {
    return {
      temp: {
        id: undefined,
        name: undefined,
        brand_name: undefined,
        qty: undefined,
        qty_unit: undefined,
        description: undefined,
        benefits: undefined,
        retail_amount: undefined,
        discount_amount: undefined,
        pv: undefined,
        stock: undefined,
        cover_image: undefined,
        images: [],
        categories: [],

      },
      currentProduct: {
        product_id: undefined,
        variant_id: undefined,
        color_id: undefined,
        size_id: undefined,
      },
      size: [],
      color: [],
      currentImage: {
        id: '',
        url: ''
      },
      cartProducts: [],
      productVariant: [],
      activeAccordion: "1",
      buttonLoading: false,
    };
  },
  created() {
    this.getProduct(this.$route.params.id);
    this.getMyCartProducts();
  },
  methods: {
    handalColorChange() {
      this.getStock();
      getSizeByColor(this.currentProduct).then(response => {
        this.size = response.data;
        let images=response.images;
        this.temp.images=[];
        let image = {
          id: 545454,
          url: this.temp.cover_image
        };

        images.forEach((image)=>{
          this.temp.images.push({
            id: image.id,
            url: image.url
          });
        });
        this.temp.images.unshift(image);
        this.currentImage = this.temp.images[0];
        
      });
    },
    handalSizeChange(){
       this.getStock();
    },
    getStock() {
      getStock(this.currentProduct).then(response => {
        this.temp.stock = response.data ? response.data.stock : 0;
        this.currentProduct.variant_id = response.data ? response.data.id : '';
      });
    },
    getProduct(id) {
      this.listLoading = true;
      getProduct(id).then(response => {
        this.temp = response.data;
        this.color = response.productColorVariant;
        this.currentProduct.product_id = this.temp.id;
        this.currentProduct.color_id=response.productColorVariant.length?this.color[0].color_id:undefined;
        this.handalColorChange();
        let image = {
          id: 545454,
          url: this.temp.cover_image
        };
        this.temp.images.unshift(image);
        this.currentImage = this.temp.images[0];
        this.listLoading = false;
      });
    },
    getMyCartProducts() {
      getMyCartProducts().then(response => {
        this.cartProducts = response.data;
      });
    },
    changeImage(img) {
      this.currentImage = img;
    },
    addToCart(id) {

      if(this.temp.is_size_variant && !this.currentProduct.size_id ){
        this.$message({
          showClose: true,
          message: 'Please select product size.',
          type: 'error'
        });
        return false
      }

      this.buttonLoading = true;
      addToCart(this.currentProduct).then((response) => {
        this.buttonLoading = false;
        this.getMyCartProducts();
        this.$events.fire('update-cart-count');
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
        this.getMyCartProducts();
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
<style scoped lang="scss">

.imgs-block {
  display: inline-block;
  width: 50px;
  padding: 5px;
  cursor: pointer;
  text-align: center;
  border: 1px solid #ccc;
}

.img-active {
  border: 2px solid #3a8ee6;
}

.more-images {
  cursor: pointer;
  max-height: 40px;
  max-width: 40px;
}

</style>
