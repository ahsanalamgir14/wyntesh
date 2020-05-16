<template>
  <div class="app-container">
    <div class="filter-container">
      
      <el-input
        v-model="listQuery.search"
        placeholder="Search"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>

      <el-select v-model="listQuery.category_id" @change="handleFilter"  clearable class="filter-item" style="width:200px;float: right;" filterable placeholder="Select Category">
        <el-option
          v-for="item in categories"
          :key="item.name"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>
    </div>
  
    <el-row>
      <el-col  :xs="24" :sm="24" :md="5" :lg="5" :xl="5" :span="8" v-for="product in list" :key="product.id" style="margin-right: 10px;">
        <el-card :body-style="{ padding: '0px' }" >
          <div style="background-color: #fff;">
            <center>
              <router-link :to="'/shopping/product/'+product.id" v-lazy-container="{ selector: 'img' }">
                <img :data-src="product.cover_image_thumbnail" class="image" data-loading="images/fallback-product.png">
              </router-link>
            </center>
          </div>
          <div style="padding: 14px;">
            <span>{{product.name}}</span>
            <el-row style="margin-top: 20px;">
              <el-col :span="12" class="price">
               <span style="text-align: left;"> MRP <br>
                {{product.retail_amount}}</span>
              </el-col>
              <el-col :span="12" class="price" style="text-align: right;">
                <span>{{product.qty}} <br>
                {{product.qty_unit}}</span>
              </el-col>
            </el-row>
            <div class="clearfix" style="margin:0 auto;width: 60%;margin-top: 20px;">
              <center>
                <el-button v-if="!cartProducts.includes(product.id)" type="warning" @click="addToCart(product.id)" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2" size="mini">Add to cart</el-button>

                <el-button v-if="cartProducts.includes(product.id)" type="danger" @click="removeFromCart(product.id)" :loading="buttonLoading" class="button" icon="el-icon-shopping-cart-2" size="mini">Remove cart</el-button>
              </center>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

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
        limit: 20,
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
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
  }
};
</script>

<style scoped>

.el-select{
  width:100%;
}
.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
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
    text-align:center;
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
