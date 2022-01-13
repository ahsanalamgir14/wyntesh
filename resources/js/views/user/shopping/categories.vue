<template>
  <div class="app-container">
    <div class="filter-container">     
      <el-input v-model="listQuery.search" style="width:200px" placeholder="Search" size="mini" class="filter-item " @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" size="mini" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
    </div>
    <el-row :gutter="10">
      <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" style="" class="mb-4">
        <el-card :body-style="{ padding: '0px' }" class="border-dashed border-green-600">
          <div style="background-color: #fff; mb-4">
            <center>
              <router-link :to="'/shopping/combos'" v-lazy-container="{ selector: 'img' }">
                <img :data-src="'images/combo.jpg'" class="image h-96" data-loading="images/combo.jpg">
              </router-link>
            </center>
          </div>
          <div style="padding: 14px;" class="text-center w-full">
            <span>Combos <el-tag type="success" size="mini">NEW</el-tag></span>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" v-for="category in categories" :key="category.id" style="" class="mb-4">
        <el-card :body-style="{ padding: '0px' }">
          <div style="background-color: #fff; mb-4">
            <center>
              <router-link :to="'/shopping/products?category_id='+category.id" v-lazy-container="{ selector: 'img' }">
                <img :data-src="category.image" class="image h-96" data-loading="images/fallback-product.png">
              </router-link>
            </center>
          </div>
          <div style="padding: 14px;" class="text-center w-full">
            <span>{{category.name}}</span>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import { fetchProducts, getAllCategories, getMyCartProducts, addToCart, removeFromCart } from "@/api/user/shopping";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "products",
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      tableKey: 0,
      list: [],
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 20,
        search: undefined,
        package_id: undefined,
        category_id: undefined,
        sort: "+id"
      },
      categories: [],
      cartProducts: [],
      buttonLoading: false
    };
  },
  created() {
    let category_id = this.$route.query.category_id
    if(category_id){
      this.listQuery.category_id=parseInt(category_id);
      this.getList();
    }else{
      this.getList();
    }
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
    getMyCartProducts() {
      getMyCartProducts().then(response => {
        this.cartProducts = response.data;
      });
    },
    addToCart(id) {
      let tempData = {
        'product_id': id,
      };
      this.buttonLoading = true;
      addToCart(tempData).then((response) => {
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


.price {
  line-height: 1.3;
  font-size: 15px;
  color: #424040;
  font-weight: bold;
}

.bottom {
  display: inline;
  margin-top: 13px;
  line-height: 12px;
}

.button {
  float: right;
}

.image {
  padding-top: 10px;
  padding-bottom: 10px;
  text-align: center;
  max-height: 200px;
  max-width: 200px;
  display: block;
}

.clearfix:before,
.clearfix:after {
  display: table;
  content: "";
}

.clearfix:after {
  clear: both
}

</style>
