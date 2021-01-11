<template>
  <div class="app-container">
    <div class="filter-container">
      <!-- <el-select size="mini" class="filter-item mobile_class" v-model="listQuery.incomes" @change="handleFilter" multiple clearable filterable placeholder="Select Product">
        <el-option v-for="item in productList" :key="item.name" :label="item.name" :value="item.id">
        </el-option>
      </el-select> -->
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item mobile_class" size="mini" @keyup.enter.native="handleFilter" />
      <el-button v-waves size="mini" class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button v-waves size="mini" :loading="downloadLoading" class="filter-item" type="warning" icon="el-icon-download" @click="handleDownload">Export</el-button>
       <el-checkbox v-model="listQuery.low_stock" class="filter-item" style="margin-left:15px;" @change="handleFilter">
        Show Low Stock Products ?
      </el-checkbox>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;">
      <el-table-column label="Sr#" prop="id" align="center" type="index" :index="indexMethod" width="70">
      </el-table-column>
      <el-table-column label="Product" min-width="180px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.product.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Color" width="100px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.color?row.color.name:"" }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Size" width="100px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.size?row.size.name:"" }}</span>
        </template>
      </el-table-column>
      <el-table-column label="SKU" min-width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.sku_code }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Stock" min-width="150px" align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.stock <= low_stock_count? 'danger':'success'" class="notranslate">{{ row.stock }}</el-tag>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
  </div>
</template>
<script>
import { getProductStocks,getAllProducts } from "@/api/admin/products-and-categories";
import { companyAdminSettings } from '@/api/admin/settings';
import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "ProductStock",
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      low_stock_count: 5,
      listQuery: {
        page: 1,
        limit: 10,
        search: undefined,
        incomes: undefined,
        low_stock: false,
        sort: "-id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      productList: [],
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    let low_stock = this.$route.query.low_stock=='true'?true:false;
    this.listQuery.low_stock=low_stock;
    this.getList();
    getAllProducts().then(response => {
      this.productList = response.data;
    });
    this.companyAdminSettings();
  },
  methods: {
    indexMethod(index) {
      let page = this.listQuery.page;
      if (this.listQuery.page == 1) {
        let tempIndex = index * 1;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      } else {
        let tempIndex = this.listQuery.limit * (page - 1) + index;;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      }
    },
    companyAdminSettings() {
      companyAdminSettings().then(response => {
        this.low_stock_count= response.data.low_stock_count;

      });
    },
    getList() {
      this.listLoading = true;
      getProductStocks(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Product Name",
          "SKU",
          "Color",
          "Size",
          "Stock",
        ];
        const filterVal = [
          "name",
          "sku_code",
          "color",
          "size",
          "stock",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "product-stocks"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "name") {
            return v.product ? v.product.name : ''
          } else if (j === "color") {
            return v.color ? v.color.name : ''
          } else if (j === "size") {
            return v.size ? v.size.name : ''
          }  else {
            return v[j];
          }
        })
      );
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
</style>
