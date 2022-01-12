<template>
  <div class="app-container">
    <div class="filter-container">
      <!-- <el-select size="mini" v-model="listQuery.category_id" @change="handleFilter" clearable class="filter-item " style="width:200px;" filterable placeholder="Select Category">
        <el-option v-for="item in categories" :key="item.name" :label="item.name" :value="item.id">
        </el-option>
      </el-select> -->
      <el-input v-model="listQuery.search" style="width:200px" placeholder="Search" size="mini" class="filter-item " @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" size="mini" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
    </div>
    <el-row :gutter="10">
        <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" v-for="combo in list" :key="combo.id" style="">
        <el-card :body-style="{ padding: '0px' }">
          <div style="background-color: #fff;">
            <center>
              <router-link :to="'/shopping/combo/'+combo.id" v-lazy-container="{ selector: 'img' }">
                <img :data-src="combo.image" class="image" data-loading="images/fallback-product.png">
              </router-link>
            </center>
          </div>
          <div style="padding: 14px;">
            <span class="font-black">{{combo.name}}</span>
            <!-- (<span class="text-xs font-extralight text-slate-900" v-for="(item, index) in combo.categories" :key="item.id">{{ item.category.name }}
               <span v-if="index+1 < combo.categories.length">+</span></span>) -->
            <div v-for="category in combo.categories" :key="category.id"><span class="text-xs font-extralight text-slate-900">{{ category.quantity }} x {{ category.category.name }}</span></div>
            <el-row style="margin-top: 20px;">
              <el-col :span="8" class="price">
                <span style="text-align: left;"> Price <br></span>
                  <span>₹ {{parseInt(combo.mrp)}}</span>
              </el-col>
             
              <el-col :span="12" class="price" style="text-align: right;">
                <span>PV <br>
                  {{combo.pv}}</span>
              </el-col>
            </el-row>
            <div class="clearfix" style="margin:0 auto;width: 50%;margin-top: 20px;">
              <center>
                <router-link :to="'/shopping/combo/'+combo.id" v-lazy-container="{ selector: 'img' }">
                    <el-button type="warning" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2" size="mini">View Details</el-button>
                    <!-- <el-button v-if="cartProducts.includes(product.id)" type="danger" @click="removeFromCart(product.id)" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2" size="mini">Remove cart</el-button> -->
                </router-link>
              </center>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
  </div>
</template>
<script>
import { getAllCategories, getMyCartProducts, addToCart, removeFromCart } from "@/api/user/shopping";
import { fetchCombos } from "@/api/user/combos";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "products",
  components: { Pagination },
  directives: { waves },
  filters: {
      uppercase: function(v) {
      return v.toLowerCase();
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
    this.getList();
    this.getConfig();
    this.getMyCartProducts();
  },
  methods: {
    getList() {
      this.listLoading = true;
      fetchCombos(this.listQuery).then(response => {
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
