<template>
  <div class="app-container">
     <div class="filter-container">
      
      <el-button
        v-waves
        class="filter-item"
        type="success"
        icon="el-icon-shopping-bag-1"
        @click="$router.push('/shopping/products')"
      >Go to All Products</el-button>

      <el-button
        v-waves
        class="filter-item"
        type="success"
        icon="el-icon-shopping-cart-full"
        @click="$router.push('/shopping/cart')"
        style="float: right;"
      >Go to Cart</el-button>

    </div>
    <el-row :gutter="10">
      <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
        <el-card :body-style="{ padding: '0px' }" shadow="never">
          <div style="background-color: #fff;" >
            <center v-lazy-container="{ selector: 'img' }">
              <img :data-src="currentImage.url" class="image" data-loading="images/fallback-product.png">
            </center>
          </div>
        </el-card>
        <div>
          <div v-for="img in temp.images" v-lazy-container="{ selector: 'img' }" class="imgs-block" v-bind:class="{ 'img-active': img.id==currentImage.id }" @click="changeImage(img)">
            <img  :data-src="img.url" data-loading="images/fallback-product.png" class="more-images" >
          </div>
        </div>
      </el-col>
      <el-col  :xs="24" :sm="24" :md="16" :lg="16" :xl="16"  >
        <el-card :body-style="{ padding: '0px' }" shadow="never">
          <el-row :gutter="10">
            <el-col  :xs="24" :sm="24" :md="14" :lg="14" :xl="14"  >        
              <div style="padding: 30px;">
                <p class="product-name">{{temp.name}}</p>
                <p class="product-brand">{{temp.brand_name}}</p>
                <p class="product-cat"><el-tag size="mini" type="warning" effect="dark" :key="cat.name" v-for="cat in temp.categories" >{{cat.name}}</el-tag></p>
                <p class="product-qty">{{temp.qty}} {{temp.qty_unit}}</p>
                <p class="product-qty"><b>{{temp.stock}}</b> Units left (Hurry up)</p>
                <p class="product-price" slot="reference">DP - ₹ {{temp.dp_amount}} /- 
                  <el-popover                  
                      placement="right"
                      width="200"
                      trigger="hover">
                      <span class="product-brand" slot="reference" style="font-weight: normal;text-decoration: underline;"><a>Inclusive of all taxes</a></span>

                      <div style="margin-left: 5px;">                   
                        <p><b>Base price</b> : {{temp.dp_base}}</p>
                        <p><b>GST</b> : {{temp.dp_gst}}</p>
                        <p><b>DP</b> : {{temp.dp_amount}}</p>
                      </div>
                  </el-popover>
                </p>
                <p class="product-price" slot="reference">MRP - ₹ {{temp.retail_amount}} /- 
                  <el-popover                  
                      placement="right"
                      width="200"
                      trigger="hover">
                      <span class="product-brand" slot="reference" style="font-weight: normal;text-decoration: underline;"><a>Inclusive of all taxes</a></span>

                      <div style="margin-left: 5px;">                   
                        <p><b>Base price</b> : {{temp.retail_base}}</p>
                        <p><b>GST</b> : {{temp.retail_gst}}</p>
                        <p><b>MRP</b> : {{temp.retail_amount}}</p>
                      </div>
                  </el-popover>
                </p>            
                <p class="product-qty" v-if="temp.discount_rate !=0 || temp.discount_amount !=0 " >
                  <el-tag
                    v-if="temp.discount_rate !=0"
                    type="primary"
                    effect="dark">
                    {{temp.discount_rate}} % Discount on this product.
                  </el-tag>
                  <el-tag
                    v-else 
                    type="primary"
                    effect="dark">
                    ₹ {{temp.discount_amount}} Flat discount on this product.
                  </el-tag>
                </p>

               
              </div>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="10" :lg="10" :xl="10"  >
              <div style="padding: 30px;">
                <p class="product-name">PV: {{temp.pv}}</p>
                <p class="product-brand">Admin Fee: {{temp.admin_fee}}</p>
                <p class="product-brand">Shipping Fee: {{temp.shipping_fee}}</p>
              </div>
            </el-col>
          </el-row>
          <div style="padding: 0px 10px 50px 30px;">
            <div class="cart-btn">
              <el-button v-if="!cartProducts.includes(temp.id)" type="success" @click="addToCart(temp.id)" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2" >Add to cart</el-button>

              <el-button v-if="cartProducts.includes(temp.id)" type="danger" @click="removeFromCart(temp.id)" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2" >Remove cart</el-button>
            </div>
          </div>
          <hr style="width: 100%">
          <el-collapse v-model="activeAccordion" style="margin-left: 30px;margin-right: 20px;" accordion>
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
import { getProduct, getMyCartProducts, addToCart, removeFromCart  } from "@/api/user/shopping";
import { parseTime } from "@/utils";
import waves from "@/directive/waves";
export default {
  name: "product",
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
      cartProducts:[],
      activeAccordion:"1",
      temp: {
        id:undefined,
        name:undefined,
        brand_name: undefined,
        qty:undefined,
        qty_unit:undefined,
        description:undefined,
        benefits:undefined,
        retail_amount:undefined,
        discount_amount:undefined,
        pv:undefined,
        stock:undefined,
        cover_image:undefined,
        images:[],
        categories:[],

      },
      currentImage:{
        id:'',
        url:''
      },
      buttonLoading: false
    };
  },
  created() {
    this.getProduct(this.$route.params.id);
    this.getMyCartProducts();
  },
  methods: {    
    getProduct(id) {
      this.listLoading = true;
      getProduct(id).then(response => {
        this.temp = response.data;
        let image={
          id:545454,
          url:this.temp.cover_image
        };
        this.temp.images.unshift(image);
        this.currentImage=this.temp.images[0];
        this.listLoading = false;
      });
    },
    getMyCartProducts(){
      getMyCartProducts().then(response => {
        this.cartProducts = response.data;
      });
    },
    changeImage(img){
      this.currentImage=img;
    },    
    addToCart(id){
      let tempData={
        'product_id':id,
      };
      
      this.buttonLoading=true;
      addToCart(tempData).then((response) => {
        this.buttonLoading=false;
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
    removeFromCart(id){      
      this.buttonLoading=true;

      removeFromCart(id).then((response) => {
        this.buttonLoading=false;
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

.el-select{
  width:100%;
}

.product-name{
  font-weight: bold;
  font-size: 20px;
  margin-top: 5px;
  margin-bottom: 5px;
}
.product-brand{
  font-size: 15px;
  margin-top: 1px;
  margin-bottom: 5px;
}
.product-cat{
  font-size: 15px;
  margin-top: 1px;
  margin-bottom: 25px;
}

.product-cat span{
  margin-right: 5px;
}
.product-qty{
  font-size: 17px;
  margin-top: 8px;
  margin-bottom: 5px;
}
.product-price {
  line-height: 1.3;
  font-size: 17px;
  color: #424040;
  font-weight: bold;
}
  
.cart-btn{
  margin-top: 20px;
}

.button {
  float: left;
}

.image {
  padding-top: 40px;
  padding-bottom: 40px;
  text-align:center;
  max-height: 350px;
  max-width: 350px;
  display: block;
}

.imgs-block{  
  display: inline-block;
  width: 50px;
  padding: 5px;
  cursor:pointer;
  text-align: center;
  border: 1px solid #ccc;
}
.img-active{
 border: 2px solid #3a8ee6; 
}

.more-images {
  cursor:pointer;
  max-height: 40px;
  max-width: 40px;
}

 
</style>
