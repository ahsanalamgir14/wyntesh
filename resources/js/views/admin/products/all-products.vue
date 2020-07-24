<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
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
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="success"
        @click="handleCreate"
      ><i class="fas fa-plus"></i> Add</el-button>
    </div>

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="170" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
              circle
              type="primary"
              icon="el-icon-edit"
              @click="handleEdit(row)"
              ></el-button>

             <el-button icon="el-icon-turn-off"
            circle v-if="row.is_active!=1" type="info" @click="handleModifyActivationStatus(row,1)">
          </el-button>
          <el-button icon="el-icon-open" circle v-if="row.is_active!=0" type="success" @click="handleModifyActivationStatus(row,0)">
          </el-button>

          </el-button>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="200px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Categories" min-width="200px">
        <template slot-scope="{row}">
          <span v-for="cat in row.categories">{{ cat.name }}, &nbsp;</span>
        </template>
      </el-table-column>
      <el-table-column label="BV" align="right" width="110px">
        <template slot-scope="{row}">
          <span> {{row.pv}} </span>
        </template>
      </el-table-column>
      <el-table-column label="MRP" align="right" width="110px">
        <template slot-scope="{row}">
          <span> {{row.retail_amount}} </span>
        </template>
      </el-table-column>
      <el-table-column label="DP" align="right" width="110px">
        <template slot-scope="{row}">
          <span> {{row.dp_amount}} </span>
        </template>
      </el-table-column>

      <el-table-column label="Stock" align="right" width="110px">
        <template slot-scope="{row}">
          <span> {{row.stock}} </span>
        </template>
      </el-table-column>

       <el-table-column label="Created at" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
    </el-table>

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
import {
  fetchProducts,
  deleteProduct,
  changeProductActivationStatus,
} from "@/api/admin/products-and-categories";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import Tinymce from '@/components/Tinymce'

export default {
  name: "ComplexTable",
  components: { Pagination,Tinymce },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        sort: "+id",
        is_active: 'all',
      },
      productStatusLog:{
        user_id:undefined,
        is_active:0,
        remarks:undefined,
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    handleModifyActivationStatus(row,status) {
      this.resetUserStatus();
      let temp = Object.assign({}, row);
      this.productStatusLog.product_id=temp.id;
      this.productStatusLog.is_active=status;
      this.dialogUserActivationStatus=true;
      this.updateProductActivationStatus(status);
    },

   updateProductActivationStatus(status){    
      changeProductActivationStatus(this.productStatusLog).then((response) => {

        this.getList();
        this.dialogUserActivationStatus=false;

        if(status){
            this.productStatusLog.is_active = 0;
        }else{
            this.productStatusLog.is_active = 1;
        }



        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })
      })

    },
    
    resetUserStatus(){
      this.productStatusLog = {
        product_id:undefined,
        is_active:0,
        remarks:undefined,
      };
    },
 
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
    handleCreate() {
      this.$router.push({ path: '/products/add' });
    },
    handleEdit(row) {
      this.$router.push({ path: '/products/edit', query: { id: row.id } });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped>
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
@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px; 
  }
}
</style>
